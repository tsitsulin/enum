<?php declare(strict_types=1);

namespace Tsitsulin\Enum\Tests;

use Tsitsulin\Enum\IntEnum;

/**
 * @method static self Case1()
 * @method static self Case2()
 */
final class TestInvalidIntEnum extends IntEnum
{
    protected const Case1 = '101';
    protected const Case2 = '102';
}