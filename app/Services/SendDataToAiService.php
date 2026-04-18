<?php


namespace App\Services;

use Illuminate\Support\Facades\Http;

class SendDataToAiService
{

    public function sendToAi($request)
    {
        $url = "https://webhook.site/7d3e67f2-37b3-4321-8e8e-1af5d2cde21e";

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