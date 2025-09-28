<?php

namespace App\Services;
use WebSocket\Client;

class LightsService
{
    public function __invoke(){}

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

    public function manageLightsMCP(string $state, string $device_name): void
    {
        /*
        // Appel à votre serveur MCP Python avec format FastMCP
        // Appel à votre serveur MCP Python
        $data = [
            'name' => 'home_automation_toggle_device',
            'params' => [
                'device_name' => $device_name,
                'state' => $state,
            ]
        ];

        $ch = curl_init('http://127.0.0.1:8000/sse');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        curl_close($ch);

        error_log("MCP Response: " . $response);
        */

    }

}
