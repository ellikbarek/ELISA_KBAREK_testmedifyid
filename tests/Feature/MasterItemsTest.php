<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MasterItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MasterItemsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(\App\Models\User::factory()->create());
    }

    public function test_guests_cannot_access_item_or_category_endpoints()
    {
        auth()->logout();
        foreach (['/master-items', '/master-items/search', '/master-items/export', '/master-items/form/new', '/master-items/1/foto', '/categories', '/categories/1/pdf'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
        $this->post('/master-items/form/new', $this->payload())->assertRedirect('/login');
        $this->post('/categories', ['nama' => 'Uji', 'kode' => 'UJ'])->assertRedirect('/login');
        $this->delete('/master-items/delete/1')->assertRedirect('/login');
        $this->delete('/categories/1')->assertRedirect('/login');
        $this->get('/master-items/update-random-data')->assertNotFound();
    }

    public function test_category_migration_can_roll_back_and_reapply_without_losing_items()
    {
        $item = $this->item();
        $migration = require database_path('migrations/2026_09_12_000001_add_categories_and_photo_to_master_items.php');
        $migration->down();
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('master_items', 'foto'));
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasTable('categories'));
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasTable('category_master_item'));
        $this->assertNotNull($item->fresh());
        $migration->up();
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('master_items', 'foto'));
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasTable('categories'));
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasTable('category_master_item'));
        $this->assertNotNull($item->fresh());
    }

    private function payload(array $overrides = []): array
    {
        return array_merge(['nama' => 'Item Uji', 'harga_beli' => 100, 'laba' => 20, 'supplier' => 'Blublu', 'jenis' => 'Umum'], $overrides);
    }

    private function item(int $price = 100): MasterItem
    {
        $item = new MasterItem;
        foreach ($this->payload(['harga_beli' => $price]) as $key => $value) $item->{$key} = $value;
        $item->kode = (string) (MasterItem::withTrashed()->count() + 1);
        $item->save();
        return $item;
    }

    public function test_price_filters_work_independently_and_include_zero_and_boundaries()
    {
        foreach ([0, 100, 200] as $price) $this->item($price);
        foreach ([
            [[], [0, 100, 200]],
            [['hargamin' => 100], [100, 200]],
            [['hargamax' => 100], [0, 100]],
            [['hargamax' => 0], [0]],
            [['hargamin' => 0], [0, 100, 200]],
            [['hargamin' => 100, 'hargamax' => 100], [100]],
        ] as [$filters, $expected]) {
            $response = $this->getJson('/master-items/search?'.http_build_query($filters))->assertOk();
            $this->assertSame($expected, array_column($response->json('data'), 'harga_beli'));
        }
        $this->getJson('/master-items/search?hargamin=200&hargamax=100')->assertUnprocessable()->assertJsonValidationErrors('hargamax');
        $this->getJson('/master-items/search?hargamin=abc')->assertUnprocessable();
    }

    public function test_item_photo_and_categories_can_be_created_replaced_cleared_and_deleted()
    {
        Storage::fake();
        $a = Category::create(['nama' => 'Obat', 'kode' => 'OB']);
        $b = Category::create(['nama' => 'Umum', 'kode' => 'UM']);
        $this->post('/master-items/form/new', $this->payload([
            'foto' => UploadedFile::fake()->image('foto.jpg'), 'categories' => [$a->id, $b->id],
        ]))->assertRedirect('/master-items');
        $item = MasterItem::firstOrFail();
        $this->assertCount(2, $item->categories);
        $oldPhoto = $item->foto;
        Storage::assertExists($oldPhoto);
        $this->get(route('items.photo', $item))->assertOk()->assertHeader('content-type', 'image/jpeg');
        $this->get('/master-items/view/'.$item->kode)->assertOk()->assertSee('Obat')->assertSee('Umum');
        $this->get('/master-items/form/edit/'.$item->id)->assertOk()->assertSee('multipart/form-data', false);

        $this->post('/master-items/form/edit/'.$item->id, $this->payload(['categories' => [$b->id]]))->assertRedirect();
        $this->assertSame($oldPhoto, $item->fresh()->foto);
        $this->assertSame([$b->id], $item->fresh()->categories->modelKeys());

        $this->post('/master-items/form/edit/'.$item->id, $this->payload(['foto' => UploadedFile::fake()->image('new.png')]))->assertRedirect();
        Storage::assertMissing($oldPhoto);
        $newPhoto = $item->fresh()->foto;
        Storage::assertExists($newPhoto);
        $this->assertCount(0, $item->fresh()->categories);

        $this->post('/master-items/form/edit/'.$item->id, $this->payload(['hapus_foto' => 1]))->assertRedirect();
        Storage::assertMissing($newPhoto);
        $this->assertNull($item->fresh()->foto);
        $this->get(route('items.photo', $item))->assertNotFound();

        $this->post('/master-items/form/edit/'.$item->id, $this->payload([
            'foto' => UploadedFile::fake()->image('delete.jpg'), 'categories' => [$a->id],
        ]));
        $deletePhoto = $item->fresh()->foto;
        $this->delete(route('items.destroy', $item))->assertRedirect('/master-items');
        Storage::assertMissing($deletePhoto);
        $this->assertSoftDeleted($item);
        $this->assertDatabaseCount('category_master_item', 0);
    }

    public function test_item_validation_rejects_non_images_large_images_and_unknown_categories()
    {
        Storage::fake();
        $this->postJson('/master-items/form/new', $this->payload([
            'foto' => UploadedFile::fake()->create('file.txt', 10, 'text/plain'),
            'categories' => [999],
        ]))->assertUnprocessable()->assertJsonValidationErrors(['foto', 'categories.0']);
        $this->postJson('/master-items/form/new', $this->payload([
            'foto' => UploadedFile::fake()->image('large.jpg')->size(2049),
        ]))->assertUnprocessable()->assertJsonValidationErrors('foto');
        $this->assertDatabaseCount('master_items', 0);
        $this->assertSame([], Storage::allFiles());
    }

    public function test_category_crud_filters_and_related_items()
    {
        $this->post(route('categories.store'), ['nama' => 'Obat', 'kode' => 'OB'])->assertRedirect();
        $a = Category::firstOrFail();
        $b = Category::create(['nama' => 'Alkes', 'kode' => 'AK']);
        $item = $this->item();
        $item->categories()->attach([$a->id, $b->id]);
        $this->get(route('categories.create'))->assertOk();
        $this->get(route('categories.edit', $a))->assertOk();
        $this->get('/categories?nama=Oba')->assertOk()->assertSee('OB')->assertDontSee('Alkes');
        $this->get('/categories?kode=AK')->assertOk()->assertSee('Alkes')->assertDontSee('Obat');
        $this->get(route('categories.show', $a))->assertOk()->assertSee('Item Uji');
        $this->put(route('categories.update', $a), ['nama' => 'Obat Baru', 'kode' => 'OB'])->assertRedirect();
        $this->postJson(route('categories.store'), ['nama' => 'Duplikat', 'kode' => 'OB'])->assertJsonValidationErrors('kode');
        $this->delete(route('categories.destroy', $a))->assertRedirect(route('categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $a->id]);
        $this->assertNotNull($item->fresh());
        $this->assertSame([$b->id], $item->fresh()->categories->modelKeys());
        $this->get('/categories/99999')->assertNotFound();
        $this->get('/master-items/view/missing')->assertNotFound();
    }

    public function test_category_pdf_download_and_footer()
    {
        $category = Category::create(['nama' => 'Obat PDF', 'kode' => 'PDF']);
        $item = $this->item();
        $item->categories()->attach($category);
        $category->load('items');
        $html = view('categories.pdf', ['category' => $category, 'printedAt' => '12-09-2026 12:00:00 WIB'])->render();
        foreach (['Obat PDF', 'PDF', 'Item Uji', '12-09-2026 12:00:00 WIB', '<footer>'] as $text) $this->assertStringContainsString($text, $html);
        $response = $this->get(route('categories.pdf', $category))->assertOk()->assertDownload('kategori-'.$category->id.'.pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $item->delete();
        $this->get(route('categories.show', $category))->assertOk()->assertDontSee('Item Uji');
        $this->get(route('categories.pdf', $category))->assertOk();
    }

    public function test_pdf_download_uses_current_wib_date_even_when_utc_is_previous_day()
    {
        $category = Category::create(['nama' => 'Waktu', 'kode' => 'WKT']);
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-09-12 18:30:45', 'UTC'));
        $pdf = \Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        \Barryvdh\DomPDF\Facade\Pdf::shouldReceive('loadView')->once()
            ->withArgs(fn ($view, $data) => $view === 'categories.pdf' && $data['printedAt'] === '13-09-2026 01:30:45 WIB')
            ->andReturn($pdf);
        $pdf->shouldReceive('setPaper')->with('a4', 'landscape')->once()->andReturnSelf();
        $pdf->shouldReceive('download')->with('kategori-'.$category->id.'.pdf')->once()->andReturn(response('PDF'));
        try {
            $this->get(route('categories.pdf', $category))->assertOk();
        } finally {
            $this->travelBack();
        }
    }

    public function test_category_index_is_sorted_by_code_ascending()
    {
        Category::create(['nama' => 'AAA', 'kode' => 'Z02']);
        Category::create(['nama' => 'ZZZ', 'kode' => 'A01']);
        $this->get(route('categories.index'))->assertOk()
            ->assertViewHas('categories', fn ($categories) => $categories->pluck('kode')->all() === ['A01', 'Z02']);
    }

    public function test_excel_is_a_real_workbook_with_required_columns_and_filtered_rows()
    {
        $a = Category::create(['nama' => 'Alkes', 'kode' => 'AK']);
        $b = Category::create(['nama' => 'Obat', 'kode' => 'OB']);
        $item = $this->item(100);
        $item->nama = '=SUM(1,2) & Item';
        $item->save();
        $item->categories()->attach([$a->id, $b->id]);
        $this->item(200);
        $response = $this->get('/master-items/export?hargamax=100')->assertOk()->assertDownload('master-items.xlsx');
        $path = $response->baseResponse->getFile()->getPathname();
        try {
            $bytes = file_get_contents($path);
            $offset = 0;
            $partCount = 0;
            while (substr($bytes, $offset, 4) === "PK\x03\x04") {
                $header = unpack('Vsignature/vversion/vflags/vmethod/vtime/vdate/Vcrc/Vcompressed/Vsize/vname/vextra', substr($bytes, $offset, 30));
                $this->assertSame(20, $header['version'], 'Excel requires a valid ZIP extraction version.');
                $this->assertSame(8, $header['method']);
                $start = $offset + 30 + $header['name'] + $header['extra'];
                $contents = gzinflate(substr($bytes, $start, $header['compressed']));
                $this->assertSame($header['size'], strlen($contents));
                $this->assertSame($header['crc'], crc32($contents));
                $offset = $start + $header['compressed'];
                $partCount++;
            }
            $this->assertSame(5, $partCount);
            $this->assertSame("PK\x01\x02", substr($bytes, $offset, 4));
            $this->assertSame(20, unpack('v', substr($bytes, $offset + 6, 2))[1]);
            $archive = new \PharData($path);
            $this->assertTrue(isset($archive['[Content_Types].xml']));
            $sheet = simplexml_load_string($archive['xl/worksheets/sheet1.xml']->getContent());
            $sheet->registerXPathNamespace('s', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $rows = $sheet->xpath('//s:row');
            $this->assertCount(2, $rows);
            $this->assertSame(['No', 'Nama kategori', 'Nama items', 'Nama supplier', 'Harga', 'Laba', 'Hargajual'], array_map(fn ($cell) => (string) $cell->is->t, iterator_to_array($rows[0]->c, false)));
            $this->assertSame('Alkes, Obat', (string) $rows[1]->c[1]->is->t);
            $this->assertSame('=SUM(1,2) & Item', (string) $rows[1]->c[2]->is->t);
            $this->assertSame('inlineStr', (string) $rows[1]->c[2]['t']);
            $this->assertSame('100', (string) $rows[1]->c[4]->v);
            $this->assertSame('20', (string) $rows[1]->c[5]->v);
            $this->assertSame('120', (string) $rows[1]->c[6]->v);
        } finally {
            unset($archive);
            unlink($path);
        }
    }
}
