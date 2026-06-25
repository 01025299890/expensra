<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    protected $table = 'goals';
   protected $fillable = [
    'user_id',
    'goal_name',
    'target_amount',
    'saved_amount',
    'deadline',
    ];



    public function user(){
      return  $this->belongsTo(User::class);
    }
}
