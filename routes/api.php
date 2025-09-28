<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\AIService;

Route::post('/chat', function(Request $request, AIService $ai) {
    $prompt = $request->input('prompt');
    return response()->json([
        'answer' => $ai->ask($prompt)
    ]);
});
