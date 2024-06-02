<?php

namespace MoeMizrak\LaravelPromptAlchemist\Tests;

use MoeMizrak\LaravelPromptAlchemist\PromptAlchemistRequest;

class PromptAlchemistTest extends TestCase
{
    private PromptAlchemistRequest $request;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

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
    }
}