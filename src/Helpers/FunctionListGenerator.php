<?php

namespace MoeMizrak\LaravelPromptAlchemist\Helpers;

use Illuminate\Support\Arr;
use MoeMizrak\LaravelPromptAlchemist\DTO\DocBlockData;
use MoeMizrak\LaravelPromptAlchemist\DTO\ErrorData;
use MoeMizrak\LaravelPromptAlchemist\DTO\FunctionData;
use MoeMizrak\LaravelPromptAlchemist\DTO\ParameterData;
use MoeMizrak\LaravelPromptAlchemist\DTO\ReturnData;
use MoeMizrak\LaravelPromptAlchemist\Types\VisibilityType;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;
use ReflectionMethod;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Symfony\Component\Yaml\Yaml;

/**
 * This is a helper class for generating function list.
 *
 * Class FunctionListGenerator
 * @package MoeMizrak\LaravelPromptAlchemist\Helpers
 */
class FunctionListGenerator
{
    /**
     * Generate a detailed function list from a given class and writes it to a file in YAML format.
     *
     * @param string|object $class
     * @param array $functions
     * @param string $fileName
     *
     * @return bool|ErrorData
     * @throws UnknownProperties
     * @throws \ReflectionException
     */
    public function generate(string|object $class, array $functions, string $fileName): bool|ErrorData
    {
        // Initiate Reflection class for given class name.
        $reflectionClass = new ReflectionClass($class);
        $functionList = [];

        foreach ($functions as $receivedFunctionData) {
            // Get the function name from the received function data.
            $functionName = $this->getFunctionName($receivedFunctionData);

            // Check if the function exists in the class.
            if (! $reflectionClass->hasMethod($functionName)) {
                return new ErrorData([
                    'code'    => 400,
                    'message' => 'Function '. $functionName.' does not exist in class '. $class,
                ]);
            }

            $function = $reflectionClass->getMethod($functionName);

            // Get the doc comment, param descriptions and return description info.
            $docBlockData = $this->extractDescription($function);

            // Get parameters of the function as an array of Parameter DTO object.
            $functionParameters = $this->getFunctionParameters($function, $receivedFunctionData, $docBlockData);

            // Get visibility of the function.
            $visibility = $this->getFunctionVisibility($function, $receivedFunctionData);

            // Get return data from received function data
            $returnData = $this->getReturnData($function, $receivedFunctionData, $docBlockData);

            // Get the description of the function
            $functionDescription = $this->getFunctionDescription($receivedFunctionData, $docBlockData);

            // Set function signature values, descriptions to FunctionData DTO object.
            $functionData = new FunctionData([
                'function_name' => $functionName,
                'visibility'    => $visibility,
                'description'   => $functionDescription,
                'parameters'    => $functionParameters,
                'return'        => $returnData,
                'class_name'    => (string) $class
            ]);

            $functionList[] = $this->array_filter_recursive($functionData->toArray());
        }

        return $this->writeToYmlFile($fileName, $functionList);
    }

    /**
     * Get the function name.
     *
     * @param string|FunctionData $receivedFunctionData
     *
     * @return string
     */
    private function getFunctionName(string|FunctionData $receivedFunctionData): string
    {
        return $receivedFunctionData->function_name ?? $receivedFunctionData;
    }

    /**
     * Get the function description.
     *
     * @param string|FunctionData $receivedFunctionData
     * @param DocBlockData $docBlockData
     *
     * @return string|null
     */
    private function getFunctionDescription(string|FunctionData $receivedFunctionData, DocBlockData $docBlockData): ?string
    {
        return $receivedFunctionData->description ?? $docBlockData->description; // Received function description has higher priority than predefined description
    }

    /**
     * Get the return data.
     *
     * @param ReflectionMethod $function
     * @param string|FunctionData $receivedFunctionData
     * @param DocBlockData $docBlockData
     *
     * @return ReturnData
     * @throws UnknownProperties
     */
    private function getReturnData(ReflectionMethod $function, string|FunctionData $receivedFunctionData, DocBlockData $docBlockData): ReturnData
    {
        $receivedReturnData = $receivedFunctionData->return ?? null;
        // Set return definitions and descriptions of the function.
        return new ReturnData([
            'type'        => $receivedReturnData->type ?? $function->getReturnType()?->getName(), // Received return type has higher priority than predefined return type
            'description' => $receivedReturnData->description ?? $docBlockData->return_description, // Received return value description has higher priority than predefined description
            'example'     => $receivedReturnData->example ?? null, // Received return value example or null is set since predefined function mostly does not have example return value.
        ]);
    }

    /**
     * Get visibility of the function.
     *
     * @param ReflectionMethod $function
     * @param string|FunctionData $receivedFunctionData
     *
     * @return string
     */
    private function getFunctionVisibility(ReflectionMethod $function, string|FunctionData $receivedFunctionData): string
    {
        return $receivedFunctionData->visibility
            ?? match ($function->getModifiers()) {
                ReflectionMethod::IS_STATIC    => VisibilityType::STATIC,
                ReflectionMethod::IS_PROTECTED => VisibilityType::PROTECTED,
                ReflectionMethod::IS_PRIVATE   => VisibilityType::PRIVATE,
                ReflectionMethod::IS_ABSTRACT  => VisibilityType::ABSTRACT,
                ReflectionMethod::IS_FINAL     => VisibilityType::FINAL,
                default                        => VisibilityType::PUBLIC,
            };
    }

    /**
     * Get the parameters of the function.
     *
     * @param ReflectionMethod $function
     * @param string|FunctionData $receivedFunctionData
     * @param DocBlockData $docBlockData
     *
     * @return array
     * @throws UnknownProperties
     */
    private function getFunctionParameters(ReflectionMethod $function, string|FunctionData $receivedFunctionData, DocBlockData $docBlockData): array
    {
        // Initialize function parameters.
        $functionParameters = [];

        // Get the function parameters
        $parameters = $function->getParameters();

        foreach ($parameters as $parameter) {
            // here first get the same param if exists in received param data, and then below just do ?? for setting them
            $paramName = $parameter->getName();
            $receivedFunctionParam = (function () use ($receivedFunctionData, $paramName) {
                $params = $receivedFunctionData?->parameters ?? [];
                $result = null;
                // Loop through received parameters and check for the same name parameter if provided.
                foreach ($params as $param) {
                    if ($param->name === $paramName) {
                        $result = $param;
                        break;
                    }
                }

                return $result;
            })();

            /*
             * Retrieve parameter type, required info, description, example and default value. They get them form received parameters and overwrites parameter predefined values.
             * Basically provided parameter definitions and descriptions have higher priority than parameter predefined values.
             */
            $paramType = $receivedFunctionParam->type ?? ($parameter->getType() ? $parameter->getType()->getName() : 'mixed');
            $paramRequired = $receivedFunctionParam->required ?? (! $parameter->isOptional());
            $paramDescription = $receivedFunctionParam->description ?? Arr::get($docBlockData->param_descriptions, $parameter->getName());
            $paramExample = $receivedFunctionParam->example ?? null; // docblock does not carry the example info for the params so here we receive it from provided function if exists.
            $paramDefault = $receivedFunctionParam->default ?? ($parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null);

            // Set retrieved parameter signature values, descriptions to ParameterData DTO object.
            $functionParameters[] = new ParameterData([
                'name'        => $paramName,
                'type'        => $paramType,
                'required'    => $paramRequired,
                'description' => $paramDescription,
                'example'     => $paramExample,
                'default'     => $paramDefault
            ]);
        }

        return $functionParameters;
    }

    /**
     * It extracts descriptions from the docblock and add them into DocBlockData DTO
     * including description of function (description) @ param tag (param_descriptions) and @ return tag (return_description)
     *
     * @param ReflectionMethod $function
     *
     * @return DocBlockData
     */
    private function extractDescription(ReflectionMethod $function): DocBlockData
    {
        // Initialize variables and DocBlockData DTO
        $combinedDescription = '';
        $paramDescriptions = [];
        $returnDescriptions = [];
        $docBlockData = new DocBlockData();

        // Get the doc comment
        $docComment = $function->getDocComment();

        // Skip inside of if statement in case docblock is not existed.
        if (false !== $docComment) {
            // Initiate DocBlock Factory
            $docBlockFactory = DocBlockFactory::createInstance();
            $docBlock = $docBlockFactory->create($docComment);

            // Retrieve summary and description, and combine them into a single description string.
            $summary = $docBlock->getSummary();
            $description = (string) $docBlock->getDescription();
            $combinedDescription = $summary . " " . $description; // Combine summary and description

            // Retrieve param tag descriptions
            $parametersDescriptions = $docBlock->getTagsByName('param');
            // Add param descriptions as associated array as parameter name and description
            foreach ($parametersDescriptions as $param) {
                $paramDescriptions[$param->getVariableName()] = $param->getDescription()?->render();
            }

            // Retrieve return tag descriptions
            $returnTags = $docBlock->getTagsByName('return');
            // Add return tag descriptions to array
            foreach ($returnTags as $returnTag) {
                $returnDescriptions[] = $returnTag->getDescription()?->render();
            }
        }

        // Map retrieved descriptions into DocBlockData DTO.
        $docBlockData->description = $combinedDescription;
        $docBlockData->param_descriptions = $paramDescriptions;
        $docBlockData->return_description = implode(". ", $returnDescriptions);

        return $docBlockData;
    }

    /**
     * Write function list to a YAML file.
     *
     * @param string $fileName
     * @param array $functionList
     *
     * @return bool
     */
    private function writeToYmlFile(string $fileName, array $functionList): bool
    {
        // convert function list into yml format
        $yml = Yaml::dump($functionList);

        // Check if file exist, create it if not.
        if (! file_exists($fileName)) {
            touch($fileName);
        }

        // Write function list to a YAML file (append mode).
        return false !== file_put_contents($fileName, $yml, FILE_APPEND);
    }

    /**
     * Recursively filter empty and null values from the array.
     *
     * @param mixed $input
     *
     * @return array
     */
    private function array_filter_recursive(mixed $input): array
    {
        if (! is_array($input)) {
            return $input;
        }

        foreach ($input as &$value) {
            if (is_array($value)) {
                $value = self::array_filter_recursive($value);
            }
        }

        return array_filter($input, fn($value) => ! is_null($value) && $value !== '');
    }
}