<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AdminService;
class AdminController extends Controller
{
    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }


    public function UsersCount()
    {
        $result = $this->adminService->UsersCount();
        
        return response()->json($result);
    }

    public function normalUsersCount()
    {
        $result = $this->adminService->normalUsersCount();
        return response()->json($result);
    }

    public function premiumUsersCount()
    {
        $result = $this->adminService->premiumUsersCount();
       
        return response()->json($result);
    }


    public function adminUsersCount()
    {
        $result = $this->adminService->adminUsersCount();
        return response()->json($result);
    }

    public function allUsers()
    {
        $result = $this->adminService->allUsers();
        return response()->json($result);
    }

    public function normalUsers()
    {
        $result = $this->adminService->normalUsers();
        return response()->json($result);
    }

    public function premiumUsers()
    {
        $result = $this->adminService->premiumUsers();
        return response()->json($result);
    }

    public function adminUsers()
    {
        $result = $this->adminService->adminUsers();
        return response()->json($result);
    }

    // public function userTransactions($userId)
    // {
    //     $result = $this->adminService->usersTransactions($userId);
    //     return response()->json($result);
    // }

    public function searchUsers(Request $request)
    {
        $query = $request->query('query');

        // تأكد إن فيه كلمة بحث أصلاً عشان ميبحثش بـ فاضي ويرجع كل الناس
        if (!$query) {
            return response()->json([
                'error' => true,
                'message' => 'برجاء إدخال كلمة بحث'
            ], 400);
        }

        $result = $this->adminService->searchUsers($query);


        return response()->json($result);
    }

    public function upgradeUserToPremium($userId)
    {
        $result = $this->adminService->upgradeUserToPremium($userId);
        return response()->json($result);
    }

    public function deleteUser($userId)
    {
        $result = $this->adminService->deleteUser($userId);
        return response()->json($result);
    }
}
