<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Barang;
use App\Models\BarangKategori;
use App\Models\StockBarang;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin Warehouse',
            'email' => 'admin@warehouse.com',
            'password' => bcrypt('password'),
        ]);

        // Create staff user
        $staff = User::create([
            'name' => 'Staff Warehouse',
            'email' => 'staff@warehouse.com',
            'password' => bcrypt('password'),
        ]);

        // Create barang kategori
        $kategoriElektronik = BarangKategori::create([
            'nama' => 'Elektronik',
            'created_by' => $admin->id,
        ]);

        $kategoriKantor = BarangKategori::create([
            'nama' => 'Peralatan Kantor',
            'created_by' => $admin->id,
        ]);

        // Create barang
        $laptop = Barang::create([
            'nama' => 'Laptop Dell Inspiron',
            'created_by' => $admin->id,
        ]);

        $mouse = Barang::create([
            'nama' => 'Mouse Wireless Logitech',
            'created_by' => $admin->id,
        ]);

        $keyboard = Barang::create([
            'nama' => 'Keyboard Mechanical',
            'created_by' => $admin->id,
        ]);

        $printer = Barang::create([
            'nama' => 'Printer Canon',
            'created_by' => $admin->id,
        ]);

        $kertas = Barang::create([
            'nama' => 'Kertas A4',
            'created_by' => $admin->id,
        ]);

        // Create initial stock
        StockBarang::create([
            'id_barang' => $laptop->id,
            'qty' => 10,
            'created_by' => $admin->id,
        ]);

        StockBarang::create([
            'id_barang' => $mouse->id,
            'qty' => 25,
            'created_by' => $admin->id,
        ]);

        StockBarang::create([
            'id_barang' => $keyboard->id,
            'qty' => 15,
            'created_by' => $admin->id,
        ]);

        StockBarang::create([
            'id_barang' => $printer->id,
            'qty' => 5,
            'created_by' => $admin->id,
        ]);

        StockBarang::create([
            'id_barang' => $kertas->id,
            'qty' => 100,
            'created_by' => $admin->id,
        ]);
    }
}
