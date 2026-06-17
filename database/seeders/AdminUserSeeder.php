<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1️⃣ حساب الأدمن الأساسي (System Admin)
        User::updateOrCreate(
            ['email' => 'admin@nbis.com'],
            [
                'name' => 'System Admin',
                'phone' => '+201000000001',
                'password' => Hash::make('Admin@1234'),
                'role' => 'admin',
            ]
        );

        // 2️⃣ حساب الممرضة (Nurse) - مسؤولة عن تسجيل المواليد وأخذ البصمات
        User::updateOrCreate(
            ['email' => 'nurse@nbis.com'],
            [
                'name' => 'Nurse Sarah',
                'phone' => '+201000000002',
                'password' => Hash::make('Nurse@1234'),
                'role' => 'nurse', // الأدوار دي لازم تكون متوافقة مع الـ Enum أو الـ Validation عندك
            ]
        );

        // 3️⃣ حساب الشرطة (Police Officer) - المسؤول عن البحث والتبليغ عن الأطفال المفقودين
        User::updateOrCreate(
            ['email' => 'police@nbis.com'],
            [
                'name' => 'Officer Ahmed',
                'phone' => '+201000000003',
                'password' => Hash::make('Police@1234'),
                'role' => 'police',
            ]
        );
    }
}