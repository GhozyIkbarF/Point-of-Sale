<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Matikan constraint sementara
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Hapus data dari tabel yang saling terkait
        DB::table('sale_details')->delete();
        DB::table('sales')->delete();
        DB::table('products')->delete();
        DB::table('users')->delete();

        // Reset auto increment
        DB::statement('ALTER TABLE sale_details AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE sales AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE products AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE users AUTO_INCREMENT = 1');

        // Aktifkan kembali constraint
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Jalankan seeder masing-masing
        $this->call([
            UserSeeder::class,
            ProductSeeder::class,
            SaleSeeder::class,
        ]);
    }
}
