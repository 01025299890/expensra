<?php

namespace App\Http\Controllers;

use App\Http\Requests\HandleSurplusRequest;
use App\Services\MonthlyResetService;

class MonthlyResetController extends Controller
{
    protected $resetService;

    public function __construct(MonthlyResetService $resetService)
    {
        $this->resetService = $resetService;
    }

    public function handleMonthlySurplus(HandleSurplusRequest $request)
    {
        // السيرفيس بتنفذ اللوجيك كله هنا
       $result = $this->resetService->handleSurplus($request->validated(), $request->user());
        if ($request->validated()['action'] === 'to_goal') {
            return response()->json([
                'status' => 'success',
                'message' => $result,
            ], 200);
        
        }
        return response()->json([
            'status' => 'success',
            'message' => $result,
        ], 200);

    }
}