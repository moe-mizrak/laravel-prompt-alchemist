<?php

namespace MoeMizrak\LaravelPromptAlchemist\Tests;

use Illuminate\Support\Arr;
use MoeMizrak\LaravelOpenrouter\DTO\ChatData;
use MoeMizrak\LaravelOpenrouter\DTO\MessageData;
use MoeMizrak\LaravelOpenrouter\DTO\ResponseData;
use MoeMizrak\LaravelOpenrouter\Types\RoleType;
use MoeMizrak\LaravelPromptAlchemist\PromptAlchemistRequest;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class PromptAlchemistTest extends TestCase
{
    private PromptAlchemistRequest $request;

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

        $this->request = $this->app->make(PromptAlchemistRequest::class);
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
            'role' => RoleType::USER,
        ]);
        $chatData = new ChatData([
            'messages' => [
                $messageData,
            ],
            'model'      => $this->model,
            'max_tokens' => 900,
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
                [ "name" => "userId", "type" => "integer"],
                [ "name" => "startDate", "type" => "date"],
                [ "name" => "endDate", "type" => "date"],
            ]
        ];

        /* EXECUTE */
        $validationResponse = $this->request->validateFunctionSignature($llmReturnedFunction);

        /* ASSERT */
        $this->assertTrue($validationResponse);
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
                [ "name" => "startDate", "type" => "date"],
                [ "name" => "endDate", "type" => "date"],
            ]
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
                [ "name" => "userId", "type" => "integer"],
                [ "name" => "randomName", "type" => "integer"], // unexpected parameter
                [ "name" => "startDate", "type" => "date"],
                [ "name" => "endDate", "type" => "date"],
            ]
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
     * - handle the case there is no function is found
     * - order of params also matter for functions.yml
     * - for functions request to ai provider, i am also getting value in response while in ContentPayloadTemplate it is strictly asked to use schema, so play with instructions
     * - ResponsePayloadTemplate instructions can also be retrieved dynamically from the config so when it is necessary to make changes in instructions for specific ai provider, then it will not require package changes
     * - In case llm returns with the response that is missing some required parameter, there can be a feedback for it in case of missing field, another llm request is made or request is made with the response to ask for correcting it
     * - callFunctions which makes the function calls, function with its parameters should be provided so that it will be able to make the function call.
     * - Be cautious! adding more to functions.yml will increase the cost of llm requests
     * - Another service can be developed which takes php code class, and asks for the functions that will be used, so it will give formatted functions.yml
     * - PromptAlchemistAPI => remove if unnecessary since constructor is empty
     *
     * - add method names of request to facade class docblock, so that we wil be able to call them with facade
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