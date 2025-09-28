<?php

namespace App\Contracts;

interface MCPServiceInterface
{
    /**
     * Get available tools from the MCP server
     */
    public function getTools(): array;

    /**
     * Call a tool on the MCP server
     */
    public function callTool(string $toolName, array $arguments = []): array;

    /**
     * Get server information
     */
    public function getServerInfo(): array;
}