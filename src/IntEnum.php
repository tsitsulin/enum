<?php

declare(strict_types=1);

namespace Tsitsulin\Enum;

use Tsitsulin\Enum\Errors\InvalidEnumCaseTypeError;

/**
 * Integer Enum Class.
 *
 * @property-read string $name
 * @property-read int $value
 *
 * @author Sergey Tsitsulin <tsitsulin@gmail.com>
 */
abstract class IntEnum extends Enum
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
    final protected static function validateValue($value): int
    {
        if (!is_int($value)) {
            throw new InvalidEnumCaseTypeError(
                sprintf(
                    'Value %s must be type of int, %s given.',
                    print_r($value, true),
                    gettype($value),
                ),
            );
        }

        if ($value < 0) {
            throw new InvalidEnumCaseTypeError(
                'IntEnum value must be positive.',
            );
        }

        return $value;
    }
}
