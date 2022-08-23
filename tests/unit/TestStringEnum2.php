<?php declare(strict_types=1);

namespace Tsitsulin\Enum\Tests;

use Tsitsulin\Enum\StringEnum;

/**
 * @method static self Case1()
 * @method static self Case2()
 */
class TestStringEnum2 extends StringEnum
{
    protected const Case1 = 'value10';
    protected const Case2 = 'value20';
}