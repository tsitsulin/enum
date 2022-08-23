<?php declare(strict_types=1);

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
     * @var Enum[][]
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
    final protected function validateValue($value): void
    {
        if (!is_int($value)) {
            throw new InvalidEnumCaseTypeError("IntEnum values can only be of the Int type.");
        }
    }
}