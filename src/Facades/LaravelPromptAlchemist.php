<?php

namespace MoeMizrak\LaravelPromptAlchemist\Facades;

use Illuminate\Support\Facades\Facade;
use MoeMizrak\LaravelPromptAlchemist\DTO\ErrorData;

/**
 * Facade for LaravelPromptAlchemist.
 *
 * @method static array preparePromptFunctionPayload(string $prompt) Retrieves the function list from the configuration file and combines it with the provided prompt.
 * @method static array prepareFunctionResultsPayload(string $prompt, array $functionResults) Prepares a payload for the function results based on the given prompt and function results.
 * @method static void callFunctions() This function will be implemented later.
 * @method static bool|ErrorData validateFunctionSignature(array $llmReturnedFunction) Validates a function signature returned by the LLM.
 * @method static bool|ErrorData generateFunctionList(string|object $class, array $functions, string $fileName) Generates a detailed function list from a given class and writes it to a file in YAML format.
 * @method static mixed generateInstructions() Generates instructions that can be used in config prompt_function_instructions.
 */
class LaravelPromptAlchemist extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-prompt-alchemist';
    }
}