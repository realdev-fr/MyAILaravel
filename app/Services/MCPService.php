<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use PhpMcp\Client\Enum\TransportType;
use PhpMcp\Client\ServerConfig;
use PhpMcp\Client\Client;
use PhpMcp\Client\ClientBuilder;
use App\Contracts\MCPServiceInterface;

class MCPService implements MCPServiceInterface
{
    private ?Client $client = null;
    private ServerConfig $serverConfig;
    private string $serverUrl;
    private string $serverName;

    public function __construct()
    {
        $this->serverUrl = config('mcp.server_url', 'http://127.0.0.1:8000');
        $this->serverName = config('mcp.server_name', 'discuss');

        $transportType = config('mcp.transport_type', 'http');

        if ($transportType === 'stdio') {
            // Configuration stdio pour votre serveur Python avec uv
            $this->serverConfig = new ServerConfig(
                name: $this->serverName,
                transport: TransportType::Stdio,
                timeout: config('mcp.timeout', 30.0),
                command: config('mcp.stdio.command', 'uv'),
                args: config('mcp.stdio.args', []),
                workingDir: config('mcp.stdio.working_dir'),
                env: config('mcp.stdio.env', [])
            );
        } else {
            // Configuration HTTP/SSE
            $this->serverConfig = new ServerConfig(
                name: $this->serverName,
                transport: TransportType::Http,
                timeout: 10,
                url: $this->serverUrl . '/sse',
                headers: [
                    'Content-Type' => 'application/json',
                    'Accept' => 'text/event-stream'
                ]
            );
        }
        $this->connect();
    }

    /**
     * Initialize the MCP client connection
     */
    public function connect(): void
    {
        if ($this->client !== null) {
            return;
        }

        try {
            $this->client = ClientBuilder::make()
                ->withClientInfo('Laravel MCP Client', '1.0.0')
                ->withServerConfig($this->serverConfig)
                ->build();

            // Initialize the connection
            $this->client->initialize();

            Log::info("MCP Client connected to {$this->serverUrl}");
        } catch (Exception $e) {
            Log::error("Failed to connect to MCP server: " . $e->getMessage());
            throw $e;
        } catch (\Throwable $e) {
            Log::error("Failed to connect to MCP server: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get available tools from the MCP server
     */
    public function getTools(): array
    {
        $this->ensureConnected();

        try {
            $tools = $this->client->listTools();

            Log::info("Retrieved tools from MCP server", [
                'server' => $this->serverName,
                'tools_count' => count($tools)
            ]);

            return $tools;
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
        $this->ensureConnected();

        try {
            $result = $this->client->callTool($toolName, $arguments);

            Log::info("MCP Tool called successfully", [
                'tool' => $toolName,
                'arguments' => $arguments,
                'response' => $result
            ]);

            // Convert CallToolResult to array
            return $result->toArray();
        } catch (\Exception $e) {
            Log::error("Failed to call MCP tool: " . $e->getMessage(), [
                'tool' => $toolName,
                'arguments' => $arguments
            ]);
            throw $e;
        }
    }

    /**
     * Ensure the client is connected
     */
    private function ensureConnected(): void
    {
        if ($this->client === null) {
            $this->connect();
        }
    }

    /**
     * Disconnect from the MCP server
     */
    public function disconnect(): void
    {
        if ($this->client !== null) {
            $this->client->disconnect();
            $this->client = null;
        }
    }

    /**
     * Get server information
     */
    public function getServerInfo(): array
    {
        return [
            'url' => $this->serverUrl,
            'name' => $this->serverName,
            'connected' => $this->client !== null
        ];
    }
}
