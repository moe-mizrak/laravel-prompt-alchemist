<?php

namespace MoeMizrak\LaravelPromptAlchemist\DTO;

use Spatie\DataTransferObject\DataTransferObject;

/**
 * DTO for function result.
 *
 * Class FunctionResultData
 * @package MoeMizrak\LaravelPromptAlchemist\DTO
 */
class FunctionResultData extends DataTransferObject
{
    /**
     * Name of the function.
     *
     * @var string
     */
    public string $function_name;

    /**
     * Result of the function call.
     *
     * @var mixed
     */
    public mixed $result;
}