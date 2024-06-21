
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

➡️ `functions_yml_path` Add path of the functions yml file which is the callable function list of your project. The [Usage](#-usage) section provides a deep dive into functions yml file; how to define functions, format needs to be used etc. (**e.g.** &#95;&#95;DIR&#95;&#95; . '/../resources/functions.yml'). 

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

---
## 💫 Contributing
> **We welcome contributions!** If you'd like to improve this package, simply create a pull request with your changes. Your efforts help enhance its functionality and documentation.

---
## 📜 License
Laravel Prompt Alchemist is an open-sourced software licensed under the **[MIT license](LICENSE)**.