<?php

namespace MoeMizrak\LaravelPromptAlchemist\Tests;

use Illuminate\Support\Arr;
use MoeMizrak\LaravelOpenrouter\DTO\ChatData;
use MoeMizrak\LaravelOpenrouter\DTO\MessageData;
use MoeMizrak\LaravelOpenrouter\DTO\ResponseData;
use MoeMizrak\LaravelOpenrouter\Types\RoleType;
use MoeMizrak\LaravelPromptAlchemist\DTO\ErrorData;
use MoeMizrak\LaravelPromptAlchemist\DTO\FunctionData;
use MoeMizrak\LaravelPromptAlchemist\DTO\ParameterData;
use MoeMizrak\LaravelPromptAlchemist\DTO\ReturnData;
use MoeMizrak\LaravelPromptAlchemist\PromptAlchemistRequest;
use MoeMizrak\LaravelPromptAlchemist\Types\VisibilityType;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Symfony\Component\Yaml\Yaml;

class PromptAlchemistTest extends TestCase
{
    private PromptAlchemistRequest $request;
    private string|object $class;
    private string $testYmlFileName;

    /**
     * Setup the test environment.
     *
     * @return void
     * @throws UnknownProperties
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->content = 'Can you break down Mr. Boolean Bob expenses from last month by category and show me the top 3 spending categories?';
        $this->model = 'mistralai/mistral-7b-instruct:free';
        $this->maxTokens = 100;
        $this->messageData = new MessageData([
            'content' => $this->content,
            'role' => RoleType::USER,
        ]);
        $this->class = Example::class;
        $this->testYmlFileName = __DIR__ . '/../resources/test_functions.yml';

        $this->request = $this->app->make(PromptAlchemistRequest::class);
    }

    /**
     * Deletes a temporary yml file with "test" in the name.
     * Caution! Use this method carefully
     *
     * @param string $fileName The filename to check and delete
     *
     * @return void
     */
    private function deleteYmlFile(string $fileName): void
    {
        // Check if file exists and has ".yml" extension
        if (file_exists($fileName) && pathinfo($fileName, PATHINFO_EXTENSION) === 'yml') {
            // Check if filename contains "test" (case-insensitive)
            if (stripos($fileName, 'test') !== false) {
                // Delete the file
                unlink($fileName);
            }
        }
    }

    /**
     * General assertions required for testing instead of replicating the same code.
     *
     * @param $response
     * @return void
     */
    private function generalTestAssertions($response): void
    {
        $this->assertInstanceOf(ResponseData::class, $response);
        $this->assertNotNull($response->id);
        $this->assertEquals($this->model, $response->model);
        $this->assertEquals('chat.completion', $response->object);
        $this->assertNotNull($response->created);
        $this->assertNotNull($response->usage->prompt_tokens);
        $this->assertNotNull($response->usage->completion_tokens);
        $this->assertNotNull($response->usage->total_tokens);
        $this->assertNotNull($response->choices);
        $this->assertNotNull(Arr::get($response->choices[0], 'finish_reason'));
    }

    /**
     * @test
     */
    public function it_makes_a_basic_chat_completion_open_route_api()
    {
        /* SETUP */
        $chatData = new ChatData([
            'messages' => [
                $this->messageData,
            ],
            'model'      => $this->model,
            'max_tokens' => 100,
        ]);

        /* EXECUTE */
        $response = invade($this->request)->openRouterChatRequest($chatData);

        /* ASSERT */
        $this->generalTestAssertions($response);
        $this->assertEquals(RoleType::ASSISTANT, Arr::get($response->choices[0], 'message.role'));
        $this->assertNotNull(Arr::get($response->choices[0], 'message.content'));
    }

    /**
     * @test
     */
    public function it_prepares_prompt_function_payload()
    {
        /* EXECUTE */
        $response = $this->request->preparePromptFunctionPayload($this->content);

        /* ASSERT */
        $this->assertIsArray($response);
        $this->assertArrayHasKey('instructions', $response);
        $this->assertArrayHasKey('prompt', $response);
        $this->assertArrayHasKey('functions', $response);
        $this->assertArrayHasKey('function_payload_schema', $response);
        $this->assertArrayHasKey('function_payload_schema', $response);
    }

    /**
     * @test
     */
    public function it_sends_prompt_function_payload_to_open_route_and_retrieves_functions_to_be_used()
    {
        /* SETUP */
        $content = $this->request->preparePromptFunctionPayload($this->content);
        $messageData = new MessageData([
            'content' => json_encode($content),
            'role'    => RoleType::USER,
        ]);
        $chatData = new ChatData([
            'messages' => [
                $messageData,
            ],
            'model'       => $this->model,
            'max_tokens'  => 900,
            'temperature' => 0.1,
        ]);

        /* EXECUTE */
        $response = invade($this->request)->openRouterChatRequest($chatData);

        /* ASSERT */
        $responseContentData = str_replace("\n", "", (Arr::get($response->choices[0], 'message.content')));
        $responseContentArray = json_decode($responseContentData, true);
        $this->generalTestAssertions($response);
        $this->assertEquals(RoleType::ASSISTANT, Arr::get($response->choices[0], 'message.role'));
        $this->assertNotNull(Arr::get($response->choices[0], 'message.content'));
        $this->assertNotNull(Arr::get($responseContentArray[0], 'function_name'));
        $this->assertNotNull(Arr::get($responseContentArray[0], 'parameters'));
    }

    /**
     * @test
     */
    public function it_prepares_function_results_payload()
    {
        /* SETUP */
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
            [
                'function_name' => 'getAccountBalance',
                'result' => [
                    'currentBalance' => 12502,
                    'status' => 'active',
                ]
            ],
        ];

        /* EXECUTE */
        $response = $this->request->prepareFunctionResultsPayload($this->content, $functionResults);

        /* ASSERT */
        $this->assertIsArray($response);
        $this->assertArrayHasKey('instructions', $response);
        $this->assertArrayHasKey('prompt', $response);
        $this->assertArrayHasKey('function_results', $response);
        $this->assertArrayHasKey('function_results_schema', $response);
    }

    /**
     * @test
     */
    public function it_sends_function_results_to_open_route_and_retrieves_formed_answer()
    {
        /* SETUP */
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
            [
                'function_name' => 'getAccountBalance',
                'result' => [
                    'currentBalance' => 12502,
                    'status' => 'active',
                ]
            ],
        ];
        $prompt = 'Can tell me Mr. Boolean Bob credit score?';
        $content = $this->request->prepareFunctionResultsPayload($prompt, $functionResults);
        $messageData = new MessageData([
            'content' => json_encode($content),
            'role' => RoleType::USER,
        ]);
        $chatData = new ChatData([
            'messages' => [
                $messageData,
            ],
            'model'      => $this->model,
            'max_tokens' => 400,
            'temperature' => 0.1,
        ]);

        /* EXECUTE */
        $response = invade($this->request)->openRouterChatRequest($chatData);

        /* ASSERT */
        $responseContent = json_decode(Arr::get($response->choices[0], 'message.content'), true);
        $this->generalTestAssertions($response);
        $this->assertEquals(RoleType::ASSISTANT, Arr::get($response->choices[0], 'message.role'));
        $this->assertNotNull(Arr::get($response->choices[0], 'message.content'));
        $this->assertNotNull(Arr::get($responseContent[0], 'name'));
        $this->assertNotNull(Arr::get($responseContent[0], 'type'));
        $this->assertNotNull(Arr::get($responseContent[0], 'value'));
    }

    /**
     * @test
     */
    public function it_validates_function_signature()
    {
        /* SETUP */
        $llmReturnedFunction = [
            "function_name" => "getFinancialData",
            "parameters" => [
                [ "name" => "userId", "type" => "int"],
                [ "name" => "startDate", "type" => "string"],
                [ "name" => "endDate", "type" => "string"],
            ],
            'class_name' => (string) $this->class
        ];

        /* EXECUTE */
        $validationResponse = $this->request->validateFunctionSignature($llmReturnedFunction);

        /* ASSERT */
        $this->assertTrue($validationResponse);
    }

    /**
     * @test
     */
    public function it_responds_with_error_data_when_class_name_is_invalid()
    {
        /* SETUP */
        $className = 'InvalidClassName';
        $llmReturnedFunction = [
            "function_name" => "getFinancialData",
            "parameters" => [
                [ "name" => "userId", "type" => "int"],
                [ "name" => "startDate", "type" => "string"],
                [ "name" => "endDate", "type" => "string"],
            ],
            'class_name' => $className
        ];

        /* EXECUTE */
        $validationResponse = $this->request->validateFunctionSignature($llmReturnedFunction);

        /* ASSERT */
        $this->assertEquals(400, $validationResponse->code);
        $this->assertEquals("Function signature is wrong, unexpected function name getFinancialData or class name {$className}", $validationResponse->message);
    }

    /**
     * @test
     */
    public function it_validates_function_signature_and_returns_error_required_field_is_missing()
    {
        /* SETUP */
        $llmReturnedFunction = [
            "function_name" => "getFinancialData",
            "parameters" => [
                [ "name" => "startDate", "type" => "string"],
                [ "name" => "endDate", "type" => "string"],
            ],
            'class_name' => (string) $this->class
        ];

        /* EXECUTE */
        $validationResponse = $this->request->validateFunctionSignature($llmReturnedFunction);

        /* ASSERT */
        $this->assertEquals(400, $validationResponse->code);
        $this->assertEquals('Function signature is wrong, required parameter userId is not present', $validationResponse->message);
    }

    /**
     * @test
     */
    public function it_validates_function_signature_and_returns_error_when_unexpected_parameter_is_received()
    {
        /* SETUP */
        $llmReturnedFunction = [
            "function_name" => "getFinancialData",
            "parameters" => [
                [ "name" => "userId", "type" => "int"],
                [ "name" => "randomName", "type" => "int"], // unexpected parameter
                [ "name" => "startDate", "type" => "string"],
                [ "name" => "endDate", "type" => "string"],
            ],
            "class_name" => (string) $this->class
        ];

        /* EXECUTE */
        $validationResponse = $this->request->validateFunctionSignature($llmReturnedFunction);

        /* ASSERT */
        $this->assertEquals(400, $validationResponse->code);
        $this->assertEquals('Function signature is wrong, unexpected parameter(s) randomName', $validationResponse->message);
    }

    /**
     * @test
     */
    public function it_validates_function_signature_and_returns_error_when_function_name_NOT_exists()
    {
        /* SETUP */
        $invalidFunctionName = 'invalidFunction';
        $llmReturnedFunction = [
            "function_name" => $invalidFunctionName,
            "parameters" => [],
            "class_name" => (string) $this->class
        ];

        /* EXECUTE */
        $validationResponse = $this->request->validateFunctionSignature($llmReturnedFunction);

        /* ASSERT */
        $this->assertEquals(400, $validationResponse->code);
        $this->assertEquals("Function signature is wrong, unexpected function name {$invalidFunctionName} or class name {$this->class}", $validationResponse->message);
    }

    /**
     * @test
     */
    public function it_generates_function_list_from_class_and_desired_function_names()
    {
        /* SETUP */
        $functions = [
            'detailedDocBlockFunction',
            'privateFunction',
            'publicFunction',
            'functionWithMissingTypeHint',
        ];

        /* EXECUTE */
        $result = $this->request->generateFunctionList($this->class, $functions, $this->testYmlFileName);

        /* ASSERT */
        $this->assertTrue($result);

        /* CLEANUP */
        $this->deleteYmlFile($this->testYmlFileName);
    }

    /**
     * @test
     */
    public function it_generates_function_list_from_multiple_classes_just_from_function_names()
    {
        /* SETUP */
        $functionsOfExampleClass = [
            'detailedDocBlockFunction',
            'privateFunction',
        ];
        $functionsOfExampleFinanceClass = [
            'getFinancialData',
            'categorizeTransactions',
        ];
        $classExampleFinance = ExampleFinance::class;

        /* EXECUTE */
        $resultExampleClass = $this->request->generateFunctionList($this->class, $functionsOfExampleClass, $this->testYmlFileName);
        $resultExampleFianceClass = $this->request->generateFunctionList($classExampleFinance, $functionsOfExampleFinanceClass, $this->testYmlFileName);

        /* ASSERT */
        $testYmlFunctionList = Yaml::parseFile($this->testYmlFileName);
        $this->assertTrue($resultExampleClass);
        $this->assertTrue($resultExampleFianceClass);
        $this->assertEquals($functionsOfExampleClass[0], Arr::get($testYmlFunctionList[0], 'function_name'));
        $this->assertEquals($functionsOfExampleClass[1], Arr::get($testYmlFunctionList[1], 'function_name'));
        $this->assertEquals($functionsOfExampleFinanceClass[0], Arr::get($testYmlFunctionList[2], 'function_name'));
        $this->assertEquals($functionsOfExampleFinanceClass[1], Arr::get($testYmlFunctionList[3], 'function_name'));

        /* CLEANUP */
        $this->deleteYmlFile($this->testYmlFileName);
    }

    /**
     * @test
     */
    public function it_overwrites_function_docblock_if_descriptions_sent()
    {
        /* SETUP */
        // this function is for the overwriting descriptions
        $functionDataA = new FunctionData([
            'function_name' => 'detailedDocBlockFunction',
            'parameters' => [
                new ParameterData([
                    'name' => 'stringParam',
                    'type' => 'string',
                    'required' => true,
                    'description' => 'Overwritten stringParam param description',
                    'example' => 'Overwritten stringParam example',
                    'default' => 'Overwritten default value'
                ]),
                new ParameterData([
                    'name' => 'intParam',
                    'type' => 'integer',
                    'required' => true,
                    'description' => 'Overwritten intParam param description',
                    'example' => 'Overwritten intParam example',
                ]),
            ],
            'visibility' => VisibilityType::PUBLIC,
            'description' => 'Overwritten function description',
            'return' => new ReturnData([
                'type' => 'string',
                'description' => 'Overwritten function return description',
                'example' => 'Overwritten function return value example'
            ]),
        ]);
        // this function is for the setting missing descriptions while using function predefined descriptions
        $functionDataB = new FunctionData([
            'function_name' => 'detailedDocBlockFunctionWithSomeMissingDocBlock',
            'parameters' => [
                new ParameterData([
                    'name' =>'stringParam',
                    'description' => 'Adds missing stringParam param description for functionDataB',
                    'example' => 'Adds missing stringParam example for functionDataB',
                    'default' => 'Adds default value for functionDataB'
                ]),
                new ParameterData([
                    'name' => 'intParam',
                    'description' => 'Adds missing intParam param description for functionDataB',
                    'example' => 'Adds missing intParam example',
                ]),
            ],
            'return' => new ReturnData([
                'example' => 'Adds missing function return value example for functionDataB'
            ]),
        ]);
        $functions = [$functionDataA, $functionDataB];

        /* EXECUTE */
        $result = $this->request->generateFunctionList($this->class, $functions, $this->testYmlFileName);

        /* ASSERT */
        $testYmlFunctionList = Yaml::parseFile($this->testYmlFileName);
        $this->assertTrue($result);
        // functionDataA Assertions
        $this->assertEquals($functions[0]->function_name, Arr::get($testYmlFunctionList[0], 'function_name'));
        $this->assertEquals('stringParam', Arr::get($testYmlFunctionList[0]['parameters'][0], 'name'));
        $this->assertEquals('Overwritten stringParam param description', Arr::get($testYmlFunctionList[0]['parameters'][0], 'description'));
        $this->assertEquals('Overwritten stringParam example', Arr::get($testYmlFunctionList[0]['parameters'][0], 'example'));
        $this->assertEquals('Overwritten default value', Arr::get($testYmlFunctionList[0]['parameters'][0], 'default'));
        $this->assertEquals('intParam', Arr::get($testYmlFunctionList[0]['parameters'][1], 'name'));
        $this->assertEquals('Overwritten intParam param description', Arr::get($testYmlFunctionList[0]['parameters'][1], 'description'));
        $this->assertEquals('Overwritten intParam example', Arr::get($testYmlFunctionList[0]['parameters'][1], 'example'));
        // functionDataB Assertions
        $this->assertEquals($functions[1]->function_name, Arr::get($testYmlFunctionList[1], 'function_name'));
        $this->assertEquals('stringParam', Arr::get($testYmlFunctionList[1]['parameters'][0], 'name'));
        $this->assertEquals('Adds missing stringParam param description for functionDataB', Arr::get($testYmlFunctionList[1]['parameters'][0], 'description'));
        $this->assertEquals('Adds missing stringParam example for functionDataB', Arr::get($testYmlFunctionList[1]['parameters'][0], 'example'));
        $this->assertEquals('Adds default value for functionDataB', Arr::get($testYmlFunctionList[1]['parameters'][0], 'default'));
        $this->assertEquals('intParam', Arr::get($testYmlFunctionList[1]['parameters'][1], 'name'));
        $this->assertEquals('Adds missing intParam param description for functionDataB', Arr::get($testYmlFunctionList[1]['parameters'][1], 'description'));
        $this->assertEquals('Adds missing intParam example', Arr::get($testYmlFunctionList[1]['parameters'][1], 'example'));
        $this->assertEquals(VisibilityType::PUBLIC, Arr::get($testYmlFunctionList[1], 'visibility'));

        /* CLEANUP */
        $this->deleteYmlFile($this->testYmlFileName);
    }

    /**
     * @test
     */
    public function it_successfully_sets_default_value_for_parameter()
    {
        /* SETUP */
        // this function is for the overwriting descriptions
        $functionDataA = new FunctionData([
            'function_name' => 'detailedDocBlockFunction',
        ]);
        $functions = [$functionDataA];

        /* EXECUTE */
        $result = $this->request->generateFunctionList($this->class, $functions, $this->testYmlFileName);

        /* ASSERT */
        $testYmlFunctionList = Yaml::parseFile($this->testYmlFileName);
        $this->assertTrue($result);
        $this->assertEquals($functions[0]->function_name, Arr::get($testYmlFunctionList[0], 'function_name'));
        $this->assertNotNull(Arr::get($testYmlFunctionList[0], 'parameters'));
        $this->assertEquals('stringParam', Arr::get($testYmlFunctionList[0]['parameters'][0], 'name'));
        $this->assertEquals('string', Arr::get($testYmlFunctionList[0]['parameters'][0], 'type'));
        $this->assertTrue(Arr::get($testYmlFunctionList[0]['parameters'][0], 'required'));
        $this->assertEquals('intParam', Arr::get($testYmlFunctionList[0]['parameters'][1], 'name'));
        $this->assertEquals('int', Arr::get($testYmlFunctionList[0]['parameters'][1], 'type'));
        $this->assertFalse(Arr::get($testYmlFunctionList[0]['parameters'][1], 'required'));
        $this->assertEquals(2, Arr::get($testYmlFunctionList[0]['parameters'][1], 'default'));
        $this->assertEquals(VisibilityType::PUBLIC, Arr::get($testYmlFunctionList[0], 'visibility'));

        /* CLEANUP */
        $this->deleteYmlFile($this->testYmlFileName);
    }

    /**
     * @test
     */
    public function it_successfully_generates_function_list_when_no_info_is_provided_by_user_and_no_predefined_function_info_exists()
    {
        /* SETUP */
        // this function is for the overwriting descriptions
        $functions = ['noExtraInfoProvidedFunction'];

        /* EXECUTE */
        $result = $this->request->generateFunctionList($this->class, $functions, $this->testYmlFileName);

        /* ASSERT */
        $testYmlFunctionList = Yaml::parseFile($this->testYmlFileName);
        $this->assertTrue($result);
        $this->assertEquals($functions[0], Arr::get($testYmlFunctionList[0], 'function_name'));
        $this->assertNotNull(Arr::get($testYmlFunctionList[0], 'parameters'));
        $this->assertEquals('stringParam', Arr::get($testYmlFunctionList[0]['parameters'][0], 'name'));
        $this->assertEquals('mixed', Arr::get($testYmlFunctionList[0]['parameters'][0], 'type'));
        $this->assertTrue(Arr::get($testYmlFunctionList[0]['parameters'][0], 'required'));
        $this->assertEquals('intParam', Arr::get($testYmlFunctionList[0]['parameters'][1], 'name'));
        $this->assertEquals('mixed', Arr::get($testYmlFunctionList[0]['parameters'][1], 'type'));
        $this->assertTrue(Arr::get($testYmlFunctionList[0]['parameters'][1], 'required'));
        $this->assertEquals(VisibilityType::PUBLIC, Arr::get($testYmlFunctionList[0], 'visibility'));

        /* CLEANUP */
        $this->deleteYmlFile($this->testYmlFileName);
    }

    /**
     * @test
     */
    public function it_successfully_responds_with_error_data_when_invalid_function_name_is_sent_to_generate_function_list()
    {
        /* SETUP */
        // this function is for the overwriting descriptions
        $functions = ['invalidFunctionName'];

        /* EXECUTE */
        $response = $this->request->generateFunctionList($this->class, $functions, $this->testYmlFileName);

        /* ASSERT */
        $this->assertInstanceOf(ErrorData::class, $response);
        $this->assertEquals(400, $response->code);
        $this->assertEquals("Function {$functions[0]} does not exist in class {$this->class}", $response->message);

        /* CLEANUP */
        $this->deleteYmlFile($this->testYmlFileName);
    }

    /**
     * @test
     */
    public function it_makes_call_to_functions_that_signature_provided()
    {
        /* SETUP */
        $this->markTestSkipped('Will be developed for later versions');

        /* EXECUTE */
        $this->request->callFunctions();

        /* ASSERT */

    }

    /**
     * TODO:
     * Things that will be developed and should be considered:
     * - DONE handle the case there is no function is found
     * - order of params also matter for functions.yml
     * - for functions request to LLM provider, i am also getting value in response while in ContentPayloadTemplate it is strictly asked to use schema, so play with instructions
     * - In case llm returns with the response that is missing some required parameter, there can be a feedback for it in case of missing field, another llm request is made or request is made with the response to ask for correcting it
     * - callFunctions which makes the function calls, function with its parameters should be provided so that it will be able to make the function call.
     * - Be cautious! adding more to functions.yml will increase the cost of llm requests
     * - Another service can be developed which takes php code class, and asks for the functions that will be used, so it will give formatted functions.yml
     * - PromptAlchemistAPI => remove if unnecessary since constructor is empty
     *
     * - add method names of request to facade class docblock, so that we wil be able to call them with facade
     * - add readme that functions can be either names array or functionData array, if your project is written in a good way, you can just provide the array of names as in it_generates_function_list_from_class_and_desired_function_names
     */

    /**
     * todo:
     * - For the function_signature_mapping in config file, we need a good readme explanation and as mentioned above, a functionality that it generates functions.yml
     * [*] indicates indexed arrays, [] indicates associative arrays, {key} indicates the dynamic associative key
     *
     * Other samples for parameter_required_info:
     * for parameter_definitions['startDate' => ['required' => true]] --> parameter_required_info = parameter_definitions.{key}.required_field  https://docs.cohere.com/docs/tool-use
     * for parameters[ ['name' => 'startDate', 'required' => true], ['name' => 'category', 'required' => true] ] --> parameter_required_info = parameters[].name.required
     * for parameters['startDate' => ['type' => 'date', 'example' => '2024'], 'category' => ['type' => 'string'] ], required['startDate', 'category']  --> parameter_required_info = required.{key} ??
     *  here it gets messy as above case, when associative array is used so how to recognize the keys as something that will be retrieved for using in codebase, function name, required part should be handled carefully
     */
}