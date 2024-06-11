<?php

namespace MoeMizrak\LaravelPromptAlchemist\Resources\Templates;

/**
 * This class is responsible for creating a structured payload template based on a given prompt and function results which later will be sent to a ai provider.
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
     * @param array|null $functionResults - The results from functions that should be used to answer the prompt.
     * @param array|null $resultsSchema - The schema that defines the structure of the function results.
     *
     * @return array
     */
    public static function createPayload(string $prompt, ?array $functionResults, ?array $resultsSchema): array
    {
        return [
            'instructions' => 'You will strictly follow the instructions:
                - Understand the provided prompt and answer the prompt using the function_results (needed info is provided in function_results). If function_results are not sufficient enough, then your answer will be "Please provide more information about [missing information]"
                - Respond based on the function_results_schema sample provided (Do not add any extra info, exactly the same format provided in function_results_schema).
                - Format the response as an array following the structure in function_results_schema, without adding any explanatory sentences or context. 
                - Do not include any additional text or sentences other than the exact format as provided in function_results_schema.
                - Consider that the given response will be used in PHP code',
            'prompt'           => $prompt,
            'function_results' => $functionResults,
            'function_results_schema' => $resultsSchema,
        ];
    }
}