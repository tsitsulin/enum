<?php declare(strict_types=1);

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
 * @author Sergey Tsitsulin <tsitsulin@gmail.com>
 */
abstract class Enum
{
    /**
     * Internal name.
     * @var string
     */
    private string $_name;
    /**
     * Internal value.
     * @var string|int
     */
    private $_value;

    /**
     * Prevent construction.
     */
    private function __construct()
    {
    }

    /**
     * Get enum instances.
     *
     * @return Enum[]|null Array reference
     */
    abstract protected static function &getInstances(): ?array;

    /**
     * Validate value by special rules.
     * @param int|string $value
     * @throws InvalidEnumCaseTypeError
     */
    abstract protected function validateValue($value): void;

    /**
     * @link https://www.php.net/manual/en/class.unitenum.php
     * @return static[]
     */
    final public static function cases(): array
    {
        // Init all
        $cases = (new ReflectionClass(static::class))->getConstants();
        foreach ($cases as $name => $value) {
            static::{$name}();
        }

        return array_values(static::getInstances() ?? []);
    }

    /**
     * @link https://www.php.net/manual/en/class.backedenum.php
     *
     * @param $value
     * @return static
     * @throws UnexpectedEnumCaseTypeError
     * @throws UnexpectedValueException
     */
    final public static function from($value): Enum
    {
        if (static::class instanceof IntEnum && !is_int($value)) {
            throw new UnexpectedEnumCaseTypeError(
                sprintf("Value %s must be type of int, %s given.", $value, gettype($value))
            );
        }

        if (static::class instanceof StringEnum && !is_string($value)) {
            throw new UnexpectedValueException(
                sprintf("Value %s must be type of string, %s given.", $value, gettype($value))
            );
        }

        foreach (static::getInstances() as $instance) {
            if ($value === $instance->value) {
                return $instance;
            }
        }

        throw new UnexpectedEnumCaseTypeError(
            sprintf('Value %s is not a valid backing value for enum %s', $value, static::class)
        );
    }

    /**
     * @link https://www.php.net/manual/en/class.backedenum.php
     *
     * @param $value
     * @return static|null
     */
    final public static function tryFrom($value): ?Enum
    {
        if (static::class instanceof IntEnum && !is_int($value)
            || static::class instanceof StringEnum && !is_string($value)
        ) {
            return null;
        }

        foreach (static::getInstances() as $instance) {
            if ($value === $instance->value) {
                return $instance;
            }
        }

        return null;
    }

    /**
     * Get enum by name.
     * Creat one instance per enum.
     *
     * @param string $name
     * @param array $arguments
     * @return static
     * @example Enum::NAME()
     * @throws InvalidEnumCaseTypeError
     */
    final public static function __callStatic(string $name, array $arguments)
    {
        $enum = static::getInstances()[$name] ?? null;
        if ($enum instanceof Enum) {
            return $enum;
        }

        return static::createInstanceByName($name);
    }

    /**
     * Get enum name or value.
     *
     * @param string $name
     * @return int|string
     * @throws UnexpectedEnumCaseTypeError
     */
    final public function __get(string $name)
    {
        if (in_array($name, ['name', 'value'])) {
            return $this->{"_$name"};
        }

        throw new UnexpectedEnumCaseCallError("Case $name is unexpected. Try call 'name' or 'value'.");
    }

    /**
     * Prevent name or value modification.
     *
     * @param string $name
     * @param string|int $value
     * @throws EnumCaseCannotBeModifiedError
     */
    final public function __set(string $name, $value): void
    {
        throw new EnumCaseCannotBeModifiedError();
    }

    /**
     * Create an instance of Enum.
     *
     * @param string $name
     * @return Enum
     * @throws InvalidEnumCaseTypeError
     */
    private static function createInstanceByName(string $name): Enum
    {
        $value = constant(static::class . '::' . $name);
        $enum = new static();
        $enum->validateValue($value);
        // Continue after validation.
        static::getInstances()[$name] = $enum;
        $enum->_name = $name;
        $enum->_value = $value;
        return $enum;
    }
}