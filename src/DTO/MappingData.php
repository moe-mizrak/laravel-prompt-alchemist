<?php

namespace MoeMizrak\LaravelPromptAlchemist\DTO;

use Spatie\DataTransferObject\DataTransferObject;

/**
 * DTO for mapping function etc.
 *
 * Class MappingData
 * @package MoeMizrak\LaravelPromptAlchemist\DTO
 */
class MappingData extends DataTransferObject
{
    /**
     * Path of the data.
     * e.g. 'parameters.name'
     *
     * @var string
     */
    public string $path;

    /**
     * Type of the data.
     * e.g. boolean, integer etc.
     *
     * @var string
     */
    public string $type;
}