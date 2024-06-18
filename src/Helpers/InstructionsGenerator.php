<?php

namespace MoeMizrak\LaravelPromptAlchemist\Helpers;

use Illuminate\Support\Arr;
use MoeMizrak\LaravelOpenrouter\DTO\ChatData;
use MoeMizrak\LaravelOpenrouter\DTO\MessageData;
use MoeMizrak\LaravelOpenrouter\Exceptions\XorValidationException;
use MoeMizrak\LaravelOpenrouter\Facades\LaravelOpenRouter;
use MoeMizrak\LaravelOpenrouter\Types\RoleType;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Symfony\Component\Yaml\Yaml;

/**
 * This is a helper class for generating instructions.
 *
 * Class InstructionsGenerator
 * @package MoeMizrak\LaravelPromptAlchemist\Helpers
 */
class InstructionsGenerator
{
    /**
     * Generate instructions.
     *
     * @return mixed
     * @throws XorValidationException
     * @throws UnknownProperties
     */
    public function generate(): mixed
    {
        // Get functions, functionPayloadSchema and prompt for instructions generation from the config.
        $functions = Yaml::parseFile(config('laravel-prompt-alchemist.functions_yml_path'));
        $functionPayloadSchema = Yaml::parseFile(config('laravel-prompt-alchemist.schemas.function_payload_schema_path'));
        $prompt = config('laravel-prompt-alchemist.instructions.generate_content_payload_instructions_prompt');

        $content = [
            'prompt'                  => $prompt,
            'functions'               => $functions,
            'function_payload_schema' => $functionPayloadSchema,
        ];

        /*
         * Prepare MessageData DTO for OpenRouter request
         */
        $messageData = new MessageData([
            'content' => json_encode($content),
            'role'    => RoleType::USER,
        ]);

        /*
         * Prepare ChatData DTO for OpenRouter request
         */
        $chatData = new ChatData([
            'messages' => [
                $messageData,
            ],
            'model'   => config('laravel-prompt-alchemist.env_variables.default_model'),
        ]);

        // Make OpenRouter request with prepared ChatData
        $openRouterResponse = LaravelOpenRouter::chatRequest($chatData);

        // Return the instructions created.
        return Arr::get($openRouterResponse->choices[0], 'message.content');
    }
}