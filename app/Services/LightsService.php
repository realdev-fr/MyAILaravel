<?php

namespace App\Services;
use WebSocket\Client;
use Illuminate\Support\Facades\Log;
use App\Contracts\MCPServiceInterface;

class LightsService
{
    private MCPServiceInterface $mcpService;

    public function __construct(MCPServiceInterface $mcpService)
    {
        $this->mcpService = $mcpService;
    }

    public function __invoke(){}

    /**
     * Legacy method for direct API access
     */
    public function manageLights(string $action): void
    {
        $endpoint = $action === 'on' ? 'turn_on_devices' : 'turn_off_devices';

        $url = "http://192.168.1.25:9999/$endpoint";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response, true);
    }

    /**
     * New MCP-based method for device management
     */
    public function manageLightsMCP(string $state, string $device_name): string
    {
        try {
            $response = $this->mcpService->callTool('home_automation_toggle_device', [
                'device_name' => $device_name,
                'state' => $state,
            ]);

            Log::info("Device management via MCP successful", [
                'device' => $device_name,
                'state' => $state,
                'response' => $response
            ]);

            // Return a user-friendly string instead of raw array
            if (isset($response['error'])) {
                return "Failed to control {$device_name}: " . ($response['error']['message'] ?? 'Unknown error');
            } elseif (isset($response['result'])) {
                return "Successfully turned {$state} the {$device_name}";
            } else {
                return "Light command sent to {$device_name} (state: {$state})";
            }
        } catch (\Exception $e) {
            Log::error("Device management via MCP failed", [
                'device' => $device_name,
                'state' => $state,
                'error' => $e->getMessage()
            ]);

            return "Error controlling {$device_name}: " . $e->getMessage();
        }
    }

    /**
     * Get available tools from MCP server
     */
    public function getAvailableTools(): array
    {
        return $this->mcpService->getTools();
    }

    /**
     * Get MCP server status
     */
    public function getMCPStatus(): array
    {
        return $this->mcpService->getServerInfo();
    }

    /**
     * Call any MCP tool
     */
    public function callMCPTool(string $toolName, array $arguments = []): array
    {
        return $this->mcpService->callTool($toolName, $arguments);
    }
}
