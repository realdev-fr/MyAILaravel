<?php

namespace App\Services;

use LLPhant\Chat\FunctionInfo\FunctionBuilder;
use LLPhant\Chat\FunctionInfo\FunctionInfo;
use LLPhant\Chat\FunctionInfo\Parameter;
use LLPhant\Chat\AnthropicChat;
use LLPhant\AnthropicConfig;

class AIService
{
    protected $chat;

    public function __construct()
    {
        $config = new AnthropicConfig(
            model: AnthropicConfig::CLAUDE_3_HAIKU, // Modèle le moins cher
            apiKey: env('ANTHROPIC_API_KEY') // Ajoutez ANTHROPIC_API_KEY dans votre .env
        );
        $this->chat = new AnthropicChat($config);

        //$action = new Parameter('action', 'string', 'either \'on\' or \'off\'');
        $state = new Parameter('state', 'string', 'either \'on\' or \'off\'');
        $device_name = new Parameter('device_name', 'string', 'place where the light is');

        /*
        $tool = new FunctionInfo(
            'manageLights',
            new LightsService(),
            'Turn on or off the lights',
            [$action]
        );
        */$tool = new FunctionInfo(
            'manageLightsMCP',
            new LightsService(new MCPService()),
            'Turn on or off the lights',
            [$state, $device_name]
        );

        /*
        $tool = new FunctionInfo(
            'manageLightsMCP',
            new LightsService(),
            'Turn on or off the concerned lights via MCP server',
            [$state, $device_name]
        );
        */


        $this->chat->addTool($tool);
        $this->chat->setSystemMessage('You are an AI that is able turn on or off lights by using your MCP tools');
    }

    public function ask(string $prompt): string
    {
        $result = $this->chat->generateTextOrReturnFunctionCalled($prompt);

        // Since manageLightsMCP now returns a string, result should always be a string
        if (is_array($result)) {
            // This shouldn't happen anymore, but keep as fallback
            return "Tool executed with result: " . json_encode($result);
        }

        return $result;
    }
}
