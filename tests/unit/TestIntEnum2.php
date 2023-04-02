<?php

declare(strict_types=1);

namespace Tsitsulin\Enum\Tests;

use Tsitsulin\Enum\IntEnum;

/**
 * @method static self Case1()
 * @method static self Case2()
 *
 * phpcs:disable Generic.NamingConventions.UpperCaseConstantName
 */
final class TestIntEnum2 extends IntEnum
{
    protected const Case1 = 110;
    protected const Case2 = 120;
}
