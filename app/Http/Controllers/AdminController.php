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
        $status = isset($result['error']) ? 404 : 200;
        return response()->json($result, $status);
    }

    public function normalUsersCount()
    {
        $result = $this->adminService->normalUsersCount();
        $status = isset($result['error']) ? 404 : 200;
        return response()->json($result, $status);
    }

    public function premiumUsersCount()
    {
        $result = $this->adminService->premiumUsersCount();
        $status = isset($result['error']) ? 404 : 200;
        return response()->json($result, $status);
    }


    public function adminUsersCount()
    {
        $result = $this->adminService->adminUsersCount();
        $status = isset($result['error']) ? 404 : 200;
        return response()->json($result, $status);
    }

    public function allUsers()
    {
        $result = $this->adminService->allUsers();
        $status = isset($result['error']) ? 404 : 200;
        return response()->json($result, $status);
    }

    public function normalUsers()
    {
        $result = $this->adminService->normalUsers();
        $status = isset($result['error']) ? 404 : 200;
        return response()->json($result, $status);
    }

    public function premiumUsers()
    {
        $result = $this->adminService->premiumUsers();
        $status = isset($result['error']) ? 404 : 200;
        return response()->json($result, $status);
    }

    public function adminUsers()
    {
        $result = $this->adminService->adminUsers();
        $status = isset($result['error']) ? 404 : 200;
        return response()->json($result, $status);
    }

    public function userTransactions($userId)
    {
        $result = $this->adminService->usersTransactions($userId);
        $status = isset($result['error']) ? 404 : 200;
        return response()->json($result, $status);
    }

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
        $status = isset($result['error']) ? 404 : 200;

        return response()->json($result, $status);
    }

    public function upgradeUserToPremium($userId)
    {
        $result = $this->adminService->upgradeUserToPremium($userId);
        $status = isset($result['error']) ? 404 : 200;
        return response()->json($result, $status);
    }
}
