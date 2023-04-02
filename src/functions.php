<?php

/**
 * Functions polyfill for PHP 8.1 enum.
 *
 * @author Sergey Tsitsulin <tsitsulin@gmail.com>
 */

declare(strict_types=1);

// phpcs:disable

if (function_exists('enum_exists')) {
    return;
}

// phpcs:enable

/**
 * @link https://www.php.net/manual/en/function.enum-exists.php
 *
 * @param string $enum
 * @param bool $autoload
 *
 * @return bool
 * @throws Exception
 */
function enum_exists(string $enum, bool $autoload = true): bool
{
    return class_exists($enum, $autoload);
}

/**
 * Deserialize enum
 *
 * @see unserialize()
 * @see \Tsitsulin\Enum\Enum::deserialize()
 *
 * @param string $enum
 * @param array{allowed_classes?:string[]|bool,max_depth?:int} $options
 *
 * @return \Tsitsulin\Enum\Enum|mixed
 */
function deserialize_enum(string $enum, array $options = [])
{
    $deserialized = unserialize($enum, $options);

    if ($deserialized instanceof \Tsitsulin\Enum\Enum) {
        return $deserialized->deserialize();
    }

    return $deserialized;
}
