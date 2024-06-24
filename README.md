
# Laravel Prompt Alchemist
Versatile **LLM Tool Use (Function Calling)** package for Laravel, compatible with **all LLMs**.
<br />

[![Latest Version on Packagist](https://img.shields.io/badge/packagist-v1.0-blue)](https://packagist.org/packages/moe-mizrak/laravel-prompt-alchemist)
<br />

---
> **Unlock Powerful Large Language Model (LLM) Interactions in Your Laravel Application.**

This Laravel package enables versatile **LLM Tool Use (Function Calling)**, allowing LLMs to decide which function to call based on the prompt, compatible with **all LLMs** regardless of built-in capabilities.

## Table of Contents

- [🤖 Requirements](#-requirements)
- [🏁 Get Started](#-get-started)
- [⚙️ Configuration](#-configuration)
- [🎨 Usage](#-usage)
- [💫 Contributing](#-contributing)
- [📜 License](#-license)

---
## 🤖 Requirements
- **PHP 8.1** or **higher**
- [<u>Laravel Openrouter</u>](https://github.com/moe-mizrak/laravel-openrouter)
> ℹ️ **[OpenRouter](https://openrouter.ai/)** is used as the default LLM provider in the package. The package is structured **flexibly**, allowing you to use your choice of LLM provider such as OpenAI, Claude, Gemini etc. (***OpenRouter is a unified interface for LLMs.***)

---
## 🏁 Get Started
You can **install** the package via composer:
```bash
composer require moe-mizrak/laravel-prompt-alchemist
```
You can **publish** the **config file** with:
```bash
php artisan vendor:publish --tag=laravel-prompt-alchemist
```
This is the contents of the **published config file**:
```php
return [
    'env_variables' => [
        'api_key'       => env('OPENROUTER_API_KEY'),
        'api_endpoint'  => env('OPENROUTER_API_ENDPOINT', 'https://openrouter.ai/api/v1/'),
        'default_model' => 'Your selection of the default model',
    ],

    'functions_yml_path' => 'Path to the functions yml file',

    'schemas' => [
        'function_payload_schema_path' => 'Path to the function payload schema yml file',
        'function_results_schema_path' => 'Path to the function results schema yml file',
    ],

    'instructions' => [
        'prompt_function_instructions'  => 'Instructions for the LLM about how to make use of provided functions, prompt and function_payload_schema in order to get the desired response',
        'function_results_instructions' => 'Instructions for the LLM about how to make use of provided function_results, prompt and function_results_schema in order to get the desired response',
        'generate_prompt_function_instructions' => 'Instructions for generating specific/customised prompt_function_instructions',
    ],

    'function_signature_mapping' => 'Function signature mapping data (FunctionSignatureMappingData)',
];
```

---
## ⚙️ Configuration
After publishing the package configuration file, steps you should be following:

➡️ `env_variables` Add following environment variables to your **.env** file in case you will be using **OpenRouter** as LLM provider (If your choice of LLM provider is different, then this is <u>**not** required</u>):
```env
OPENROUTER_API_ENDPOINT=https://openrouter.ai/api/v1/
OPENROUTER_API_KEY=your_api_key
OPENROUTER_DEFAULT_MODEL=default_model
```
- <small>OPENROUTER_API_ENDPOINT: The endpoint URL for the **OpenRouter API** (**default**: https://openrouter.ai/api/v1/).</small>
- <small>OPENROUTER_API_KEY: Your **API key** for accessing the OpenRouter API. You can obtain this key from the [<u>OpenRouter Dashboard</u>](https://openrouter.ai/keys). (**e.g.** sk-or-v1... )</small>
- <small>OPENROUTER_DEFAULT_MODEL: Default model that will be used in the package - necessary for generateInstructions functionality (In codebase, you can still specify any model that you want for OpenRouter requests). You can check model list from [<u>OpenRouter Models</u>](https://openrouter.ai/docs/models). (**e.g.** 'mistralai/mistral-7b-instruct:free')</small>

➡️ `functions_yml_path` Add path of the functions yml file which is the callable function list of your project. The [Generate Function List](#generate-function-list) section provides a deep dive into functions yml file; how to define functions, format needs to be used etc. (**e.g.** &#95;&#95;DIR&#95;&#95; . '/../resources/functions.yml'). 

➡️ `function_signature_mapping`: Add mapping for function signature namings. The [Define Function Signature Mapping](#define-function-signature-mapping) section provides a deep dive into function signature mapping. (**e.g.** ``` new FunctionSignatureMappingData(['parameters' => new MappingData(['path' => 'input_schema.properties', 'type' => 'array']), ...])``` )

➡️ `schemas` Add path of the schemas needed for function payload and function results payload.
- `function_payload_schema_path`: Path of the function payload schema. The [Function Payload Schema](#function-payload-schema) section provides a deep dive into function payload schema. (**e.g.** &#95;&#95;DIR&#95;&#95; . '/../resources/schemas/function_payload_schema.yml')
- `function_results_schema_path`: Path of the function results schema. The [Function Results Schema](#function-results-schema) section provides a deep dive into function results schema. (**e.g.** &#95;&#95;DIR&#95;&#95; . '/../resources/schemas/function_results_schema.yml')

➡️ `instructions`: Add instructions for prompt_function_instructions, function_results_instructions and generate_prompt_function_instructions.
- `prompt_function_instructions`: Instructions for the LLM used for prompt function payload. The [Generate Prompt Function Instructions](#generate-prompt-function-instructions) section provides a deep dive into prompt function instructions. (**e.g.** You are an AI assistant that strictly follows instructions and provides response ...)
- `function_results_instructions`: Instructions for the LLM used for function results payload. The [Prepare Function Results Payload](#prepare-function-results-payload) section provides a deep dive into function results instructions. (**e.g.** You will strictly follow the instructions as ...)
- `generate_prompt_function_instructions`: Instructions for generating prompt_function_instructions by using **generateInstructions** function. The [Generate Prompt Function Instructions](#generate-prompt-function-instructions) section provides a deep dive into generating prompt function instructions. (**e.g.** Your role is to analyze the provided "functions" and ...)

---
## 🎨 Usage
This package provides two ways to interact with the Laravel Prompt Alchemist package:
- Using the `LaravelPromptAlchemist` facade (i.e. [Using Facade](#using-facade)).
- Instantiating the `PromptAlchemistRequest` class directly (i.e. [Using PromptAlchemistRequest Class](#using-promptalchemistrequest-class)).

### Using Facade
The `LaravelPromptAlchemist` facade offers a convenient way to make Laravel Prompt Alchemist requests. Following sections will lead you through further configuration and usage of `LaravelPromptAlchemist` facade.

#### Generate Function List
In order to generate function list (functions.yml), you can either:

##### Manually create function list
Create function signatures **manually** using your chosen naming convention in a **flat associative array structure**.
<br/>

ℹ️ Ensure coherence with **function_signature_mapping**, which aligns your naming practices with the package format for integration.
(**Note:** Please refer to [Define Function Signature Mapping](#define-function-signature-mapping) section for which function signatures should be defined in function list yml file for better performance)
- Create yml file for function list with any preferred naming and directory.
<br/>

**e.g.** You can create a file named `functions.yml` in the resources folder under your project
```env 
__DIR__ . '/../resources/functions.yml'
```
- Define functions in yml file that you will be using for the package (for Tool Use - Function Calling).
<br/>

**e.g.** You can create functions as samples below in a **flat associative array structure** or your choice of function naming convention.
<br/>

**Recommended** naming convention ([generateFunctionList](#use-generatefunctionlist-method) method for generating a function list, also outputs the function list in this format):
<a id="recommended-naming-convention"></a>
```
-
function_name: getFinancialData
parameters: [{ name: userId, type: int, required: true, description: 'The unique identifier for the user.', example: 12345 }, { name: startDate, type: string, required: true, description: 'The starting date for the timeframe (inclusive).', example: '2023-01-01' }, { name: endDate, type: string, required: true, description: 'The ending date for the timeframe (inclusive).', example: '2023-01-31' }]
visibility: public
description: 'Retrieves financial data for a specific user and timeframe. '
return: { type: object, description: 'An object containing details like totalAmount, transactions (array), and other relevant financial data.' }
class_name: MoeMizrak\LaravelPromptAlchemist\Tests\Example
- 
function_name: categorizeTransactions
parameters: [{ name: transactions, type: array, required: true, description: 'An array of transactions with details like amount, date, and description.', example: [{ amount: 100, date: '2023-01-01', description: 'Groceries' }, { amount: 50, date: '2023-01-02', description: 'Entertainment' }] }]
visibility: public
description: 'Categorizes a list of transactions based on predefined rules or machine learning models. '
return: { type: array, description: 'An array of transactions with an added "category" field if successfully categorized. Each transaction may also include a "confidenceScore" field.', example: [{ amount: 100, date: '2023-01-01', description: 'Groceries', category: 'Food', confidenceScore: 0.95 }, { amount: 50, date: '2023-01-02', description: 'Entertainment', category: 'Leisure', confidenceScore: 0.8 }] }
class_name: MoeMizrak\LaravelPromptAlchemist\Tests\Example
```
Or, another naming convention in a **flat associative array structure** can be as:
<a id="another-naming-convention"></a>
```
- function: getFinancialData
  input_schema:
    type: object
    properties:
      - name: userId
        type: int
        required: true
        description: 'The unique identifier for the user.'
      - name: startDate
        type: string
        required: true
        description: 'The starting date for the timeframe (inclusive).'
      - name: endDate
        type: string
        required: true
        description: 'The ending date for the timeframe (inclusive).'
  description: 'Retrieves financial data for a specific user and timeframe.'
  return:
    type: object
    description: 'An object containing details like totalAmount, transactions (array), and other relevant financial data.'
  class: MoeMizrak\LaravelPromptAlchemist\Tests\Example
- function: categorizeTransactions
  input_schema:
    properties:
      - name: transactions
        type: array
        required: true
        description: 'An array of transactions with details like amount, date, and description.'
  description: 'Categorizes a list of transactions based on predefined rules or machine learning models.'
  return:
    type: array
    description: 'An array of transactions with an added "category" field if successfully categorized. Each transaction may also include a "confidenceScore" field.'
  class: MoeMizrak\LaravelPromptAlchemist\Tests\Example
```

##### Use generateFunctionList method
Use `generateFunctionList` function to generate a detailed function list from a given class and write it to a file in **yml** format.
<br/>
(For each different class name, you need to run the `generateFunctionList` to generate function list - functions.yml, list gets appended whenever the `generateFunctionList` is called)
<br/><br/>
ℹ️ This is a good practice to automate the generation of function list.
The fields that are defined in the function description (functionData) **overwrites** the function predefined fields if exists.
If a function signature field is missing then it is added to function list definitions.
```php
$class = Example::class; // Or you can give fully qualified class name string as $class = 'MoeMizrak\LaravelPromptAlchemist\Tests\Example'

// Function descriptions that will be used to generate function list (functions.yml) 
$functionDataA = new FunctionData([
    'function_name' => 'getFinancialData',
    'parameters' => [
        new ParameterData([
            'name' => 'userId',
            'type' => 'int',
            'required' => true,
            'description' => 'The unique identifier for the user.',
            'example' => 12345
        ]),
        new ParameterData([
            'name' => 'startDate',
            'type' => 'string',
            'required' => true,
            'description' => 'The starting date for the timeframe (inclusive).',
            'example' => '2023-01-01'
        ]),
        new ParameterData([
           'name' => 'endDate',
           'type' => 'string',
           'required' => true,
           'description' => 'The ending date for the timeframe (inclusive).',
           'example' => '2023-01-31'
        ]),
    ],
    'visibility' => VisibilityType::PUBLIC,
    'description' => 'Retrieves financial data for a specific user and timeframe.',
    'return' => new ReturnData([
        'type' => 'object',
        'description' => 'An object containing details like totalAmount, transactions (array), and other relevant financial data.'
    ]),
]);
   
$functionDataB = new FunctionData([
    'function_name' => 'categorizeTransactions',
    'parameters' => [
        new ParameterData([
            'name' => 'transactions',
            'type' => 'array',
            'required' => true,
            'description' => 'An array of transactions with details like amount, date, and description.',
            'example' => [
                ['amount' => 100, 'date' => '2023-01-01', 'description' => 'Groceries'],
                ['amount' => 50, 'date' => '2023-01-02', 'description' => 'Entertainment']
            ]
        ]),
    ],
    'visibility' => VisibilityType::PUBLIC,
    'description' => 'Categorizes a list of transactions based on predefined rules or machine learning models.',
    'return' => new ReturnData([
        'type' => 'array',
        'description' => 'An array of transactions with an added "category" field if successfully categorized. Each transaction may also include a "confidenceScore" field.',
        'example' => [
            ['amount' => 100, 'date' => '2023-01-01', 'description' => 'Groceries', 'category' => 'Food', 'confidenceScore' => 0.95],
            ['amount' => 50, 'date' => '2023-01-02', 'description' => 'Entertainment', 'category' => 'Leisure', 'confidenceScore' => 0.8]
        ]
    ]),
]);
   
$functions = [$functionDataA, $functionDataB];

$fileName = __DIR__ . '/../resources/functions.yml'; // Path and the name of the file that function list will be generated
// Call generateFunctionList for automated function list generation in given $fileName (Creates file in this path if not existed).
LaravelPromptAlchemist::generateFunctionList($class, $functions, $fileName);
```
<a id="alternative-functions-array"></a>
**Alternative** way for creating the `functions` array; when function signatures and docblock are **well-defined**, simply adding function names suffices to create a comprehensive function list:
```php
$class = Example::class; // Or you can give fully qualified class name string as $class = 'MoeMizrak\LaravelPromptAlchemist\Tests\Example'
// Name of the functions that will be added to the list - functions that will be used for Tool Use (Function Calling)
$functions = [
    'getFinancialData',
    'categorizeTransactions',
];
$fileName = __DIR__ . '/../resources/functions.yml'; // Path and the name of the file that function list will be generated
// Call generateFunctionList for automated function list generation in given $fileName (Creates file in this path if not existed).
LaravelPromptAlchemist::generateFunctionList($class, $functions, $fileName);
```
Sample of **well-defined function** signature and docblock.
(Docblock description with enough info, type-hinted params, return type declaration and return tag description in docblock, parameter descriptions in docblock):
```php
/**
 * This public function is intended for testing purposes. It accepts a string and int parameters and returns a string.
 * It has additional parameter descriptions and detailed docblock.
 *
 * @param string $stringParam This is the string param description
 * @param int $intParam This is the int param description
 *
 * @return string This is the return value description
 */
public function detailedDocBlockFunction(string $stringParam, int $intParam = 2): string
{
    return 'detailed docblock function return value ' . $stringParam . ' ' . $intParam;
}
```
Sample of **poorly-defined function** signature and docblock.
(no docblock, no type-hint for params, no return type declaration):
```php
function noExtraInfoProvidedFunction($stringParam, $intParam)
{
    return 'missing parameter and docblock function return value ' . $stringParam . ' ' . $intParam;
}
```
Sample of **partially-defined function** signature and docblock:
```php
/**
 * This public function is intended for testing purposes.
 * It has missing parameter descriptions and missing type-hint.
 *
 * @return string This is the return value description
 */
public function functionWithSomeMissingDocBlockAndMissingTypeHint($stringParam, $intParam): string
{
    return 'missing parameter docblock function return value ' . $stringParam . ' ' . $intParam;
}
```
<br/>
Based on your best practices in your codebase, you can choose how to generate function list (functions.yml).
   
1. If functions are **well-defined** in your codebase, you can use [alternative approach](#alternative-functions-array) to send the function names in functions array with no additional info (`generateFunctionList` does everything for you).
2. If functions are **poorly-defined** in your codebase, then it is best to use the [main approach](#use-generatefunctionlist-method) by creating FunctionData DTO for each function and setting function related info/descriptions to create functions array.
3. If functions are **partially-defined** in your codebase, then similarly using the [main approach](#use-generatefunctionlist-method) is the best option since more info is better for LLM to know about your functions to make the best decision.
<br/>
   
**Note:** You can just add **missing/ambiguous/poorly-defined** fields in **FunctionData** DTO and skip the descriptions/fields that are **well-defined** 
since `generateFunctionList` analyses function and adds all possible information about the function.
Also note that fields added to **FunctionData** DTO **overwrite** the existing predefined descriptions/fields in the function .
(Basically if a field is added to **FunctionData** DTO, it is taken into account; if a field is NOT added to FunctionData and exists in function declaration then this predefined description/field is added to function list).  

#### Define Function Signature Mapping
Based on the function list (functions.yml), align your naming practices for functions with the package format for integration. 
It is better practice to set all possible fields for function signature (`FunctionSignatureMappingData`) for better performance since more info provided for LLM means better result.
<br/>

`function_signature_mapping` needs to be defined in the configuration file as following examples:
<br/>

(As shown in examples below, use `'[]'` for the arrays. In package, it is replaced with the index key as e.g. `parameters[].name` becomes `parameters.{key}.name` which is `parameters.0.name` for the first array index, `parameters.1.name` for the second etc.)
- In case your function list (functions.yml) is created with [recommended naming convention](#recommended-naming-convention):
```php
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
        'path' => 'parameters[].name', // since parameters field is array, '[]' states the index key which will be resolved in the package as 'parameters.0.name' for the first array and so on.
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
```
- If your function list (functions.yml) is created with [another naming convention](#another-naming-convention):
```php
'function_signature_mapping' => new FunctionSignatureMappingData([
    'function_name' => new MappingData([
        'path' => 'function',
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
    'input_schema_type' => new MappingData([
        'path' => 'input_schema.type',
        'type' => 'string',
    ]),
    'parameters' => new MappingData([
        'path' => 'input_schema.properties',
        'type' => 'array',
    ]),
    'parameter_name' => new MappingData([
        'path' => 'input_schema.properties[].name', // since properties field is array, '[]' states the index key which will be resolved in the package as 'properties.0.name' for the first array and so on.
        'type' => 'string',
    ]),
    'parameter_type' => new MappingData([
        'path' => 'input_schema.properties[].type',
        'type' => 'string',
    ]),
    'parameter_required_info' => new MappingData([
        'path' => 'input_schema.properties[].required',
        'type' => 'boolean',
    ]),
    'parameter_description' => new MappingData([
        'path' => 'input_schema.properties[].description',
        'type' => 'string',
    ]),
    'parameter_example' => new MappingData([
        'path' => 'input_schema.properties[].example',
        'type' => 'mixed',
    ]),
    'parameter_default' => new MappingData([
        'path' => 'input_schema.properties[].default',
        'type' => 'mixed',
    ]),
    'class_name' => new MappingData([
        'path' => 'class',
        'type' => 'string',
    ])
]),
```
Regarding these 2 examples, you can define your `function_signature_mapping` in the configuration file depending on your choice of naming convention.

#### Generate Prompt Function Instructions
This section defines the instructions which gives **strict description** of **desired format answers** and how LLM provider will process provided prompt, schemas etc.
<br/>

You can use your own created/generated instructions for `prompt_function_instructions`, or use `generateInstructions` method.
<br/>

`generateInstructions` method simply generates customized instructions regarding your **function list** (`functions.yml`), **function payload schema** (`function_payload_schema.yml`) and **prompt for generating instructions** (`generate_prompt_function_instructions` in **config**).
<br/>

(**Note:** `generate_prompt_function_instructions` is the prompt/instructions in **config** that describe how to generate specific/customized instructions with **functions** and **function_payload_schema**)
<br/>

Sample `generate_prompt_function_instructions` for `generateInstructions` call:
```
You are an AI assistant tasked with providing instructions to another AI system on how to respond to a given prompt with a specific JSON format. Your role is to analyze the provided "functions" and "function_payload_schema" and create a set of instructions that will ensure the other AI system generates a response following the specified format.
            The "functions" field contains a list of available functions, their parameters, descriptions, and return types. The "function_payload_schema" field specifies the expected format for the response, which should be a JSON array listing the required fields for each function.
            Your instructions should cover the following points:
                1. Read the provided "prompt" and the list of available "functions".
                2. Identify which function(s) from the "functions" list are needed to answer the prompt.
                3. Analyze the "function_payload_schema" to determine the required fields for each function in the response JSON array. Do not add or omit any fields from the schema.
                4. Explain that the response should ONLY contain a JSON array following the exact format specified in the "function_payload_schema". No additional fields, values, text, or explanations should be included.
                5. Specify that the JSON array should list the required fields for each function, as specified in the "function_payload_schema".
                6. Emphasize that no other information beyond the JSON array matching the "function_payload_schema" format should be added.
                7. Ensure that the response can be directly used in PHP code without any modifications.
                8. For the parameter fields of each function, analyze the "function_payload_schema" and include the fields exactly as specified in the schema, without making any assumptions about the field names or structure.
                9. Clarify that no actual values for the parameters should be provided. Only the parameter fields as specified in the "function_payload_schema" should be included.
                10. If no relevant function is found in the "functions" list to answer the prompt, the response should be the string "NULL" without any additional description or text.
                11. If the other AI system cannot understand or follow the instructions, it should return the string "NULL" without any additional description or text.
            Your response should be a clear and concise set of instructions that the other AI system can follow to generate the desired JSON response format based on the provided "functions" and "function_payload_schema".
            OK, now provide the instructions as described above for the other AI system to generate the desired JSON response format based on the provided "functions" and "function_payload_schema"
```

In order to generate `prompt_function_instructions`, you can call `generateInstructions` method:
```php
LaravelPromptAlchemist::generateInstructions();
```

Sample response of `generateInstructions` will look like:
```
You are an AI assistant that strictly follows instructions and provides responses in a specific format.
            Your task is to analyze a given prompt and identify the required functions from a provided list to answer the prompt.
            Your response should be a JSON array that lists the required function names, their parameters (name and type only), and the class_name, following the exact format specified in the "function_payload_schema".
                Do not include any additional information, explanations, or values beyond what is specified in the schema. Adhere to the following instructions:
                1. Read the provided "prompt" and the list of available "functions".
                2. Identify which function(s) from the "functions" list are needed to answer the prompt.
                3. Your response should ONLY contain a JSON array following the exact format specified in the "function_payload_schema". Do not include any additional fields, values, text, or explanations.
                4. The JSON array should list the required function names, their parameters (name and type only), and the class_name.
                5. Do not add any other information beyond the JSON array matching the "function_payload_schema" format.
                6. Ensure that the response can be directly used in PHP code without any modifications.
                7. Do not provide any actual values for the parameters. Only include the parameter names and types as specified in the "function_payload_schema".
                8. If you do not understand the instructions or cannot provide a response following the specified format, respond with "NULL".
                9. If no relevant function is found in the "functions" list to answer the prompt, the response should be the string "NULL" without any additional description or text
```

#### Define Schemas
This section defines the desired schema samples that are used for formatting the **function payload** and **function results**.
Basically schemas are for deciding the **response format of LLM**.
They are sent to LLM along with other info (prompt, functions - function results, instructions etc.) and in instructions LLM asked to give results as same format as in schema provided.
In this way, LLM response can be resolved in package so that it can be used for Tool Use (Function Calling).

##### Function Payload Schema
Schema that defines the structure of the function payload.
- Create yml file for function payload schema with any preferred naming and directory, add it to config file under `schemas`.
```php
'schemas' => [
    'function_payload_schema_path' => __DIR__ . '/../resources/schemas/function_payload_schema.yml',
]
```
- Define schema as example given below. The naming convention should align with the function list (functions.yml)
because LLM response will depend on this schema and response will be **validated** in the package by comparing the function list (functions.yml) file.
```
-
  function_name: getFinancialData
  parameters: [{ name: userId, type: int }, { name: startDate, type: string }, { name: endDate, type: string }]
  class_name: MoeMizrak\LaravelPromptAlchemist\Tests\Example
-
  function_name: getCreditScore
  parameters: [{ name: userId, type: int }]
  class_name: MoeMizrak\LaravelPromptAlchemist\Tests\ExampleD
```

##### Function Results Schema
Schema that defines the structure of the function results. This schema is for deciding the final response where function results are sent to LLM to form the response after Tool Use (Function Calling).
<br/>

This schema is not required if you will not be sending Tool Use (Function Calling) results to LLM. You might prefer using function results in your codebase to derive a response directly.
- Create yml file for function results schema with any preferred naming and directory, add it to config file under `schemas`.
```php
'schemas' => [
    'function_results_schema_path' => __DIR__ . '/../resources/schemas/function_results_schema.yml',
]
```
- Define schema as example given below.
```
-
  function_name: getFinancialData
  result: [{ name: transactions, type: array, value: [{ amount: , date: '2023-02-02', description: shoes }] }, { name: totalAmount, type: int, value: 1234 }]
-
  function_name: getCreditScore
  result: [{ name: creditScore, type: float, value: 0.5 }, { name: summary, type: string, value: reliable }]
```

#### Prepare Prompt Function Payload
This method is responsible for creating a structured payload template based on a given prompt and a list of functions (functions.yml) which later will be sent to an LLM provider.
This method constructs an array containing **instructions**, the **prompt**, a **list of functions**, and a **function payload schema**.
The instructions detail how the prompt should be processed, ensuring the response strictly adheres to the provided format.
<br/>

For the prompt which you will be used for Tool Use (Function Calling):
```php
$prompt = 'Can tell me Mr. Boolean Bob credit score?';
```
This is how you can call preparePromptFunctionPayload method by using facade:
```php
LaravelPromptAlchemist::preparePromptFunctionPayload($prompt);
```
And this is the expected prepared payload response sample:
```php
[
  "prompt" => "Can tell me Mr. Boolean Bob credit score?",
  "instructions" => "You are an AI assistant that strictly follows instructions and provides responses in a specific format.\n
    Your task is to analyze a given prompt and identify the required functions from a provided list to answer the prompt.\n
    Your response should be a JSON array that lists the required function names, their parameters (name and type only), and the class_name, following the exact format specified in the \"function_payload_schema\".\n
    Do not include any additional information, explanations, or values beyond what is specified in the schema. Adhere to the following instructions:\n
    1. Read the provided \"prompt\" and the list of available \"functions\".\n
    2. Identify which function(s) from the \"functions\" list ...",
  "functions" => [
    [
        "function_name" => "getFinancialData",
        "parameters" => [
            ["name" => "userId", "type" => "int"],
            ["name" => "startDate", "type" => "string"],
            ["name" => "endDate", "type" => "string"]
        ],
        "visibility" => "public",
        "description" => "Retrieves financial data for a specific user and timeframe.",
        "return" => [
            "type" => "object",
            "description" => "An object containing details like totalAmount, transactions (array), and other relevant financial data."
        ],
        "class_name" => "MoeMizrak\LaravelPromptAlchemist\Tests\Example"
    ],
    [
        "function_name" => "getCreditScore",
        "parameters" => [
            ["name" => "userId", "type" => "int"]
        ],
        "visibility" => "public",
        "description" => "Retrieves the current credit score for a specific user.",
        "return" => [
            "type" => "object",
            "description" => "An object containing the credit score, credit report summary, and any relevant notes."
        ],
        "class_name" => "MoeMizrak\LaravelPromptAlchemist\Tests\Example"
    ],
    ...
  ],
  "function_payload_schema" => [
    [
        "function_name" => "getFinancialData",
        "parameters" => [
            ["name" => "userId", "type" => "int"],
            ["name" => "startDate", "type" => "string"],
            ["name" => "endDate", "type" => "string"]
        ],
        "class_name" => "MoeMizrak\LaravelPromptAlchemist\Tests\ExampleA"
    ],
    [
        "function_name" => "getCreditScore",
        "parameters" => [
            ["name" => "userId", "type" => "int"]
        ],
        "class_name" => "MoeMizrak\LaravelPromptAlchemist\Tests\ExampleD"
    ],
    ...
  ]
]
```

#### Prepare Function Results Payload
This method is responsible for creating a structured payload template based on a given prompt and a list of functions (functions.yml) which later will be sent to an LLM provider.
This method constructs an array containing **instructions**, the **prompt**, a **list of functions**, and a **function payload schema**.
The instructions detail how the prompt should be processed, ensuring the response strictly adheres to the provided format.
<br/>

For the prompt which you will be used for Tool Use (Function Calling):
```php
$prompt = 'Can tell me Mr. Boolean Bob credit score?';

$functionResults = [
    [
        'function_name' => 'getFinancialData',
        'result' => [
            'totalAmount' => 122,
            'transactions' => [
                [
                    'amount'      => 12,
                    'date'        => '2023-02-02',
                    'description' => 'food',
                ],
            ]
        ],
    ],
    [
        'function_name' => 'getCreditScore',
        'result' => [
            'creditScore' => 0.8,
            'summary' => 'reliable',
        ]
    ],
    ...
];
```
This is how you can call prepareFunctionResultsPayload method by using facade:
```php
LaravelPromptAlchemist::prepareFunctionResultsPayload($prompt, $functionResults);
```
And this is the expected prepared payload response sample:
```php
[
  "prompt" => "Can tell me Mr. Boolean Bob credit score?",
  "instructions" => "You will strictly follow the instructions:\n
    - Understand the provided prompt and answer the prompt using the function_results (needed info is provided in function_results). If function_results are not sufficient enough, then your answer will be \"Please provide more information about [missing information]\"\n
    - Respond based on the function_results_schema sample provided (Do not add any extra info, exactly the same format provided in function_results_schema).\n
    - Format the response as an array following  ...",
  "function_results" => [
    [
        "function_name" => "getFinancialData",
        "result" => [
            "totalAmount" => 122,
            "transactions" => [
                [
                "amount" => 12,
                "date" => "2023-02-02",
                "description" => "food"
                ]
            ]
        ]
    ],
    [
        "function_name" => "getCreditScore",
        "result" => [
            "creditScore" => 0.8,
            "summary" => "reliable"
        ]
    ]
    ...
  ],
  "function_results_schema" => [
    [
        "function_name" => "getFinancialData",
        "result" => [
            [
                "name" => "transactions",
                "type" => "array",
                "value" => [
                    [
                        "amount" => null,
                        "date" => "2023-02-02",
                        "description" => "shoes"
                    ]
                ]
            ],
            [
                "name" => "totalAmount",
                "type" => "int",
                "value" => 1234
            ]
      ]
    ],
    [
        "function_name" => "getCreditScore",
        "result" => [
            [
                "name" => "creditScore",
                "type" => "float",
                "value" => 0.5
            ],
            [
                "name" => "summary",
                "type" => "string",
                "value" => "reliable"
            ]
        ]
    ]
    ...
  ]
]
```

#### Send Tool Use (Function Calling) Request to OpenRouter
Since this package is designed in a **flexible** way, you may use [Laravel OpenRouter]((https://github.com/moe-mizrak/laravel-openrouter)) (please check out **OpenRouter** github repository for more information)
which is used as the **default LLM provider** for this package, or you may use **any other LLM provider** with this package to send Tool Use (Function Calling) request.
<br/>
This is the sample OpenRouter request:
```php
$prompt = 'Can tell me Mr. Boolean Bob credit score?';
$model = config('laravel-prompt-alchemist.env_variables.default_model'); // Check https://openrouter.ai/docs/models for supported models
$content = LaravelPromptAlchemist::preparePromptFunctionPayload($prompt);

$messageData = new MessageData([
    'content' => json_encode($content),
    'role'    => RoleType::USER,
]);

$chatData = new ChatData([
    'messages' => [
        $messageData,
    ],
    'model'       => $model,
    'max_tokens'  => 900,
    'temperature' => 0.1, // Set temperature low to get better result. Higher values like 0.8 will make the output more random, while lower values like 0.2 will make it more focused and deterministic.
]);

// Send OpenRouter request
$response = LaravelOpenRouter::chatRequest($chatData);
```

Sample **Laravel OpenRouter** response (**ResponseData** is returned):
```php
output:

ResponseData([
    'id' => 'gen-YFd68mMgTkrfHVvkdemwYxdGSfZA',
    'model' => 'mistralai/mistral-7b-instruct:free',
    'object' => 'chat.completion',
    'created' => 1719251736,
    'choices' => [
        0 => [
            'index' => 0,
            'message' => [
                'role' => 'assistant',
                'content' => '["function_name":"getFinancialData", "parameters":[{"name":"userId","type":"int"},{"name":"startDate","type":"string"},{"name":"endDate","type":"string"}],"function_name":"categorizeTransactions", "parameters":[{"name":"transactions","type":"array"}],"function_name":"getTopCategories", "parameters":[{"name":"transactions","type":"array"}]]',
            ],
            'finish_reason' => 'stop',
        ]
    ],
    'usage' => UsageData([
        'prompt_tokens' => 1657,
        'completion_tokens' => 97,
        'total_tokens' => 1754,
    ])    
]);
```

#### Validate Function Signature
Validates a function signature returned by the LLM. It returns **boolean** or **ErrorData** for **wrong** function signature with missing/wrong field.
You can retrieve LLM returned functions from **ResponseData** returned by [Send Tool Use (Function Calling) Request to OpenRouter](#send-tool-use-function-calling-request-to-openrouter).
<br/>
Sample LLM returned functions:
```php
// $response = LaravelOpenRouter::chatRequest($chatData); as shown in  [Send Tool Use (Function Calling) Request to OpenRouter] section
$responseContentData = str_replace("\n", "", (Arr::get($response->choices[0], 'message.content'))); // Get content from the response.
$llmReturnedFunctions = json_decode($responseContentData, true); // Functions returned from LLM.

// Foreach $llmReturnedFunctions and get each function to validate:
$llmReturnedFunction = [ // Sample LLM returned function
    "function_name" => "getFinancialData",
    "parameters" => [
        [ "name" => "userId", "type" => "int"],
        [ "name" => "startDate", "type" => "string"],
        [ "name" => "endDate", "type" => "string"],
    ],
    'class_name' => 'MoeMizrak\LaravelPromptAlchemist\Tests\Example'
];
```

And this is how to **validate** a function signature returned from LLM:
```php
LaravelOpenRouter::validateFunctionSignature($llmReturnedFunction);
```

In case the LLM returned function signature is **invalid**, this is a sample **ErrorData** returned:
```php
output:

ErrorData([
    'code' => 400,
    'message' => 'Function invalidFunctionName does not exist in class MoeMizrak\LaravelPromptAlchemist\Tests\Example'
]);
```

### Using PromptAlchemistRequest Class
You can also inject the `PromptAlchemistRequest` class in the **constructor** of your class and use its methods directly.
```php
public function __construct(protected PromptAlchemistRequest $promptAlchemistRequest) {}
```
While everything else stays same with the [Using Facade](#using-facade), with `PromptAlchemistRequest` you can call methods as following:
```php
// generateFunctionList request.
$this->promptAlchemistRequest->generateFunctionList($class, $functions, $fileName);

// Validate function signature returned by the LLM request.
$this->promptAlchemistRequest->validateFunctionSignature($llmReturnedFunction);

// Generate instructions request.
$this->promptAlchemistRequest->generateInstructions();

// Prepare prompt function payload request.
$this->promptAlchemistRequest->preparePromptFunctionPayload($prompt);

// Prepare function results payload request.
$this->promptAlchemistRequest->prepareFunctionResultsPayload($prompt, $functionResults);
```

---
## 💫 Contributing
> **We welcome contributions!** If you'd like to improve this package, simply create a pull request with your changes. Your efforts help enhance its functionality and documentation.

---
## 📜 License
Laravel Prompt Alchemist is an open-sourced software licensed under the **[MIT license](LICENSE)**.