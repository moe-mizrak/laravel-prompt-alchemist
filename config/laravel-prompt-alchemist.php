<?php

use MoeMizrak\LaravelPromptAlchemist\DTO\FunctionSignatureMappingData;
use MoeMizrak\LaravelPromptAlchemist\DTO\MappingData;

return [

    /*
    |--------------------------------------------------------------------------
    | LLM Environment Variables
    |--------------------------------------------------------------------------
    | This section allows you to define environment variables related to LLM functionality.
    | These variables are essential for authenticating requests to the LLM service provider
    | and configuring your application's interaction with LLM services.
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
        'api_key'      => env('OPENROUTER_API_KEY'),

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
    | Callable Function List Path
    |--------------------------------------------------------------------------
    | This path contains a list of functions that can be called in your
    | application. Each function is represented by an array with its details.
    | You can describe all functions here directly, or you can create a yml file to retrieve them from a separate file.
    | Please check README for alternative usage.
    |
    */
    'functions_yml_path' => __DIR__ . '/../resources/functions.yml',

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
         * Path of the schema that defines the structure of the function payload
         */
        'function_payload_schema_path' => __DIR__ . '/../resources/schemas/function_payload_schema.yml',

        /**
         * Path of the schema that defines the structure of the function results.
         */
        'function_results_schema_path' => __DIR__ . '/../resources/schemas/function_results_schema.yml',
    ],

    'instructions' => [
        'content_payload_instructions'  => 'This is a tool use (function calling) request. You will strictly follow the instructions:
                - Understand the provided prompt, and decide which function or functions (from provided functions list) is needed to respond to prompt
                - Respond ONLY with the list of function names and their parameters following the exact format provided in the function_payload_schema. Do not include any other text or explanations.
                - The response should be a JSON array with the required function calls, their parameter names and types. Follow the function_payload_schema formatting precisely.
                - Do not add any explanation, sentences or other information beyond the JSON array response following the function_payload_schema format.
                - Consider that the given response will be used in PHP code.',
        'response_payload_instructions' => 'You will strictly follow the instructions:
                - Understand the provided prompt and answer the prompt using the function_results (needed info is provided in function_results). If function_results are not sufficient enough, then your answer will be "Please provide more information about [missing information]"
                - Respond based on the function_results_schema sample provided (Do not add any extra info, exactly the same format provided in function_results_schema).
                - Format the response as an array following the structure in function_results_schema, without adding any explanatory sentences or context. 
                - Do not include any additional text or sentences other than the exact format as provided in function_results_schema.
                - Consider that the given response will be used in PHP code',
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
        'function_visibility' => new MappingData([
            'path' => 'visibility',
            'type' => 'string',
        ]),
        'function_return_type' => new MappingData([
            'path' => 'return.type',
            'type' => 'string',
        ]),
        'function_return_description' => new MappingData([
            'path' => 'return.description',
            'type' => 'string',
        ]),
        'function_return_example' => new MappingData([
            'path' => 'return.example',
            'type' => 'mixed',
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
        'parameter_default' => new MappingData([
            'path' => 'parameters[].default',
            'type' => 'mixed',
        ]),
    ]),
];