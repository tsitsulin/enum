<?php

declare(strict_types=1);

namespace Tsitsulin\Enum\Tests;

use Tsitsulin\Enum\IntEnum;
use Tsitsulin\Enum\PrivateEnum;

/**
 * @method static self Case1()
 * @method static self Case2()
 *
 * phpcs:disable Generic.NamingConventions.UpperCaseConstantName
 */
final class TestIntPrivateEnum extends IntEnum
{
    use PrivateEnum;

    protected const Case1 = 111;
    protected const Case2 = 222;
}
