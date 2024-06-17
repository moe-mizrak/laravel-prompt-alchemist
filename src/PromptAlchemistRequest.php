<?php

namespace MoeMizrak\LaravelPromptAlchemist;

use Illuminate\Support\Arr;
use MoeMizrak\LaravelOpenrouter\DTO\ChatData;
use MoeMizrak\LaravelOpenrouter\DTO\MessageData;
use MoeMizrak\LaravelOpenrouter\DTO\ResponseData;
use MoeMizrak\LaravelOpenrouter\Facades\LaravelOpenRouter;
use MoeMizrak\LaravelOpenrouter\Types\RoleType;
use MoeMizrak\LaravelPromptAlchemist\DTO\DocBlockData;
use MoeMizrak\LaravelPromptAlchemist\DTO\ErrorData;
use MoeMizrak\LaravelPromptAlchemist\DTO\FunctionData;
use MoeMizrak\LaravelPromptAlchemist\DTO\FunctionSignatureMappingData;
use MoeMizrak\LaravelPromptAlchemist\DTO\ParameterData;
use MoeMizrak\LaravelPromptAlchemist\DTO\ReturnData;
use MoeMizrak\LaravelPromptAlchemist\Resources\Templates\ContentPayloadTemplate;
use MoeMizrak\LaravelPromptAlchemist\Resources\Templates\ResponsePayloadTemplate;
use MoeMizrak\LaravelPromptAlchemist\Types\VisibilityType;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;
use ReflectionMethod;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Symfony\Component\Yaml\Yaml;

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
     * Makes open router chat request and retrieve the response
     *
     * @param ChatData $chatData
     *
     * @return ResponseData
     */
    protected function openRouterChatRequest(ChatData $chatData): ResponseData
    {
        return LaravelOpenRouter::chatRequest($chatData);
    }

    /**
     * Retrieves the function list from the configuration file and combines it with the provided prompt.
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
        $functions = Yaml::parseFile(config('laravel-prompt-alchemist.functions_yml_path'));
        $functionPayloadSchema = Yaml::parseFile(config('laravel-prompt-alchemist.schemas.function_payload_schema_path'));
        $instructions = config('laravel-prompt-alchemist.instructions.content_payload_instructions');

        return ContentPayloadTemplate::createPayload($prompt, $instructions, $functions, $functionPayloadSchema);
    }

    /**
     * Prepares a payload for the function results based on the given prompt and function results.
     * This method ensures the results adhere to the defined schema, ready for processing by the LLM provider.
     *
     * @param string $prompt
     * @param array $functionResults
     *
     * @return array
     */
    public function prepareFunctionResultsPayload(string $prompt, array $functionResults): array
    {
        $resultsSchema = Yaml::parseFile(config('laravel-prompt-alchemist.schemas.function_results_schema_path'));
        $instructions = config('laravel-prompt-alchemist.instructions.response_payload_instructions');

        return ResponsePayloadTemplate::createPayload($prompt, $instructions, $functionResults, $resultsSchema);
    }

    /**
     * This function will be implemented later.
     *
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
        $callableFunctionData = $this->formCallableFunctionData($signatureMappingData, $llmReturnedFunctionData);

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
        // LLm returned class name of the function
        $llmReturnedClassName= Arr::get($llmReturnedFunction, $signatureMappingData->class_name->path);

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
            'class_name'    => $llmReturnedClassName
        ]);
    }

    /**
     * Forms callable function data based on the signature mapping.
     *
     * @param FunctionSignatureMappingData $signatureMappingData - This is for the signature mapping (path and type info of fields in array)
     * @param FunctionData $llmReturnedFunctionData - This is for retrieving the correct function from function list by comparing with $llmReturnedFunctionName and $llmReturnedClassName.
     *
     * @return FunctionData
     * @throws UnknownProperties
     */
    private function formCallableFunctionData(FunctionSignatureMappingData $signatureMappingData, FunctionData $llmReturnedFunctionData): FunctionData
    {
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
     * Generates instructions that can be used in config content_payload_instructions.
     *
     * @return array|\ArrayAccess|mixed
     * @throws UnknownProperties
     * @throws \MoeMizrak\LaravelOpenrouter\Exceptions\XorValidationException
     */
    public function generateInstructions(): mixed
    {
        // Get functions, functionPayloadSchema and prompt for instructions generation from the config.
        $functions = Yaml::parseFile(config('laravel-prompt-alchemist.functions_yml_path'));
        $functionPayloadSchema = Yaml::parseFile(config('laravel-prompt-alchemist.schemas.function_payload_schema_path'));
        $prompt = config('laravel-prompt-alchemist.instructions.generate_content_payload_instructions_prompt');

        $content = [
            'prompt'                  => $prompt,
            'functions'               => $functions,
            'function_payload_schema' => $functionPayloadSchema,
        ];

        /*
         * Prepare MessageData DTO for OpenRouter request
         */
        $messageData = new MessageData([
            'content' => json_encode($content),
            'role'    => RoleType::USER,
        ]);

        /*
         * Prepare ChatData DTO for OpenRouter request
         */
        $chatData = new ChatData([
            'messages' => [
                $messageData,
            ],
            'model'   => config('laravel-prompt-alchemist.env_variables.default_model'),
        ]);

        // Make OpenRouter request with prepared ChatData
        $openRouterResponse = $this->openRouterChatRequest($chatData);

        // Return the instructions created.
        return Arr::get($openRouterResponse->choices[0], 'message.content');
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