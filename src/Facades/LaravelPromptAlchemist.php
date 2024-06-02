<?php

namespace MoeMizrak\LaravelPromptAlchemist\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Facade for LaravelPromptAlchemist.
 *
 * todo: here method names of request, so that we wil be able to call them with facade
 */
class LaravelPromptAlchemist extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-prompt-alchemist';
    }
}