<?php

namespace MoeMizrak\LaravelPromptAlchemist\Resources\Templates;

/**
 * This class is responsible for creating a structured payload template based on a given prompt and a list of functions which later will be sent to an AI provider.
 * The createPayload method constructs an array containing instructions, the prompt, a list of functions, and a function payload schema.
 * The instructions detail how the prompt should be processed, ensuring the response strictly adheres to the provided format.
 *
 * Class ContentPayloadTemplate
 * @package MoeMizrak\LaravelPromptAlchemist\Resources\Templates
 */
final class ContentPayloadTemplate
{
    /**
     * Create a structured payload template based on a given prompt and a list of functions regarding the function payload schema.
     *
     * @param string $prompt - The prompt that needs to be processed.
     * @param array|null $functions - The list of functions that can be used to respond to the prompt.
     * @param array|null $functionPayloadSchema - The schema that defines the structure of the function payload.
     *
     * @return array
     */
    public static function createPayload(string $prompt, ?array $functions, ?array $functionPayloadSchema): array
    {
        return [
            'instructions'    => 'This is a tool use (function calling) request. You will strictly follow the instructions:
                - Understand the provided prompt, and decide which function or functions (from provided functions list) is needed to respond to prompt
                - Respond based on the function_payload_schema sample provided (Do not add any extra info, exactly same format provided in function_payload_schema).
                - Do not add any explanation, sentences other than function_payload_schema formatted answer as described (give only function list as described in function_payload_schema)
                - Consider that given response will be used in PHP code',
            'prompt'          => $prompt,
            'functions'       => $functions,
            'function_payload_schema' => $functionPayloadSchema,
        ];
    }
}