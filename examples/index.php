<?php declare(strict_types=1);

namespace Tsitsulin\Enum\Examples;

require(__DIR__ . '/../vendor/autoload.php');

echo 'Examples:' . PHP_EOL;

example('Get an enum', HttpMethodEnum::Post()); // HttpMethodEnum

example('Get an enum name', HttpMethodEnum::Post()->name); // 'Post'

example('Get an enum value', HttpMethodEnum::Post()->value); // 'post'

echo PHP_EOL . 'Get enum cases:' . PHP_EOL;
foreach (HttpMethodEnum::cases() as $enum) {
    print_r([$enum->name => $enum->value]); // [..., 'Post' => 'post', ...]
};

example('Get a enum name via from', HttpMethodEnum::from('post')->name); // 'Post'

example('Try to get an enum value via tryFrom', HttpMethodEnum::tryFrom('INVALID')); // Null

example('Check if an enum exists', enum_exists(HttpMethodEnum::class)); // True

echo PHP_EOL . 'Ok.' . PHP_EOL;

/**
 * @param string $title
 * @param mixed $result
 */
function example(string $title, $result)
{
    echo PHP_EOL . "$title:" . PHP_EOL;
    print_r($result === null
        ? 'Null'
        : ($result === true
            ? 'True'
            : $result)
    );
    echo PHP_EOL;
}