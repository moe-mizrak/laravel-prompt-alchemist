<?php

namespace MoeMizrak\LaravelPromptAlchemist;

use Illuminate\Support\Arr;
use MoeMizrak\LaravelOpenrouter\DTO\ChatData;
use MoeMizrak\LaravelOpenrouter\DTO\ResponseData;
use MoeMizrak\LaravelOpenrouter\Facades\LaravelOpenRouter;
use MoeMizrak\LaravelPromptAlchemist\DTO\ErrorData;
use MoeMizrak\LaravelPromptAlchemist\DTO\FunctionData;
use MoeMizrak\LaravelPromptAlchemist\DTO\FunctionSignatureMappingData;
use MoeMizrak\LaravelPromptAlchemist\DTO\ParameterData;
use MoeMizrak\LaravelPromptAlchemist\Resources\Templates\ContentPayloadTemplate;
use MoeMizrak\LaravelPromptAlchemist\Resources\Templates\ResponsePayloadTemplate;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

/**
 * Handles requests to the Prompt Alchemist, integrating with various services and configurations to process prompts,
 * validate function signatures, and form payloads for AI interactions.
 *
 * Class PromptAlchemistRequest
 * @package MoeMizrak\LaravelPromptAlchemist
 */
class PromptAlchemistRequest extends PromptAlchemistAPI
{
    /**
     * Makes open router chat request and retrieve the response
     *
     * @param ChatData $chatData
     * @return ResponseData
     */
    protected function openRouterChatRequest(ChatData $chatData): ResponseData
    {
        return LaravelOpenRouter::chatRequest($chatData);
    }

    /**
     * Retrieves the function list from the configuration file and combines it with the provided prompt.
     *
     * This method prepares the data in a way that the AI provider can process to determine
     * which functions to use for generating the desired response. The output is structured to ensure
     * the AI provider returns a well-defined list of functions to be called based on the prompt.
     *
     * @param string $prompt The user's input or query that requires AI processing.
     *
     * @return array The formatted payload combining the prompt and function list along with schema, ready for the AI provider.
     */
    public function preparePromptFunctionPayload(string $prompt): array
    {
        $functions = config('laravel-prompt-alchemist.functions');
        $functionPayloadSchema = config('laravel-prompt-alchemist.schemas.function_payload_schema');

        return ContentPayloadTemplate::createPayload($prompt, $functions, $functionPayloadSchema);
    }

    /**
     * Prepares a payload for the function results based on the given prompt and function results.
     * This method ensures the results adhere to the defined schema, ready for processing by the AI provider.
     *
     * @param string $prompt
     * @param array $functionResults
     * @return array
     */
    public function prepareFunctionResultsPayload(string $prompt, array $functionResults): array
    {
        $resultsSchema = config('laravel-prompt-alchemist.schemas.function_results_schema');

        return ResponsePayloadTemplate::createPayload($prompt, $functionResults, $resultsSchema);
    }

    /**
     * This function will be implemented later.
     * @return void
     */
    public function callFunctions()
    {
        // here it will make the calls for the functions
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
        // Function signature mapping data (FunctionSignatureMappingData).
        $signatureMappingData = config('laravel-prompt-alchemist.function_signature_mapping');

        // Formed LLM returned function data (FunctionData).
        $llmReturnedFunctionData = $this->formLlmReturnedFunctionData($signatureMappingData, $llmReturnedFunction);

        // Formed callable function data (FunctionData).
        $callableFunctionData = $this->formCallableFunctionData($signatureMappingData, $llmReturnedFunctionData->function_name);

        return $this->validate($callableFunctionData, $llmReturnedFunctionData);
    }

    /**
     * Forms data from the function returned by the LLM based on the signature mapping.
     *
     * @param FunctionSignatureMappingData $signatureMappingData - This is for the signature mapping (path and type info of fields in array)
     * @param array $llmReturnedFunction - This is the function that will be formed in function data.
     *
     * @return FunctionData
     * @throws UnknownProperties
     */
    private function formLlmReturnedFunctionData(FunctionSignatureMappingData $signatureMappingData, array $llmReturnedFunction): FunctionData
    {
        // LLM returned function name.
        $llmReturnedFunctionName = Arr::get($llmReturnedFunction, $signatureMappingData->function_name->path);
        // LLM returned parameters.
        $llmReturnedParameters = Arr::get($llmReturnedFunction, $signatureMappingData->parameters->path);

        // Loop through the LLm returned parameters and set parameter name and type.
        $key = 0;
        $parameters = [];
        foreach ($llmReturnedParameters as $_) {
            $parameters[] = new ParameterData([
                'name' => Arr::get($llmReturnedFunction, $this->modifyMappingPath($signatureMappingData->parameter_name->path, $key)),
                'type' => Arr::get($llmReturnedFunction,  $this->modifyMappingPath($signatureMappingData->parameter_type->path, $key)),
            ]);
            $key++;
        }

        // Return the function data for llm returned function.
        return new FunctionData([
            'function_name' => $llmReturnedFunctionName,
            'parameters'    => $parameters,
        ]);
    }

    /**
     * Forms callable function data based on the signature mapping.
     *
     * @param FunctionSignatureMappingData $signatureMappingData - This is for the signature mapping (path and type info of fields in array)
     * @param string $llmReturnedFunctionName - This is for retrieving the correct function from functions list by comparing with $llmReturnedFunctionName.
     *
     * @return FunctionData
     * @throws UnknownProperties
     */
    private function formCallableFunctionData(FunctionSignatureMappingData $signatureMappingData, string $llmReturnedFunctionName): FunctionData
    {
        // Function list with signatures (names, parameters etc.). This is the function that will be formed in function data.
        $callableFunctions = config('laravel-prompt-alchemist.functions');
        $callableFunction = null;
        $callableFunctionName = '';

        // Loop through functions list and find the same function with the LLM returned function.
        foreach ($callableFunctions as $function) {
            $callableFunctionName = Arr::get($function, $signatureMappingData->function_name->path);
            if ($callableFunctionName === $llmReturnedFunctionName) {
                $callableFunction = $function;
                break;
            }
        }

        // Callable function parameters.
        $callableFunctionParameters = Arr::get($callableFunction, $signatureMappingData->parameters->path);

        // Loop through the parameters and set parameter name and type.
        $key = 0;
        $parameters = [];
        foreach ($callableFunctionParameters as $_) {
            $parameters[] = new ParameterData([
                'name'     => Arr::get($callableFunction, $this->modifyMappingPath($signatureMappingData->parameter_name->path, $key)),
                'type'     => Arr::get($callableFunction,  $this->modifyMappingPath($signatureMappingData->parameter_type->path, $key)),
                'required' => Arr::get($callableFunction,  $this->modifyMappingPath($signatureMappingData->parameter_required_info->path, $key)),
            ]);
            $key++;
        }

        // Return the function data for callable function.
        return new FunctionData([
            'function_name' => $callableFunctionName,
            'parameters'    => $parameters,
        ]);
    }

    /**
     * Modifies the mapping path by replacing placeholders with actual indexes.
     *
     * @param string $path
     * @param int|null $key
     *
     * @return array|string|string[]
     */
    private function modifyMappingPath(string $path, ?int $key): array|string
    {
        return str_replace('[]', '.' . $key, $path);
    }

    /**
     * Validates the callable function data against the LLM returned function data.
     *
     * @param $callableFunctionData
     * @param $llmReturnedFunctionData
     *
     * @return ErrorData|true
     * @throws UnknownProperties
     */
    private function validate($callableFunctionData, $llmReturnedFunctionData): bool|ErrorData
    {
        /*
         * This part checks if there is any mismatch between llm returned function parameters and callable function parameters, and gives the mismatched parameter names.
         */
        $callableParameterNames = array_map(fn ($item) => $item->name, $callableFunctionData->parameters);
        $llmReturnedParameterNames = array_map(fn ($item) => $item->name, $llmReturnedFunctionData->parameters);
        $mismatchedNames = array_diff($llmReturnedParameterNames, $callableParameterNames);

        // If there are any mismatched parameter name, return the error along with the mismatched parameter names.
        if (! empty($mismatchedNames)) {
            return new ErrorData([
                'code'    => 400,
                'message' => 'Function signature is wrong, unexpected parameter(s) '. implode(", ", $mismatchedNames),
            ]);
        }

        // Loop through the callable function parameters and check if the required parameter is present in the LLM returned parameters.
        foreach ($callableFunctionData->parameters as $callableParameter) {
            $required = $callableParameter->required ?? false;

            if ($required && ! $this->isParameterNameAndTypeMatches($callableParameter, $llmReturnedFunctionData->parameters)) {
                return new ErrorData([
                    'code'    => 400,
                    'message' => 'Function signature is wrong, required parameter ' . $callableParameter->name . ' is not present',
                ]);
            }
        }

        return true;
    }

    /**
     * Checks if a callable parameter is name and type matches LLM returned parameters.
     *
     * @param ParameterData $callableParameter
     * @param array $llmReturnedParameters
     *
     * @return bool
     */
    private function isParameterNameAndTypeMatches(ParameterData $callableParameter, array $llmReturnedParameters): bool
    {
        // Loop through the LLM returned parameters and check if the name and type matches the callable parameter.
        foreach ($llmReturnedParameters as $llmReturnedParameter) {
            if ($llmReturnedParameter->name === $callableParameter->name && $llmReturnedParameter->type === $callableParameter->type) {
                return true;
            }
        }

        return false;
    }
}