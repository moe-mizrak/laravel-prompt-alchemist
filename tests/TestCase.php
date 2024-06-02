<?php

namespace MoeMizrak\LaravelPromptAlchemist\Tests;

use Illuminate\Foundation\Testing\WithFaker;
use MoeMizrak\LaravelPromptAlchemist\Facades\LaravelPromptAlchemist;
use MoeMizrak\LaravelPromptAlchemist\PromptAlchemistServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    use WithFaker;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @param $app
     * @return string[]
     */
    protected function getPackageProviders($app): array
    {
        return [
            PromptAlchemistServiceProvider::class,
        ];
    }

    /**
     * @param $app
     * @return string[]
     */
    protected function getPackageAliases($app): array
    {
        return [
            'LaravelPromptAlchemist' => LaravelPromptAlchemist::class,
        ];
    }
}