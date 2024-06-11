<?php

use MoeMizrak\LaravelPromptAlchemist\DTO\FunctionSignatureMappingData;
use MoeMizrak\LaravelPromptAlchemist\DTO\MappingData;
use Symfony\Component\Yaml\Yaml;

return [

    /*
    |--------------------------------------------------------------------------
    | AI Environment Variables
    |--------------------------------------------------------------------------
    | This section allows you to define environment variables related to AI functionality.
    | These variables are essential for authenticating requests to the AI service provider
    | and configuring your application's interaction with AI services.
    |
    */
    'env_variables' => [

        /*
        |--------------------------------------------------------------------------
        | OpenRouter API Key
        |--------------------------------------------------------------------------
        | Here you may specify the API key for accessing the OpenRouter API.
        | This key is required to authenticate your requests to the OpenRouter service.
        | You can obtain your API key from the OpenRouter dashboard.
        |
        */
        'api_key'      => env('OPENROUTER_API_KEY', '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'),

        /*
        |--------------------------------------------------------------------------
        | OpenRouter API Endpoint
        |--------------------------------------------------------------------------
        | Here you may specify the endpoint URL for the OpenRouter API.
        | This is the URL where your application will send requests to interact with OpenRouter.
        | You can find the API endpoint URL in the OpenRouter documentation.
        | Default value is https://openrouter.ai/api/v1/ , which is the base URL for all requests.
        */
        'api_endpoint' => env('OPENROUTER_API_ENDPOINT', 'https://openrouter.ai/api/v1/'),

    ],

    /*
    |--------------------------------------------------------------------------
    | Callable Function List
    |--------------------------------------------------------------------------
    | This array contains a list of functions that can be called in your
    | application. Each function is represented by an array with its details.
    | You can describe all functions here directly, or you can create a yml file to retrieve them from a separate file.
    | Please check README for alternative usage.
    |
    */
    'functions' => Yaml::parseFile(__DIR__ . '/../resources/functions.yml'),

    /*
    |--------------------------------------------------------------------------
    | Schema Definitions
    |--------------------------------------------------------------------------
    | This section defines the desired schema samples that are used for
    | formatting the function payloads and results.
    |
    */
    'schemas' => [
        /*
         * The schema that defines the structure of the function payload
         */
        'function_payload_schema' => Yaml::parseFile(__DIR__ . '/../resources/schemas/function_payload_schema.yml'),

        /**
         * The schema that defines the structure of the function results.
         */
        'function_results_schema' => Yaml::parseFile(__DIR__ . '/../resources/schemas/function_results_schema.yml'),
    ],


    /**
     * This part can be set per project dynamically based on the functions.yml or any type of function declaration.
     * It should follow the same formatting, meaning how FunctionSignatureMappingData fields will be set should consider in which layer is this filed is located in function declaration yml.
     * e.g. if parameter name gos like this in functions.yml finance->data->arguments->name, then 'parameter_name' => 'finance.data.arguments.name' should be set.
     * Missing mappings will be skipped, if not used in function.yml
     * e.g. 'function_visibility' is skipped since in functions.yml is not defined.
     */
    'function_signature_mapping' => new FunctionSignatureMappingData([
        'function_name' => new MappingData([
            'path' => 'function_name',
            'type' => 'string',
        ]),
        'function_description' => new MappingData([
            'path' => 'description',
            'type' => 'string',
        ]),
        'function_return_value' => new MappingData([
            'path' => 'return',
            'type' => 'object',
        ]),
        'parameters' => new MappingData([
            'path' => 'parameters', // could be arguments, parameter_definitions, input_schema.properties, parameters.properties
            'type' => 'array',
        ]),
        'parameter_name' => new MappingData([
            'path' => 'parameters[].name',
            'type' => 'string',
        ]),
        'parameter_type' => new MappingData([
            'path' => 'parameters[].type',
            'type' => 'string',
        ]),
        'parameter_required_info' => new MappingData([
            'path' => 'parameters[].required',
            'type' => 'boolean',
        ]),
        'parameter_description' => new MappingData([
            'path' => 'parameters[].description',
            'type' => 'string',
        ]),
        'parameter_example' => new MappingData([
            'path' => 'parameters[].example',
            'type' => 'mixed',
        ]),
    ]),
];