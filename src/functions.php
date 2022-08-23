<?php declare(strict_types=1);

/**
 * Functions polyfill for PHP 8.1 enum.
 *
 * @author Sergey Tsitsulin <tsitsulin@gmail.com>
 */

/**
 * @link https://www.php.net/manual/en/function.enum-exists.php
 *
 * @param string $enum
 * @param bool $autoload
 * @return bool
 * @throws Exception
 */
if (function_exists('enum_exists')) {
    return;
}
function enum_exists(string $enum, bool $autoload = true): bool
{
    return class_exists($enum, $autoload);
}