<?php

namespace MoeMizrak\LaravelPromptAlchemist\Helpers;

use MoeMizrak\LaravelPromptAlchemist\Resources\Templates\ContentPayloadTemplate;
use MoeMizrak\LaravelPromptAlchemist\Resources\Templates\ResponsePayloadTemplate;
use Symfony\Component\Yaml\Yaml;

/**
 * This is a helper class for building payload for prompt function and function result.
 *
 * Class PayloadBuilder
 * @package MoeMizrak\LaravelPromptAlchemist\Helpers
 */
class PayloadBuilder
{
    /**
     * Builds payload for the prompt function payload.
     *
     * @param string $prompt
     *
     * @return array
     */
    public function buildPromptFunctionPayload(string $prompt): array
    {
        $functions = Yaml::parseFile(config('laravel-prompt-alchemist.functions_yml_path'));
        $functionPayloadSchema = Yaml::parseFile(config('laravel-prompt-alchemist.schemas.function_payload_schema_path'));
        $instructions = config('laravel-prompt-alchemist.instructions.content_payload_instructions');

        return ContentPayloadTemplate::createPayload($prompt, $instructions, $functions, $functionPayloadSchema);
    }

    /**
     * Builds payload for the function results payload regarding the given prompt and function results.
     *
     * @param string $prompt
     * @param array $functionResults
     *
     * @return array
     */
    public function buildFunctionResultsPayload(string $prompt, array $functionResults): array
    {
        $resultsSchema = Yaml::parseFile(config('laravel-prompt-alchemist.schemas.function_results_schema_path'));
        $instructions = config('laravel-prompt-alchemist.instructions.response_payload_instructions');

        return ResponsePayloadTemplate::createPayload($prompt, $instructions, $functionResults, $resultsSchema);
    }
}