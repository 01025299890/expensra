<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendDataToAiRequest;
use Illuminate\Http\Request;
use App\Services\SendDataToAiService;
use App\Services\TransactionService;

class SendDataToAiAndStore extends Controller
{
        protected $sendDataToAiService;
        protected $transactionService;

    public function __construct(SendDataToAiService $sendDataToAiService, TransactionService $transactionService)
    {
        $this->sendDataToAiService = $sendDataToAiService;
        $this->transactionService = $transactionService;
    }
    public function storeByAi(SendDataToAiRequest $request)
    {
        
        $sendToAi = $this->sendDataToAiService->sendToAi($request);
        $response = $this->transactionService->storeTransaction($sendToAi ?? [], auth()->id());
        if (isset($response['error'])) {
            return response()->json(['message' => $response['error']], 400);
        }
        return response()->json($response->load('category'));
    }

    
}
