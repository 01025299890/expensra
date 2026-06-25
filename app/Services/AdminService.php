<?php
namespace App\Services;
use App\Models\User;
class AdminService{
    public function UsersCount(){
        if(User::count() === 0){
            return [
                'error' => true,
                'message' => 'لا يوجد مستخدمين حالياً',
                'count' => 0
            ];
        }
        return User::count();
    }
    public function normalUsersCount(){
        if(User::where('system_role', 'normal_user')->count() === 0){
            return [
                'error' => true,
                'message' => 'لا يوجد مستخدمين عاديين حالياً',
                'count' => 0
            ];
        }
        return User::where('system_role', 'normal_user')->count();
    }

    public function premiumUsersCount(){
        if(User::where('system_role', 'premium_user')->count() === 0){
            return [
                'error' => true,
                'message' => 'لا يوجد مستخدمين مميزين حالياً',
                'count' => 0
            ];
        }
        return User::where('system_role', 'premium_user')->count();
    }

    public function adminUsersCount(){
        if(User::where('system_role', 'admin')->count() === 0){
            return [
                'error' => true,
                'message' => 'لا يوجد مستخدمين غيرك حالياً',
                'count' => 0
            ];
        }
        return User::where('system_role', 'admin')->count();
    }

    public function allUsers(){
        $users = User::paginate(50);
        if($users->isEmpty()){
            return [
                'error' => true,
                'message' => 'لا يوجد مستخدمين حالياً',
                'users' => []
            ];
        }
        return $users;
    }

    public function normalUsers(){
        $users = User::where('system_role', 'normal_user')->paginate(50);
        if($users->isEmpty()){
            return [
                'error' => true,
                'message' => 'لا يوجد مستخدمين عاديين حالياً',
                'users' => []
            ];
        }
        return $users;
    }

    public function premiumUsers(){
        $users = User::where('system_role', 'premium_user')->paginate(50);
        if($users->isEmpty()){
            return [
                'error' => true,
                'message' => 'لا يوجد مستخدمين مميزين حالياً',
                'users' => []
            ];
        }
        return $users;
    }

    public function adminUsers(){
        $users = User::where('system_role', 'admin')->paginate(50);
        if($users->isEmpty()){
            return [
                'error' => true,
                'message' => 'لا يوجد مستخدمين غيرك حالياً',
                'users' => []
            ];
        }
        return $users;
    }

    public function usersTransactions($userId){
        $user = User::find($userId);
        if(!$user){
            return [
                'error' => true,
                'message' => 'المستخدم غير موجود',
                'transactions' => []
            ];
        }
        $transactions = $user->transactions()->latest()->paginate(50);
        if($transactions->isEmpty()){
            return [
                'error' => true,
                'message' => 'لا يوجد معاملات لهذا المستخدم',
                'transactions' => []
            ];
        }
        return $transactions;
    }

    public function searchUsers($query)
    {
        // بنستخدم function داخل الـ where عشان نجمع شروط الـ OR مع بعض
        $users = User::where(function ($q) use ($query) {
            $q->where('first_name', 'LIKE', "%{$query}%")
                ->orWhere('last_name', 'LIKE', "%{$query}%")
                ->orWhere('email', 'LIKE', "%{$query}%")
                ->orWhere('system_role', 'LIKE', "%{$query}%");
        })->paginate(50);

        if ($users->isEmpty()) {
            return [
                'error' => true,
                'message' => 'لا يوجد مستخدمين يطابقون البحث',
                'users' => []
            ];
        }

        return $users;
    }


    public function deleteUser($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return [
                'error' => true,
                'message' => 'المستخدم غير موجود'
            ];
        }
        $user->delete();
        return [
            'error' => false,
            'message' => 'تم حذف المستخدم بنجاح'
        ];
    }

    public function updateUserRole($userId, $newRole)
    {
        $user = User::find($userId);
        if (!$user) {
            return [
                'error' => true,
                'message' => 'المستخدم غير موجود'
            ];
        }
        $validRoles = ['normal_user', 'premium_user', 'admin'];
        if (!in_array($newRole, $validRoles)) {
            return [
                'error' => true,
                'message' => 'الدور الجديد غير صالح'
            ];
        }
        $user->system_role = $newRole;
        $user->save();
        return [
            'error' => false,
            'message' => 'تم تحديث دور المستخدم بنجاح'
        ];
    }

    public function upgradeUserToPremium($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return [
                'error' => true,
                'message' => 'المستخدم غير موجود'
            ];
        }
        if ($user->system_role === 'premium_user') {
            return [
                'error' => true,
                'message' => 'المستخدم بالفعل مميز'
            ];
        }
        $user->system_role = 'premium_user';
        $user->save();
        return [
            'error' => false,
            'message' => 'تم ترقية المستخدم إلى مميز بنجاح'
        ];
    }
    
}