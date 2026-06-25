<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $table = 'budgets';

    protected $fillable = 
    [
        'category_id',
        'user_id',
        'limit_amount',
        'start_date',
        'end_date',
    ];

    public function category(){
      return $this->belongsTo(Category::class);
    }

  public function transactions()
  {
    return $this->hasMany(Transaction::class, 'category_id', 'category_id')
      ->whereColumn('transactions.user_id', 'budgets.user_id');
  }
    public function user(){
      return $this->belongsTo(User::class);
    }  
}
