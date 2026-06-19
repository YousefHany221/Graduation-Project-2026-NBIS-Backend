<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// ⚠️ تأكد من وجود هذا السطر فوق
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    // ⚠️ أضف HasApiTokens هنا بداخل الكلاس
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // تأكد من إضافة الـ role هنا أيضاً إذا كنت تستخدمه
    ];

    // ... باقي كود الموديل الافتراضي كما هو
}