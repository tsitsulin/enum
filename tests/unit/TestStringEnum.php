<?php declare(strict_types=1);

namespace Tsitsulin\Enum\Tests;

use Tsitsulin\Enum\StringEnum;

/**
 * @method static self Case1()
 * @method static self Case2()
 */
class TestStringEnum extends StringEnum
{
    protected const Case1 = 'value1';
    protected const Case2 = 'value2';
}