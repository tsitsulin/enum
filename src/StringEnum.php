<?php

declare(strict_types=1);

namespace Tsitsulin\Enum;

use Tsitsulin\Enum\Errors\InvalidEnumCaseTypeError;

/**
 * String Enum Class.
 *
 * @property-read string $name
 * @property-read string $value
 *
 * @author Sergey Tsitsulin <tsitsulin@gmail.com>
 */
abstract class StringEnum extends Enum
{
    /**
     * Enum instances.
     *
     * @var static[][]
     */
    private static array $instances = [];

    /**
     * {@inheritDoc}
     */
    protected static function &getInstances(): ?array
    {
        return self::$instances[static::class];
    }

    /**
     * {@inheritDoc}
     */
    final protected static function validateValue($value): string
    {
        if (!is_string($value)) {
            throw new InvalidEnumCaseTypeError(
                sprintf(
                    "Value %s must be type of string, %s given.",
                    print_r($value, true),
                    gettype($value),
                ),
            );
        }

        if (empty($value)) {
            throw new InvalidEnumCaseTypeError(
                'StringEnum value must be not empty.',
            );
        }

        return $value;
    }
}
