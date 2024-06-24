
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

➡️ `schemas` Add path of the schemas needed for function payload and function results payload.
- `function_payload_schema_path`: Path of the function payload schema. The [Usage](#-usage) section provides a deep dive into function payload schema. (**e.g.** &#95;&#95;DIR&#95;&#95; . '/../resources/schemas/function_payload_schema.yml')
- `function_results_schema_path`: Path of the function results schema. The [Usage](#-usage) section provides a deep dive into function results schema. (**e.g.** &#95;&#95;DIR&#95;&#95; . '/../resources/schemas/function_results_schema.yml')

➡️ `instructions`: Add instructions for prompt_function_instructions, function_results_instructions and generate_prompt_function_instructions.
- `prompt_function_instructions`: Instructions for the LLM used for prompt function payload. The [Usage](#-usage) section provides a deep dive into prompt function instructions. (**e.g.** You are an AI assistant that strictly follows instructions and provides response ...)
- `function_results_instructions`: Instructions for the LLM used for function results payload. The [Usage](#-usage) section provides a deep dive into function results instructions. (**e.g.** You will strictly follow the instructions as ...)
- `generate_prompt_function_instructions`: Instructions for generating prompt_function_instructions by using **generateInstructions** function. The [Usage](#-usage) section provides a deep dive into generating prompt function instructions. (**e.g.** Your role is to analyze the provided "functions" and ...)

➡️ `function_signature_mapping`: Add mapping for function signature namings. The [Usage](#-usage) section provides a deep dive into function signature mapping. (**e.g.** ``` new FunctionSignatureMappingData(['parameters' => new MappingData(['path' => 'input_schema.properties', 'type' => 'array']), ...])``` )

---
## 🎨 Usage
This package provides two ways to interact with the Laravel Prompt Alchemist package:
- Using the `LaravelPromptAlchemist` facade.
- Instantiating the `PromptAlchemistRequest` class directly.

### Using Facade
The `LaravelPromptAlchemist` facade offers a convenient way to make Laravel Prompt Alchemist requests.

#### Generate Function List
In order to generate function list (functions.yml), you can either:

##### Manually create function signatures
Create function signatures **manually** using your chosen naming convention in a **flat associative array structure**.
<br/>

ℹ️ Ensure coherence with **function_signature_mapping**, which aligns your naming practices with the package format for integration. (**Note:** Please refer to !!HERE link function signature!! section for which function signatures should be defined in function list yml file for better performance)
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

**Recommended** naming convention (Automated way of generating function list as given in the second option below [generateFunctionList](#use-generatefunctionlist-method) also generates function list in this format):
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
**Alternative** way for creating the `functions` array; when function signatures and docblock are well-defined, simply adding function names suffices to create a comprehensive function list:
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
Sample of well-defined function signature and docblock.
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
Sample of poorly-defined function signature and docblock.
(no docblock, no type-hint for params, no return type declaration):
```php
function noExtraInfoProvidedFunction($stringParam, $intParam)
{
    return 'missing parameter and docblock function return value ' . $stringParam . ' ' . $intParam;
}
```
Sample of partially-defined function signature and docblock:
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
   
1. If functions are well-defined in your codebase, you can use [alternative approach](#alternative-functions-array) to send the function names in functions array with no additional info (`generateFunctionList` does everything for you).
2. If functions are poorly-defined in your codebase, then it is best to use the [main approach](#use-generatefunctionlist-method) by creating FunctionData DTO for each function and setting function related info/descriptions to create functions array.
3. If functions are partially-defined in your codebase, then similarly using the [main approach](#use-generatefunctionlist-method) is the best option since more info is better for LLM to know about your functions to make the best decision.
<br/>
   
**Note:** You can just add **missing/weak/poorly-defined** fields in **FunctionData** DTO and skip the descriptions/fields that are well-defined 
since `generateFunctionList` analyses function and adds all possible information about the function.
Also note that fields added to **FunctionData** DTO overwrite the existing predefined descriptions/fields in the function .
(Basically if a field is added to **FunctionData** DTO, it is taken into account; if a field is NOT added to FunctionData and exists in function declaration then this predefined description/field is added to function list).  

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