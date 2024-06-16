<?php

namespace MoeMizrak\LaravelPromptAlchemist\DTO;

use Spatie\DataTransferObject\DataTransferObject;

/**
 * DTO for docblock of a function.
 *
 * Class DocBlockData
 * @package MoeMizrak\LaravelPromptAlchemist\DTO
 */
class DocBlockData extends DataTransferObject
{
    /**
     * Description in the docblock.
     * e.g. This function calculates ...
     *
     * @var string|null
     */
    public ?string $description;

    /**
     * Descriptions in parameter tag @ param
     * e.g. @ param bool $isRequired - If the value is required in ...
     * e.g. @ param int $sum - Summation of the financial ...
     *
     * @var array|null
     */
    public ?array $param_descriptions;

    /**
     * Description in return tag @ return
     * e.g. @ return bool - If the function is calculated ...
     *
     * @var string|null
     */
    public ?string $return_description;
}