<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $table = 'transactions';
    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'transaction_type',
        'transaction_date',
        'notes'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeExpenses($query){
        return $query->where('transaction_type','expense');
    }

    public function scopeIncomes($query) {
        return $query->where('transaction_type','income');
    }
}
