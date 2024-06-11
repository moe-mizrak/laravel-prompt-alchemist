<?php

namespace MoeMizrak\LaravelPromptAlchemist\Types;

/**
 * It keeps visibility types of properties, methods, and constants within your classes.
 *
 * Class VisibilityType
 * @package MoeMizrak\LaravelPromptAlchemist\Types
 */
class VisibilityType
{
    /**
     * Public visibility type.
     *
     * @var string
     */
    const PUBLIC = 'public';

    /**
     * Private visibility type.
     *
     * @var string
     */
    const PRIVATE = 'private';

    /**
     * Protected visibility type.
     *
     * @var string
     */
    const PROTECTED = 'protected';
}