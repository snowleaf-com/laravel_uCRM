<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Purchase;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'kana',
        'tel',
        'email',
        'postcode',
        'address',
        'birthday',
        'gender',
        'memo',
    ];

    public function scopeSearchCustomers($query, $input = null)
    {
        if (!empty($input)) {
            // kana, tel, nameのいずれかに一致するデータを検索
            if (Customer::where('kana', 'like', $input . '%')
                ->orWhere('tel', 'like', $input . '%')
                ->orWhere('name', 'like', $input . '%')
                ->exists()
            ) {
                return $query->where('kana', 'like', $input . '%')
                    ->orWhere('tel', 'like', $input . '%')
                    ->orWhere('name', 'like', $input . '%');
            }
        }
    }

    public function purchases() {
        return $this->hasMany(Purchase::class);
    }
}
