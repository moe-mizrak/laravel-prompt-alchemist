<?php

namespace MoeMizrak\LaravelPromptAlchemist\DTO;

use MoeMizrak\LaravelPromptAlchemist\Types\VisibilityType;
use Spatie\DataTransferObject\DataTransferObject;

/**
 * DTO for necessary function signatures such as name of the function, parameters etc..
 *
 * Class FunctionData
 * @package MoeMizrak\LaravelPromptAlchemist\DTO
 */
class FunctionData extends DataTransferObject
{
    /**
     * Name of the function.
     *
     * @var string
     */
    public string $function_name;

    /**
     * Parameters of the function.
     *
     * @var ParameterData[]|null
     */
    public ?array $parameters;

    /**
     * Visibility of function.
     * Default is public.
     *
     * @var string|null
     */
    public ?string $visibility = VisibilityType::PUBLIC;

    /**
     * Description of the function.
     * Such as "Retrieves financial data for a specific user and timeframe."
     *
     * @var string|null
     */
    public ?string $description;

    /**
     * Return data of the function.
     *
     * @var ReturnData|null
     */
    public ?ReturnData $return;
}