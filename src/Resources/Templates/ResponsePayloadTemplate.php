<?php

namespace MoeMizrak\LaravelPromptAlchemist\Resources\Templates;

/**
 * This class is responsible for creating a structured payload template based on a given prompt and function results which later will be sent to a LLM provider.
 * The createPayload method constructs an array containing instructions, the prompt, function results, and a results schema.
 * The instructions detail how the prompt should be processed, ensuring the response strictly adheres to the provided format.
 *
 * Class ResponsePayloadTemplate
 * @package MoeMizrak\LaravelPromptAlchemist\Resources\Templates
 */
final class ResponsePayloadTemplate
{
    /**
     * Create a structured payload template based on a given prompt and function results regarding the function result schema.
     *
     * @param string $prompt - The prompt that needs to be processed.
     * @param string $instructions - The instructions for the llm provider about how to form the payload.
     * @param array|null $functionResults - The results from functions that should be used to answer the prompt.
     * @param array|null $resultsSchema - The schema that defines the structure of the function results.
     *
     * @return array
     */
    public static function createPayload(string $prompt, string $instructions, ?array $functionResults, ?array $resultsSchema): array
    {
        return [
            'prompt'                  => $prompt,
            'instructions'            => $instructions,
            'function_results'        => $functionResults,
            'function_results_schema' => $resultsSchema,
        ];
    }
}