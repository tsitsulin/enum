<?php

declare(strict_types=1);

namespace Tsitsulin\Enum;

use ReflectionClass;
use Tsitsulin\Enum\Errors\EnumCaseCannotBeModifiedError;
use Tsitsulin\Enum\Errors\InvalidEnumCaseTypeError;
use Tsitsulin\Enum\Errors\UnexpectedEnumCaseCallError;
use Tsitsulin\Enum\Errors\UnexpectedEnumCaseTypeError;
use UnexpectedValueException;

/**
 * Base Enum class.
 *
 * @property-read string $name
 * @property-read int|string $value
 *
 * @author Sergey Tsitsulin <tsitsulin@gmail.com>
 */
abstract class Enum
{
    /**
     * Internal name.
     *
     * @var string
     */
    private string $internalName;

    /**
     * Internal value.
     *
     * @var string|int
     */
    private $internalValue;

    /**
     * Prevent construction.
     */
    final private function __construct()
    {
    }

    /**
     * Get enum instances.
     *
     * @return static[]|null Array reference
     */
    abstract protected static function &getInstances(): ?array;

    /**
     * Validate value by special rules.
     *
     * @param mixed $value
     *
     * @return int|string
     * @throws InvalidEnumCaseTypeError
     */
    abstract protected static function validateValue($value);

    /**
     * @link https://www.php.net/manual/en/class.unitenum.php
     *
     * @return static[]
     */
    final public static function cases(): array
    {
        return self::refreshInstances();
    }

    /**
     * @link https://www.php.net/manual/en/class.backedenum.php
     *
     * @param mixed $value
     *
     * @return static
     * @throws UnexpectedEnumCaseTypeError
     * @throws UnexpectedValueException
     */
    final public static function from($value): Enum
    {
        static::validateValue($value);

        foreach (self::refreshInstances() as $instance) {
            if ($value === $instance->value) {
                return $instance;
            }
        }

        throw new UnexpectedEnumCaseTypeError(
            sprintf(
                'Value %s is not a valid backing value for enum %s',
                print_r($value, true),
                static::class,
            ),
        );
    }

    /**
     * @link https://www.php.net/manual/en/class.backedenum.php
     *
     * @param mixed $value
     *
     * @return static|null
     */
    final public static function tryFrom($value): ?Enum
    {
        try {
            static::validateValue($value);
        } catch (InvalidEnumCaseTypeError $e) {
            return null;
        }

        foreach (self::refreshInstances() as $instance) {
            if ($value === $instance->value) {
                return $instance;
            }
        }

        return null;
    }

    /**
     * Refresh it after deserialization when PHP created a different instance.
     *
     * After deserialization via PHP {@see unserialize()}:
     * ```php
     *     if ($enum1 === $deserializedEnum1) // False
     *     if ($enum1 == $deserializedEnum1) // True
     *     if ($enum1.name === $deserializedEnum1.name) // True
     *     if ($enum1.value === $deserializedEnum1.value) // True
     * ```
     *
     * After deserialization via {@see deserialize_enum()} which use it:
     * ```php
     *     $deserializedEnum1 = deserialize_enum($serializedEnum1);
     *
     *     if ($enum1 === $deserializedEnum1) // True
     *     ...
     * ```
     *
     * Don't call it directly to make it easier to migrate to PHP 8.1+.
     * Use {@see deserialize_enum()} as synonym of {@see unserialize()} instead if deserialization is needed.
     *
     * @return static
     */
    final public function deserialize(): Enum
    {
        return self::__callStatic($this->internalName, []);
    }

    /**
     * Get enum by name.
     * Creat one instance per enum.
     *
     * @param string $name
     * @param mixed[] $arguments
     *
     * @return static
     * @throws InvalidEnumCaseTypeError
     */
    final public static function __callStatic(string $name, array $arguments)
    {
        $enum = static::getInstances()[$name] ?? null;
        if ($enum instanceof self) {
            return $enum;
        }

        return self::createInstanceByName($name);
    }

    /**
     * Get enum name or value.
     *
     * @param string $name
     *
     * @return int|string
     * @throws UnexpectedEnumCaseTypeError
     */
    final public function __get(string $name)
    {
        if (in_array($name, ['name', 'value'])) {
            return $this->{'internal' . ucfirst($name)};
        }

        throw new UnexpectedEnumCaseCallError("Case $name is unexpected. Try call 'name' or 'value'.");
    }

    /**
     * Prevent name or value modification.
     *
     * @param string $name
     * @param string|int $value
     *
     * @throws EnumCaseCannotBeModifiedError
     */
    final public function __set(string $name, $value): void
    {
        throw new EnumCaseCannotBeModifiedError();
    }

    /**
     * Emulate PHP behaviour.
     *
     * @param string $name
     *
     * @return bool
     */
    final public function __isset(string $name): bool
    {
        return true;
    }

    /**
     * Create an instance of Enum.
     *
     * @param string $name
     *
     * @return static
     * @throws InvalidEnumCaseTypeError
     */
    private static function createInstanceByName(string $name): Enum
    {
        $value = constant(static::class . '::' . $name);

        $enum = new static();
        $value = $enum::validateValue($value);

        static::getInstances()[$name] = $enum;

        $enum->internalName = $name;
        $enum->internalValue = $value;

        return $enum;
    }

    /**
     * Refresh instances
     *
     * @return static[]
     */
    private static function refreshInstances(): array
    {
        $cases = (new ReflectionClass(static::class))->getConstants();
        foreach ($cases as $name => $value) {
            static::{$name}();
        }

        /** @var static[] $instances */
        $instances = array_values(static::getInstances() ?? []);

        return $instances;
    }
}
