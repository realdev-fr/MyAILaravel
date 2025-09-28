<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MCP Server Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for connecting to your MCP (Model Context Protocol) server.
    | This includes the server URL and name.
    |
    */

    'server_url' => env('MCP_SERVER_URL', 'http://127.0.0.1:8000'),
    'server_name' => env('MCP_SERVER_NAME', 'discuss'),

    /*
    |--------------------------------------------------------------------------
    | Transport Type
    |--------------------------------------------------------------------------
    |
    | Transport type to use: 'http' for SSE, 'stdio' for subprocess
    |
    */

    'transport_type' => env('MCP_TRANSPORT_TYPE', 'http'), // 'http' ou 'stdio'

    /*
    |--------------------------------------------------------------------------
    | Connection Settings
    |--------------------------------------------------------------------------
    |
    | Additional connection settings for the MCP client.
    |
    */

    'timeout' => env('MCP_TIMEOUT', 30),
    'retry_attempts' => env('MCP_RETRY_ATTEMPTS', 3),

    /*
    |--------------------------------------------------------------------------
    | Stdio Configuration (pour serveur Python avec uv)
    |--------------------------------------------------------------------------
    |
    | Configuration pour le transport stdio avec votre serveur Python
    |
    */

    'stdio' => [
        'command' => env('MCP_STDIO_COMMAND', 'uv'),
        'args' => [
            'run',
            env('MCP_PYTHON_SCRIPT', '/Users/realdev/PycharmProjects/MyAI/mcp_server.py'),
            '--server_type=stdio'
        ],
        'working_dir' => env('MCP_WORKING_DIR', '/Users/realdev/PycharmProjects/MyAI'),
        'env' => [
            'PYTHONPATH' => env('MCP_WORKING_DIR', '/Users/realdev/PycharmProjects/MyAI')
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    |
    | Enable debug logging for MCP communications.
    |
    */

    'debug' => env('MCP_DEBUG', false),
];
