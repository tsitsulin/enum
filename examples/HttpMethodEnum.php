<?php

declare(strict_types=1);

namespace Tsitsulin\Enum\Examples;

use Tsitsulin\Enum\PrivateEnum;
use Tsitsulin\Enum\StringEnum;

/**
 * @method static self Get()
 * @method static self Post()
 * @method static self Put()
 * @method static self Patch()
 * @method static self Delete()
 *
 * phpcs:disable Generic.NamingConventions.UpperCaseConstantName
 */
final class HttpMethodEnum extends StringEnum
{
    use PrivateEnum;

    protected const Get = 'get';
    protected const Post = 'post';
    protected const Put = 'put';
    protected const Patch = 'patch';
    protected const Delete = 'delete';
}
