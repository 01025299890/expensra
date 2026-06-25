<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGoalRequest;
use App\Http\Requests\UpdateGoalRequest;
use App\Models\Goal;
use App\Services\GoalService;
use Illuminate\Http\Request;

class GoalController extends Controller
{
   protected $GoalService;
    public function __construct(GoalService $GoalService){
        $this->GoalService = $GoalService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       $result = $this->GoalService->index(auth()->user());
       $status = isset($result['error']) ? 404 : 200;
       return response()->json($result, $status);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGoalRequest $request)
    {
        $result = $this->GoalService->store(auth()->user(),$request->validated());
        $status = isset($result['error']) ? 422 : 201;
        return response()->json($result,$status);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $result = $this->GoalService->show(auth()->user(),$id);
        $status = isset($result['error']) ? 404 : 200;
        return response()->json($result,$status);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGoalRequest $request,$id)
    {
        $result = $this->GoalService->update(auth()->user(), $request->validated(),$id);
        $status = isset($result['error']) ? 404 : 200;
        return response()->json($result, $status);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $result = $this->GoalService->destroy(auth()->user(),$id);
        $status = isset($result['error']) ? 404 : 200;
        return response()->json($result, $status);
    }

    public function depositToGoal(Request $request, $id)
    {
        $validatedRequest = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $result = $this->GoalService->depositToGoal(auth()->user(), $validatedRequest, $id);
        $status = isset($result['error']) ? 422 : 200;
        return response()->json($result, $status);
    }
}
