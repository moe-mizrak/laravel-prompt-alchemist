<?php

namespace MoeMizrak\LaravelPromptAlchemist;

use MoeMizrak\LaravelOpenrouter\Exceptions\XorValidationException;
use MoeMizrak\LaravelPromptAlchemist\DTO\ErrorData;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

/**
 * Handles requests to the Prompt Alchemist, integrating with various services and configurations to process prompts,
 * validate function signatures, and form payloads for LLM interactions.
 *
 * Class PromptAlchemistRequest
 * @package MoeMizrak\LaravelPromptAlchemist
 */
class PromptAlchemistRequest extends PromptAlchemistAPI
{
    /**
     * Prepares a payload for the prompt function based on the given prompt along with functions, function payload schema and instructions.
     *
     * This method prepares the data in a way that the LLM provider can process to determine
     * which functions to use for generating the desired response. The output is structured to ensure
     * the LLM provider returns a well-defined list of functions to be called based on the prompt.
     *
     * @param string $prompt The user's input or query that requires LLM processing.
     *
     * @return array The formatted payload combining the prompt and function list along with schema, ready for the LLM provider.
     */
    public function preparePromptFunctionPayload(string $prompt): array
    {
        return $this->payloadBuilder->buildPromptFunctionPayload($prompt);
    }

    /**
     * Prepares a payload for the function results based on the given prompt and function results along with function results schema and instructions.
     * This method ensures the results adhere to the defined schema, ready for processing by the LLM provider.
     *
     * @param string $prompt
     * @param array $functionResults
     *
     * @return array
     */
    public function prepareFunctionResultsPayload(string $prompt, array $functionResults): array
    {
        return $this->payloadBuilder->buildFunctionResultsPayload($prompt, $functionResults);
    }

    /**
     * Validates a function signature returned by the LLM.
     *
     * @param array $llmReturnedFunction
     *
     * @return bool|ErrorData
     * @throws UnknownProperties
     */
    public function validateFunctionSignature(array $llmReturnedFunction): bool|ErrorData
    {
        return $this->functionSignatureValidator->signatureValidator($llmReturnedFunction);
    }

    /**
     * Generates a detailed function list from a given class and writes it to a file in YAML format.
     *
     * This function reflects on the specified class to retrieve information about its functions,
     * including parameters, visibility, and return types etc. It prioritizes provided function
     * definitions and descriptions over those predefined in the class.
     *
     * @param string|object $class
     * @param array $functions
     * @param string $fileName
     *
     * @return bool|ErrorData
     * @throws UnknownProperties
     * @throws \ReflectionException
     */
    public function generateFunctionList(string|object $class, array $functions, string $fileName): bool|ErrorData
    {
        return $this->functionListGenerator->generate($class, $functions, $fileName);
    }

    /**
     * Generate instructions that can be used in config content_payload_instructions.
     *
     * @return mixed
     * @throws XorValidationException
     * @throws UnknownProperties
     */
    public function generateInstructions(): mixed
    {
        return $this->instructionsGenerator->generate();
    }
}