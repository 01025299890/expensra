<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;
    protected $table = 'categories';
    protected $fillable = [
        'user_id',
        'name',
        'icon',
        'type'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    public function scopeCategoryExpenses($query){
        return $query->where('type','expense');
    }
    public function scopeCategoryIncomes($query){
        return $query->where('type','income');
    }

    public function scopeGetOrCreate($query,$categoryName,$userId,$categoryType){
        
        return $query->firstOrCreate(
            ['name' => $categoryName ?? 'uncategorized', 'user_id' => $userId, 'type' => $categoryType ?? null],
            ['icon' =>  null]
        ) ;
    }
}
