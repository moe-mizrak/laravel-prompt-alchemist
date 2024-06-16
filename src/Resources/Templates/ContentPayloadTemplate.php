<?php

namespace MoeMizrak\LaravelPromptAlchemist\Resources\Templates;

/**
 * This class is responsible for creating a structured payload template based on a given prompt and a list of functions which later will be sent to an LLM provider.
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
     * @param string $instructions - The instructions for the llm provider about how to form the payload.
     * @param array|null $functions - The list of functions that can be used to respond to the prompt.
     * @param array|null $functionPayloadSchema - The schema that defines the structure of the function payload.
     *
     * @return array
     */
    public static function createPayload(string $prompt, string $instructions, ?array $functions, ?array $functionPayloadSchema): array
    {
        return [
            'prompt'                  => $prompt,
            'instructions'            => $instructions,
            'functions'               => $functions,
            'function_payload_schema' => $functionPayloadSchema,
        ];
    }
}