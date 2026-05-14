<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 1. BUAT AKUN ADMIN SAJA
        // Kita tidak perlu PaymentMethod lagi karena checkout sudah pakai HTML manual
        User::create([
            'name' => 'Admin',
            'email' => 'admin@abuser.com',
            'password' => Hash::make('123'),
            'role' => 'admin'
        ]);
        
        // Kode PaymentMethod dihapus agar tidak error
    }
}