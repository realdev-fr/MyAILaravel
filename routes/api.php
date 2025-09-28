<?php

use App\Http\Controllers\MCPTestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\AIService;

Route::post('/chat', function(Request $request, AIService $ai) {
    $prompt = $request->input('prompt');
    return response()->json([
        'answer' => $ai->ask($prompt)
    ]);
});

// MCP Test Routes
Route::prefix('/mcp')->group(function () {
    Route::get('/test', [MCPTestController::class, 'testConnection']);
    Route::get('/tools', [MCPTestController::class, 'getTools']);
    Route::post('/device/control', [MCPTestController::class, 'controlDevice']);
    Route::post('/tool/call', [MCPTestController::class, 'callTool']);
});
