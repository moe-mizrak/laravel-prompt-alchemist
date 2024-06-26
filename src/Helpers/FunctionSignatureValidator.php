<?php

namespace MoeMizrak\LaravelPromptAlchemist\Helpers;

use Illuminate\Support\Arr;
use MoeMizrak\LaravelPromptAlchemist\DTO\ErrorData;
use MoeMizrak\LaravelPromptAlchemist\DTO\FunctionData;
use MoeMizrak\LaravelPromptAlchemist\DTO\ParameterData;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Symfony\Component\Yaml\Yaml;

/**
 * This is a helper class for validating function signatures.
 *
 * Class FunctionSignatureValidator
 * @package MoeMizrak\LaravelPromptAlchemist\Helpers
 */
class FunctionSignatureValidator
{
    /**
     * Validate function signature.
     *
     * @param FunctionData $llmReturnedFunctionData
     *
     * @return bool|ErrorData
     * @throws UnknownProperties
     */
    public function signatureValidator(FunctionData $llmReturnedFunctionData): bool|ErrorData
    {
        // Formed callable function data (FunctionData).
        $callableFunctionData = $this->formCallableFunctionData($llmReturnedFunctionData);

        return $this->validate($callableFunctionData, $llmReturnedFunctionData);
    }

    /**
     * Forms data from the function returned by the LLM based on the signature mapping.
     *
     * @param array $llmReturnedFunction - This is the function that will be formed in function data.
     *
     * @return FunctionData
     * @throws UnknownProperties
     */
    public function formLlmReturnedFunctionData(array $llmReturnedFunction): FunctionData
    {
        // Get the signature mapping (path and type info of fields in array)
        $signatureMappingData = config('laravel-prompt-alchemist.function_signature_mapping');
        // LLM returned function name.
        $llmReturnedFunctionName = Arr::get($llmReturnedFunction, $signatureMappingData->function_name->path);
        // LLM returned parameters.
        $llmReturnedParameters = Arr::get($llmReturnedFunction, $signatureMappingData->parameters->path);
        // LLm returned class name of the function
        $llmReturnedClassName= Arr::get($llmReturnedFunction, $signatureMappingData->class_name->path);

        // Loop through the LLm returned parameters and set parameter name and type.
        $key = 0;
        $parameters = [];
        foreach ($llmReturnedParameters as $_) {
            $parameters[] = new ParameterData([
                'name'  => Arr::get($llmReturnedFunction, $this->modifyMappingPath($signatureMappingData->parameter_name->path, $key)),
                'type'  => Arr::get($llmReturnedFunction,  $this->modifyMappingPath($signatureMappingData->parameter_type->path, $key)),
                'value' => Arr::get($llmReturnedFunction,  $this->modifyMappingPath($signatureMappingData->parameter_value->path, $key)),
            ]);

            $key++;
        }

        // Return the function data for llm returned function.
        return new FunctionData([
            'function_name' => $llmReturnedFunctionName,
            'parameters'    => $parameters,
            'class_name'    => $llmReturnedClassName
        ]);
    }

    /**
     * Forms callable function data based on the signature mapping.
     *
     * @param FunctionData $llmReturnedFunctionData - This is for retrieving the correct function from function list by comparing with $llmReturnedFunctionName and $llmReturnedClassName.
     *
     * @return FunctionData
     * @throws UnknownProperties
     */
    private function formCallableFunctionData(FunctionData $llmReturnedFunctionData): FunctionData
    {
        // Get the signature mapping (path and type info of fields in array)
        $signatureMappingData = config('laravel-prompt-alchemist.function_signature_mapping');
        // Function list with signatures (names, parameters etc.). This is the function that will be formed in function data.
        $callableFunctions = Yaml::parseFile(config('laravel-prompt-alchemist.functions_yml_path'));
        $callableFunction = null;
        $llmReturnedFunctionName = $llmReturnedFunctionData->function_name;
        $llmReturnedClassName = $llmReturnedFunctionData->class_name;

        // Loop through function list and find the same function with the LLM returned function.
        foreach ($callableFunctions as $function) {
            $callableFunctionName = Arr::get($function, $signatureMappingData->function_name->path);
            $callableClassName = Arr::get($function, $signatureMappingData->class_name->path);
            if ($callableFunctionName === $llmReturnedFunctionName && $callableClassName === $llmReturnedClassName) {
                $callableFunction = $function;
                break;
            }
        }

        // Callable function parameters.
        $callableFunctionParameters = Arr::get($callableFunction, $signatureMappingData->parameters->path, []);

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
            'function_name' => Arr::get($callableFunction, $signatureMappingData->function_name->path),
            'parameters'    => $parameters,
            'class_name'    => Arr::get($callableFunction, $signatureMappingData->class_name->path),
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
     * @param FunctionData $callableFunctionData
     * @param FunctionData $llmReturnedFunctionData
     *
     * @return ErrorData|true
     * @throws UnknownProperties
     */
    private function validate(FunctionData $callableFunctionData, FunctionData $llmReturnedFunctionData): bool|ErrorData
    {
        // If there are any mismatched function name / class name, return the error along with the mismatched function name / class name.
        if (
            $callableFunctionData->function_name !== $llmReturnedFunctionData->function_name ||
            $callableFunctionData->class_name !== $llmReturnedFunctionData->class_name
        ) {
            return new ErrorData([
                'code'    => 400,
                'message' => 'Function signature is wrong, unexpected function name '. $llmReturnedFunctionData->function_name . ' or class name '.$llmReturnedFunctionData->class_name,
            ]);
        }

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

        // Loop through the LLM returned function parameters and check if the value is in correct type. If value is not set, skip this check.
        foreach ($llmReturnedFunctionData->parameters as $llmReturnedParameter) {
            // Check if value is set, skip the check if not set.
            if (isset($llmReturnedParameter->value)) {
                // Check if parameter type and type of the value matches.
                if (! $this->isParameterValueInCorrectType($llmReturnedParameter)) {
                    return new ErrorData([
                        'code'    => 400,
                        'message' => 'Function signature is wrong, parameter type ' . $llmReturnedParameter->type . ' does NOT match with the value ' . $llmReturnedParameter->value,
                    ]);
                }
            }
        }

        return true;
    }

    /**
     * Checks if parameter type and type of the value matches.
     *
     * @param ParameterData $llmReturnedParameter
     *
     * @return bool
     */
    private function isParameterValueInCorrectType(ParameterData $llmReturnedParameter): bool
    {
        $value = $llmReturnedParameter->value;
        $type = $llmReturnedParameter->type;

        return match ($type) {
            'int'    => is_int($value),
            'string' => is_string($value),
            'float'  => is_float($value),
            'bool'   => is_bool($value),
            'array'  => is_array($value),
            'object' => is_object($value),
            'null'   => is_null($value),
            default  => false,
        };
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