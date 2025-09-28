<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use App\Contracts\MCPServiceInterface;

class SimpleMCPService implements MCPServiceInterface
{
    private string $serverUrl;
    private string $serverName;
    private string $pythonScript;
    private string $workingDir;

    public function __construct()
    {
        $this->serverUrl = config('mcp.server_url', 'http://127.0.0.1:8000');
        $this->serverName = config('mcp.server_name', 'discuss');
        $this->pythonScript = config('mcp.stdio.args.1', '/Users/realdev/PycharmProjects/MyAI/mcp_server.py');
        $this->workingDir = config('mcp.stdio.working_dir', '/Users/realdev/PycharmProjects/MyAI');
    }

    /**
     * Get available tools from the MCP server
     */
    public function getTools(): array
    {
        try {
            $request = [
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'tools/list',
                'params' => []
            ];

            $response = $this->callMCPServer($request);

            Log::info("Retrieved tools from MCP server", [
                'server' => $this->serverName,
                'response' => $response
            ]);

            return $response['result']['tools'] ?? [];
        } catch (\Exception $e) {
            Log::error("Failed to get tools from MCP server: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Call a tool on the MCP server
     */
    public function callTool(string $toolName, array $arguments = []): array
    {
        try {
            $request = [
                'jsonrpc' => '2.0',
                'id' => 2,
                'method' => 'tools/call',
                'params' => [
                    'name' => $toolName,
                    'arguments' => [
                        "device_name" => "salon",
                        "state" => "on"
                    ]
                ]
            ];

            $response = $this->callMCPServer($request);

            Log::info("MCP Tool called successfully", [
                'tool' => $toolName,
                'arguments' => $arguments,
                'response' => $response,
                'request' => $request
            ]);


            return $response['result'] ?? $response;
        } catch (\Exception $e) {
            Log::error("Failed to call MCP tool: " . $e->getMessage(), [
                'tool' => $toolName,
                'arguments' => $arguments
            ]);
            throw $e;
        }
    }

    /**
     * Call the MCP server via stdio
     */
    private function callMCPServer(array $request): array
    {
        // Use a persistent session approach by sending init and request together
        // but parsing all JSON responses
        $tempFile = tempnam(sys_get_temp_dir(), 'mcp_request_');

        // First send initialize
        $initRequest = [
            'jsonrpc' => '2.0',
            'id' => 0,
            'method' => 'initialize',
            'params' => [
                'protocolVersion' => '2024-11-05',
                'capabilities' => [
                    'tools' => []
                ],
                'clientInfo' => [
                    'name' => 'Laravel MCP Client',
                    'version' => '1.0.0'
                ]
            ]
        ];

        $input = json_encode($initRequest) . "\n" . json_encode($request) . "\n";
        file_put_contents($tempFile, $input);

        Log::debug("MCP Request JSON", ['json' => json_encode($input, JSON_PRETTY_PRINT)]);


        $command = sprintf(
            'cd %s && timeout 10s uv run %s --server_type=stdio < %s',
            escapeshellarg($this->workingDir),
            escapeshellarg($this->pythonScript),
            escapeshellarg($tempFile)
        );

        Log::info("Calling MCP server", [
            'command' => $command,
            'request' => $request
        ]);

        $output = shell_exec($command . ' 2>&1');
        unlink($tempFile);

        if ($output === null) {
            throw new \Exception("Failed to execute MCP command");
        }

        Log::info("MCP raw output", ['output' => $output]);

        // Parse all JSON responses from the output
        $lines = explode("\n", trim($output));
        $responses = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $decoded = json_decode($line, true);
            if ($decoded && isset($decoded['jsonrpc']) && isset($decoded['id'])) {
                $responses[$decoded['id']] = $decoded;
            }
        }

        // Return the response for our request ID
        if (isset($responses[$request['id']])) {
            dd($responses);
            return $responses[$request['id']];
        }

        // If we don't get the expected response, but got initialize response,
        // the server might not support the method
        if (isset($responses[0])) {
            $initResponse = $responses[0];

            // For tools/list, we can extract from server capabilities
            if ($request['method'] === 'tools/list') {
                dd($responses);
                $tools = $this->extractToolsFromCapabilities($initResponse);
                if (!empty($tools)) {
                    return [
                        'jsonrpc' => '2.0',
                        'id' => $request['id'],
                        'result' => ['tools' => $tools]
                    ];
                }
            }
        }

        throw new \Exception("No valid response found for request ID {$request['id']}. Available responses: " . implode(', ', array_keys($responses)) . ". Output: " . $output);
    }

    /**
     * Extract tools from server capabilities if available
     */
    private function extractToolsFromCapabilities(array $initResponse): array
    {
        // This is a fallback - try to get tools info from server capabilities
        // You might need to adjust this based on your server's actual response structure
        $serverInfo = $initResponse['result']['serverInfo'] ?? [];

        // Return hardcoded tools based on what we know your server supports
        // You can modify this list based on your actual tools
        return [
            [
                'name' => 'weather',
                'description' => 'Get the weather in a location',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'location' => ['type' => 'string']
                    ]
                ]
            ],
            [
                'name' => 'time',
                'description' => 'Get the current time',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => []
                ]
            ],
            [
                'name' => 'home_automation_toggle_device',
                'description' => 'Toggle device state (on/off)',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'device_name' => ['type' => 'string'],
                        'state' => ['type' => 'string']
                    ]
                ]
            ]
        ];
    }

    /**
     * Get server information
     */
    public function getServerInfo(): array
    {
        return [
            'url' => $this->serverUrl,
            'name' => $this->serverName,
            'connected' => true // Always true for stdio
        ];
    }
}
