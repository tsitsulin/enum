<?php declare(strict_types=1);

namespace Tsitsulin\Enum;

/**
 * Optional enum instances isolation Trait.
 * Each enum that has implemented it has isolated private instances.
 * Otherwise, the instances of different enums will be collected in the StringEnum or/and IntEnum.
 * It is recommended to implement it, so as not to break the encapsulation.
 * @see StringEnum
 * @see IntEnum
 *
 * @author Sergey Tsitsulin <tsitsulin@gmail.com>
 */
trait PrivateEnum
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
}