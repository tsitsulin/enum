<?php // phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols

declare(strict_types=1);

namespace Tsitsulin\Enum\Examples;

require(__DIR__ . '/../vendor/autoload.php');

echo 'Examples:' . PHP_EOL;

// phpcs:disable Squiz.PHP.CommentedOutCode.Found

example('Get an enum', HttpMethodEnum::Post()); // HttpMethodEnum

example('Get an enum name', HttpMethodEnum::Post()->name); // 'Post'

example('Get an enum value', HttpMethodEnum::Post()->value); // 'post'

echo PHP_EOL . 'Get enum cases:' . PHP_EOL;
foreach (HttpMethodEnum::cases() as $enum) {
    print_r([$enum->name => $enum->value]); // [..., 'Post' => 'post', ...]
};

example('Get a enum name via from', HttpMethodEnum::from('post')->name); // 'Post'

example('Try to get an enum value via tryFrom', HttpMethodEnum::tryFrom('INVALID')); // NULL

example('Check if an enum exists', enum_exists(HttpMethodEnum::class)); // true

echo PHP_EOL . 'Ok.' . PHP_EOL;

/**
 * @param string $title
 * @param mixed $result
 *
 * @return void
 */
function example(string $title, $result): void
{
    echo PHP_EOL . "$title:" . PHP_EOL;
    var_export($result);
    echo PHP_EOL;
}
