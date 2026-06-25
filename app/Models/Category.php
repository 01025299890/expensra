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

    public function scopeGetOrCreate($query, $categoryName, $userId, $categoryType)
    {
        $name = $categoryName ?? 'uncategorized';

        // 1. ابحث عن أي سجل (سواء كان عام null أو خاص بالمستخدم) بنفس الاسم والنوع
        // هذا يضمن عدم تكرار "طعام" مرتين في الجدول
        $category = (clone $query)->where('name', $name)
            ->where('type', $categoryType)
            ->where(function ($q) use ($userId) {
                $q->whereNull('user_id')
                    ->orWhere('user_id', $userId);
            })
            ->first();

        if ($category) {
            return $category; // سيعيد السجل العام إذا وجده، وبذلك لن نكرر البيانات
        }

        // 2. إذا لم يجد (لا عام ولا خاص)، ينشئ واحد جديد مخصص للمستخدم
        return $query->create([
            'name' => $name,
            'user_id' => $userId,
            'type' => $categoryType,
            'icon' => null
        ]);
    }

    // داخل App\Models\Category.php

    public function scopeVisibleToUser($query)
    {
        return $query->whereNull('user_id')
            ->orWhere('user_id', auth()->id());
    }
}
