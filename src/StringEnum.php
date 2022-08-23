<?php declare(strict_types=1);

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
        if (!is_string($value)) {
            throw new InvalidEnumCaseTypeError("StringEnum values can only be of the String type.");
        }
    }
}