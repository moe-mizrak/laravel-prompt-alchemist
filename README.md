# laravel-prompt-alchemist
Laravel Package for Tool Use (Function Calling)

# 🚧 Under Construction 🚧

--------------------------------
**Function Definitions**

Instead of writing everything for functions in config file, alternative way is creating a separate yml file as [functions.yml](resources/functions.yml)

And adding it into config file as:
```php
    return [
        'env_variables' => [
            // here env variables for llm provider
        ],
        'functions' => Yaml::parseFile(__DIR__.'/../resources/functions.yml'),
    ];
```

Or add them directly to config file as follows:

```php
    return [
        'env_variables' => [
            // here env variables for llm provider
        ],
        'functions' => [
            'getFinancialData' => [
                'description' => 'Retrieves financial data for a user and timeframe.',
                'parameters' => [
                    ...,
                    [
                        'name' => 'user_id',
                        'type' => 'integer',
                        'description' => 'The user ID ...',
                        'required' => true,
                        'example' => 12345,
                    ],
                    [
                        'name' => 'from_date',
                        'type' => 'date',
                        'description' => 'The starting date for the timeframe (inclusive).',
                        'required' => true,
                        'default' => date('Y-m-d'), // Current date in YYYY-MM-DD format
                        'example' => '2023-01-01',
                    ]
                    ...,
                ],
                'return' => [
                    'type' => 'object',
                    'description' => 'Financial data object...',
                ],
            ],
            'anotherFunction' => [
                'description' => 'Performs another action...',
                'parameters' => [
                    ...,
                ],
                'return' => [
                    'type' => '...',
                    'description' => 'Description of the return value...',
                ],
            ],
            // ... other functions
        ],
    ];
```
