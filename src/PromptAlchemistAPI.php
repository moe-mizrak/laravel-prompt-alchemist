<?php

namespace MoeMizrak\LaravelPromptAlchemist;

use MoeMizrak\LaravelPromptAlchemist\Helpers\FunctionCaller;
use MoeMizrak\LaravelPromptAlchemist\Helpers\FunctionListGenerator;
use MoeMizrak\LaravelPromptAlchemist\Helpers\FunctionSignatureValidator;
use MoeMizrak\LaravelPromptAlchemist\Helpers\InstructionsGenerator;
use MoeMizrak\LaravelPromptAlchemist\Helpers\PayloadBuilder;

/**
 * This abstract class forms the response from PromptAlchemist
 *
 * Class PromptAlchemistAPI
 * @package MoeMizrak\LaravelPromptAlchemist
 */
abstract class PromptAlchemistAPI
{
    /**
     * PromptAlchemistAPI constructor.
     *
     * @param PayloadBuilder $payloadBuilder
     * @param FunctionSignatureValidator $functionSignatureValidator
     * @param FunctionListGenerator $functionListGenerator
     * @param InstructionsGenerator $instructionsGenerator
     * @param FunctionCaller $functionCaller
     */
    public function __construct(
        protected PayloadBuilder $payloadBuilder,
        protected FunctionSignatureValidator $functionSignatureValidator,
        protected FunctionListGenerator $functionListGenerator,
        protected InstructionsGenerator $instructionsGenerator,
        protected FunctionCaller $functionCaller,
    ) {}
}