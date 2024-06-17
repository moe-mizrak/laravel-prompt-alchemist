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
        'content_payload_instructions'  => 'You are an AI assistant that strictly follows instructions and provides responses in a specific format.
                Your task is to analyze a given prompt and identify the required functions from a provided list to answer the prompt.
                Your response should be a JSON array that lists the required function names, their parameters (name and type only), and the class_name, following the exact format specified in the "function_payload_schema".
                Do not include any additional information, explanations, or values beyond what is specified in the schema. Adhere to the following instructions:
                1. Read the provided "prompt" and the list of available "functions".
                2. Identify which function(s) from the "functions" list are needed to answer the prompt.
                3. Your response should ONLY contain a JSON array following the exact format specified in the "function_payload_schema". Do not include any additional fields, values, text, or explanations.
                4. The JSON array should list the required function names, their parameters (name and type only), and the class_name.
                5. Do not add any other information beyond the JSON array matching the "function_payload_schema" format.
                6. Ensure that the response can be directly used in PHP code without any modifications.
                7. Do not provide any actual values for the parameters. Only include the parameter names and types as specified in the "function_payload_schema".',
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
        'class_name' => new MappingData([
            'path' => 'class_name',
            'type' => 'string',
        ])
    ]),
];