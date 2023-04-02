ENUM
====

This is a PHP 7.4 to PHP 8.0 ENUM polyfill.

# Quick start

Install the library using [composer](https://getcomposer.org):

    php composer.phar require tsitsulin/enum

Usage
-----

Create a string enum:

```php
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
```

Create an int enum:

```php
<?php

declare(strict_types=1);

namespace Tsitsulin\Enum\Examples;

use Tsitsulin\Enum\PrivateEnum;
use Tsitsulin\Enum\IntEnum;

/**
 * @method static self Ok()
 * @method static self MovedPermanently()
 * 
 * phpcs:disable Generic.NamingConventions.UpperCaseConstantName
 */
final class HttpResponseEnum extends IntEnum
{
    use PrivateEnum;

    protected const Ok = 200;
    protected const MovedPermanently = 301;
}
```

**PrivateEnum** is optional enum instances isolation Trait: 
* Each enum that has implemented it has isolated private instances.
* Otherwise, the instances of different enums will be collected in the StringEnum or/and IntEnum.
* It is recommended to implement it, so as not to break the encapsulation.

Access to name and value:

```php
$caseName = HttpResponseEnum::Ok()->name; // 'Ok'
$caseValue = HttpResponseEnum::Ok()->value; // 200
```

Protected from modification or creation:

```php
new HttpResponseEnum(); // Fatal error
HttpResponseEnum::Ok()->value = 100; // EnumCaseCannotBeModifiedError
```

[UnitEnum](https://www.php.net/manual/en/class.unitenum.php) cases:

```php
foreach (HttpResponseEnum::cases() as $httpResponse) {
    print_r([$httpResponse->name => $httpResponse->value]);
};
// ['Ok' => 200]
// ['MovedPermanently' => 301]
```

[BackedEnum](https://www.php.net/manual/en/class.backedenum.php) from, tryFrom:

```php
$caseName = HttpResponseEnum::from(200)->name); // name of case 'Ok'
$caseName = HttpResponseEnum::from('invalidValue')->name); // \Tsitsulin\Enum\Errors\UnexpectedEnumCaseTypeError
$caseName = HttpResponseEnum::tryFrom('invalidValue')->name); // Null
```

PHP 8.1 function enum_exists:

```php
if (enum_exists(HttpResponseEnum::Ok())) { // True
    ...
}
```

Implementation differences from the original PHP 8.1 ENUM
---------------------------------------------------------

* Access to ENUM:
    ```php
    $original = HttpResponseEnum::Ok; // PHP 8.1
    $current = HttpResponseEnum::Ok();
    ```
* Typing:
    ```php
    $original = function (enum|HttpResponseEnum $enum) {}
    $current = function(\Tsitsulin\Enum|HttpResponseEnum $enum) {}
    ```
* Originally name of the Enum itself is case-insensitive. Current Enum implementation based on classes and case-sensitive:
* Use `deserialize_enum()` this package function as synonym of `unserialize()` if deserialization is needed.
    * After deserialization via PHP `unserialize()`:
    ```php
    if ($enum1 === $deserializedEnum1) // False
    if ($enum1 == $deserializedEnum1) // True
    if ($enum1.name === $deserializedEnum1.name) // True
    if ($enum1.value === $deserializedEnum1.value) // True
    ```
    * After deserialization via `deserialize_enum()`:
    ```php
    $deserializedEnum1 = deserialize_enum($serializedEnum1);

    if ($enum1 === $deserializedEnum1) // True
    ...
    ```
    * Don't call `Enum::Case()->unserialize()` directly to make it easier to migrate to `PHP 8.1+`.
