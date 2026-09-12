<?php

namespace App\Services;

use Illuminate\Support\Collection;

class ItemSpreadsheet
{
    /** Write an OOXML workbook without requiring the optional ext-zip extension. */
    public function create(Collection $items): string
    {
        $path = tempnam(sys_get_temp_dir(), 'items-');
        if ($path === false) throw new \RuntimeException('Cannot create spreadsheet temporary file.');

        try {
            $archive = [];
            $archive['[Content_Types].xml'] = '<?xml version="1.0" encoding="UTF-8"?>'
                .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
                .'<Default Extension="xml" ContentType="application/xml"/>'
                .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
                .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>';
            $archive['_rels/.rels'] = '<?xml version="1.0"?>'
                .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>';
            $archive['xl/workbook.xml'] = '<?xml version="1.0" encoding="UTF-8"?>'
                .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
                .'<sheets><sheet name="Master Items" sheetId="1" r:id="rId1"/></sheets></workbook>';
            $archive['xl/_rels/workbook.xml.rels'] = '<?xml version="1.0"?>'
                .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>';

            $xml = '<?xml version="1.0" encoding="UTF-8"?>'
                .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
                .'<cols><col min="1" max="1" width="8" customWidth="1"/><col min="2" max="4" width="30" customWidth="1"/><col min="5" max="7" width="18" customWidth="1"/></cols><sheetData>';
            $xml .= $this->row(1, ['No', 'Nama kategori', 'Nama items', 'Nama supplier', 'Harga', 'Laba', 'Hargajual']);
            foreach ($items->values() as $index => $item) {
                $xml .= $this->row($index + 2, [
                    $index + 1, $item->categories->sortBy('nama')->pluck('nama')->implode(', '),
                    $item->nama, $item->supplier, $item->harga_beli, $item->laba, $item->harga_jual,
                ]);
            }
            $archive['xl/worksheets/sheet1.xml'] = $xml.'</sheetData></worksheet>';
            $this->writeArchive($path, $archive);
            return $path;
        } catch (\Throwable $exception) {
            unset($archive);
            if (is_file($path)) unlink($path);
            throw $exception;
        }
    }

    /**
     * Package the five workbook parts as a standard ZIP 2.0/Deflate archive.
     * Phar emits ZIP version-needed 0 on this PHP build, which Excel rejects.
     * Header layouts: https://pkware.cachefly.net/webdocs/casestudies/APPNOTE.TXT
     */
    private function writeArchive(string $path, array $parts): void
    {
        $body = '';
        $directory = '';
        // A valid, deterministic DOS timestamp: 1980-01-01 00:00:00.
        $dosDate = 33;
        foreach ($parts as $name => $contents) {
            $compressed = gzdeflate($contents);
            if ($compressed === false) throw new \RuntimeException('Cannot compress spreadsheet.');
            $size = strlen($contents);
            $compressedSize = strlen($compressed);
            $offset = strlen($body);
            $crc = crc32($contents);
            $nameLength = strlen($name);
            if (max($size, $compressedSize, $offset) >= 0xffffffff) {
                throw new \RuntimeException('Spreadsheet exceeds ZIP32 size limit.');
            }
            $body .= pack('VvvvvvVVVvv', 0x04034b50, 20, 0, 8, 0, $dosDate,
                $crc, $compressedSize, $size, $nameLength, 0).$name.$compressed;
            $directory .= pack('VvvvvvvVVVvvvvvVV', 0x02014b50, 20, 20, 0, 8, 0, $dosDate,
                $crc, $compressedSize, $size, $nameLength, 0, 0, 0, 0, 0, $offset).$name;
        }
        $end = pack('VvvvvVVv', 0x06054b50, 0, 0, count($parts), count($parts),
            strlen($directory), strlen($body), 0);
        $bytes = $body.$directory.$end;
        if (file_put_contents($path, $bytes) !== strlen($bytes)) {
            throw new \RuntimeException('Cannot write spreadsheet.');
        }
    }

    private function row(int $number, array $values): string
    {
        $xml = '<row r="'.$number.'">';
        foreach ($values as $index => $value) {
            $ref = chr(65 + $index).$number;
            if (is_int($value) || is_float($value)) {
                $xml .= '<c r="'.$ref.'"><v>'.$value.'</v></c>';
            } else {
                // Inline strings keep names/codes literal, including a leading '='.
                $value = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', (string) $value);
                $xml .= '<c r="'.$ref.'" t="inlineStr"><is><t xml:space="preserve">'
                    .htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</t></is></c>';
            }
        }
        return $xml.'</row>';
    }
}
