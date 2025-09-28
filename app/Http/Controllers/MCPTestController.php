<?php

namespace App\Http\Controllers;

use App\Services\LightsService;
use App\Services\MCPService;
use App\Services\SimpleMCPService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MCPTestController extends Controller
{
    private LightsService $lightsService;
    private SimpleMCPService $mcpService;

    public function __construct(LightsService $lightsService, SimpleMCPService $mcpService)
    {
        $this->lightsService = $lightsService;
        $this->mcpService = $mcpService;
    }

    /**
     * Test MCP connection
     */
    public function testConnection(): JsonResponse
    {
        try {
            $info = $this->mcpService->getServerInfo();
            return response()->json([
                'success' => true,
                'server_info' => $info,
                'message' => 'MCP server connection test'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available MCP tools
     */
    public function getTools(): JsonResponse
    {
        try {
            $tools = $this->lightsService->getAvailableTools();
            return response()->json([
                'success' => true,
                'tools' => $tools
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Control device via MCP
     */
    public function controlDevice(Request $request): JsonResponse
    {
        $request->validate([
            'device_name' => 'required|string',
            'state' => 'required|string|in:on,off'
        ]);

        try {
            $response = $this->lightsService->manageLightsMCP(
                $request->state,
                $request->device_name
            );

            return response()->json([
                'success' => true,
                'device' => $request->device_name,
                'state' => $request->state,
                'mcp_response' => $response
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Call any MCP tool
     */
    public function callTool(Request $request): JsonResponse
    {
        $request->validate([
            'tool_name' => 'required|string',
            'arguments' => 'sometimes|array'
        ]);

        try {
            $response = $this->lightsService->callMCPTool(
                $request->tool_name,
                $request->arguments ?? []
            );

            return response()->json([
                'success' => true,
                'tool' => $request->tool_name,
                'arguments' => $request->arguments ?? [],
                'response' => $response
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
