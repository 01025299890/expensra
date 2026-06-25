<?php


namespace App\Services;

use Illuminate\Support\Facades\Http;

class SendDataToAiService
{

    public function sendToAi($request)
    {
        
        if($request['type'] == 'image') {
            
            $url = "https://ahmedhamdymohameds-expensra-ai-v3.hf.space/predict_image";
        }
        if($request['type'] == 'voice') {
            $url = "https://ahmedhamdymohameds-expensra-ai-v3.hf.space/predict_voice_file";
        }
        if($request['type'] != 'image' && $request['type'] != 'voice') {
            return [];
        }
        unset($request['type']);

        $httpRequest = Http::asMultipart()->timeout(60);
        
        if ($request->hasFile('file')) {
            $httpRequest->attach(
                'file',
                fopen($request->file('file')->getRealPath(), 'r'),
                $request->file('file')->getClientOriginalName()
            );
        }
       
        $response = $httpRequest->post($url, [
            'text' => $request->input('text'),
        ]);
        if ($response->failed()) {
            return [];
        }

        return $response->json();
    }
}