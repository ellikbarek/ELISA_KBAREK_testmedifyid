<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Menjalankan aplikasi

Instalasi pertama setelah clone (PHP 8.2+, Composer, MySQL; aktifkan ekstensi PDO MySQL, fileinfo, mbstring, DOM/XML, GD, dan zlib):

```sh
composer install
```

Salin `.env.example` menjadi `.env`, lalu isi `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` sesuai database lokal yang sudah dibuat. Setelah itu:

```sh
php artisan key:generate
php artisan migrate
php artisan serve
```

Daftarkan akun melalui `/register`, kemudian login untuk mengakses CRUD, foto, PDF, dan Excel. Instalasi baru dimulai dengan data kosong; tambahkan kategori dan item lewat menu aplikasi. Folder `storage` dan `bootstrap/cache` harus dapat ditulis oleh PHP.

Untuk menambahkan tabel kategori, relasi kategori-item, dan kolom foto pada database yang sudah dikonfigurasi, jalankan sekali:

```sh
php artisan migrate
```

Setelah dependensi PHP, `.env`, dan database siap, jalankan:

```sh
php artisan serve
```

Buka http://127.0.0.1:8000. Tidak perlu menjalankan `npm run dev` karena aset CSS dan JavaScript hasil build disertakan dalam folder `public/build`.

Jika mengubah file frontend di `resources/js`, `resources/sass`, atau konfigurasi Vite, jalankan `npm ci` lalu `npm run build` dan sertakan perubahan `public/build` bersama perubahan sumber. Untuk menjalankan aplikasi sehari-hari, Node.js/npm tidak diperlukan.

Jika sebelumnya menjalankan Vite dan aplikasi masih mencoba mengakses port 5173, hentikan Vite dan hapus file `public/hot` jika masih ada agar Laravel kembali menggunakan aset hasil build.

## Fitur Master Items dan Kategori

- Navbar menyediakan menu Master Items dan Kategori Items.
- Form item mendukung beberapa kategori serta foto JPG/PNG/WebP maksimal 2 MB. Foto dapat diganti atau dihapus dan dilayani lewat Laravel, tanpa `storage:link`.
- Filter harga beli minimum/maksimum bekerja terpisah, inklusif, dan menerima nol. Rentang terbalik ditolak.
- Kategori memiliki nama dan kode unik, filter nama/kode, serta detail berisi daftar item terkait. Menghapus kategori tidak menghapus item.
- Detail kategori menyediakan PDF melalui DomPDF, dengan tabel item dan tanggal/waktu permintaan download dalam WIB (Asia/Jakarta) pada footer.
- Tombol Download Excel mengekspor hasil filter terakhir yang diterapkan menjadi `.xlsx`: No, Nama kategori (dipisahkan koma), Nama items, Nama supplier, Harga, Laba, Hargajual. Harga adalah harga beli, laba dalam persen, dan harga jual dibulatkan ke rupiah terdekat.
- Workbook dikemas sebagai ZIP 2.0/Deflate menggunakan zlib bawaan PHP, tanpa memerlukan ekstensi ZIP tambahan. Node.js tidak diperlukan saat aplikasi dijalankan.

Pengujian memerlukan ekstensi PDO SQLite dan menggunakan SQLite in-memory, terpisah dari database aplikasi:

```sh
php artisan test
```

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 2000 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
