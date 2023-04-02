<?php

declare(strict_types=1);

namespace Tsitsulin\Enum\Tests;

use Tsitsulin\Enum\PrivateEnum;
use Tsitsulin\Enum\StringEnum;

/**
 * @method static self Case1()
 * @method static self Case2()
 *
 * phpcs:disable Generic.NamingConventions.UpperCaseConstantName
 */
class TestStringPrivateEnum extends StringEnum
{
    use PrivateEnum;

    protected const Case1 = 'value11';
    protected const Case2 = 'value22';
}
