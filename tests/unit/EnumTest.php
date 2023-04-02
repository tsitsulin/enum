<?php

declare(strict_types=1);

namespace Tsitsulin\Enum\Tests;

use Closure;
use Codeception\Test\Unit;
use Error;
use Exception;
use ReflectionClass;
use ReflectionException;
use Throwable;
use Tsitsulin\Enum\Enum;
use Tsitsulin\Enum\Errors\EnumCaseCannotBeModifiedError;
use Tsitsulin\Enum\Errors\InvalidEnumCaseTypeError;
use Tsitsulin\Enum\Errors\UnexpectedEnumCaseCallError;
use Tsitsulin\Enum\Errors\UnexpectedEnumCaseTypeError;
use Tsitsulin\Enum\IntEnum;
use Tsitsulin\Enum\StringEnum;

/**
 * Unit tests.
 *
 * @covers \Tsitsulin\Enum\Enum, \Tsitsulin\Enum\IntEnum, \Tsitsulin\Enum\StringEnum
 *
 * @author Sergey Tsitsulin <tsitsulin@gmail.com>
 */
final class EnumTest extends Unit
{
    /**
     * @return void
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        array_map(
            static fn (string $class) => (new ReflectionClass($class))
                ->setStaticPropertyValue('instances', []),
            [
                IntEnum::class,
                StringEnum::class,
            ],
        );
    }

    /**
     * Test typing.
     *
     * @return void
     */
    public function testTyping(): void
    {
        $foo = new class {
            /**
             * @param Enum $enum
             *
             * @return int|string
             */
            public function testEnum(Enum $enum)
            {
                return $enum->value;
            }

            public function testStringEnum(TestStringEnum $testStringEnum): string
            {
                return $testStringEnum->value;
            }

            public function testIntEnum(TestIntEnum $testIntEnum): int
            {
                return $testIntEnum->value;
            }
        };

        $this->assertEquals('value1', $foo->testEnum(TestStringEnum::Case1()));
        $this->assertEquals(101, $foo->testEnum(TestIntEnum::Case1()));
        $this->assertEquals('value1', $foo->testStringEnum(TestStringEnum::Case1()));
        $this->assertEquals(101, $foo->testIntEnum(TestIntEnum::Case1()));
    }

    /**
     * Test encapsulation.
     *
     * @return void
     * @throws ReflectionException
     */
    public function testEncapsulation(): void
    {
        // Init enums
        $testStringEnum = TestStringEnum::Case1();
        $testStringEnum2 = TestStringEnum2::Case1();
        $testStringPrivateEnum = TestStringPrivateEnum::Case1();
        $testIntEnum = TestIntEnum::Case1();
        $testIntEnum2 = TestIntEnum2::Case1();
        $testIntPrivateEnum = TestIntPrivateEnum::Case1();

        // There are no instances on base level
        $this->assertEmpty((new ReflectionClass(Enum::class))->getStaticProperties());

        // There is no private enum instance
        $this->assertEquals(
            [
                $testStringEnum,
                $testStringEnum2,
            ],
            $this->getActualInstances(StringEnum::class),
        );
        $this->assertEquals(
            [
                $testIntEnum,
                $testIntEnum2,
            ],
            $this->getActualInstances(IntEnum::class),
        );

        // There is only private enum instance
        $this->assertEquals(
            [$testStringPrivateEnum],
            $this->getActualInstances(TestStringPrivateEnum::class),
        );
        $this->assertEquals(
            [$testIntPrivateEnum],
            $this->getActualInstances(TestIntPrivateEnum::class),
        );
    }

    /**
     * Test equality.
     *
     * @return void
     */
    public function testEquality(): void
    {
        $this->assertSame(TestStringEnum::Case1(), TestStringEnum::Case1());
    }

    /**
     * Test invalid enum.
     *
     * @return void
     */
    public function testInvalidEnum(): void
    {
        $expectedError = InvalidEnumCaseTypeError::class;
        $this->assertError($expectedError, fn () => TestInvalidStringEnum::Case1()->value);
        $this->assertError($expectedError, fn () => TestInvalidIntEnum::Case1()->value);
        $this->assertError($expectedError, fn () => TestInvalidStringEnum::Case1());
        $this->assertError($expectedError, fn () => TestInvalidIntEnum::Case1());
    }

    /**
     * Test access to enum.
     *
     * @return void
     */
    public function testAccess(): void
    {
        $this->assertErrorByMessage(
            'Cannot access protected const',
            /**
             * @phpstan-ignore-next-line
             */
            fn () => TestStringEnum::Case1,
        );
    }

    /**
     * Test access to name and value.
     *
     * @return void
     */
    public function testAccessToNameAndValue(): void
    {
        $this->assertSame(TestStringEnum::Case1()->name, 'Case1');
        $this->assertSame(TestStringEnum::Case2()->value, 'value2');
    }

    /**
     * Test name or value modification prevention.
     *
     * @return void
     */
    public function testNameOrValueCannotBeModified(): void
    {
        $expectedError = EnumCaseCannotBeModifiedError::class;
        /** @phpstan-ignore-next-line */
        $this->assertError($expectedError, fn () => TestStringEnum::Case1()->name = 'newName');
        /** @phpstan-ignore-next-line */
        $this->assertError($expectedError, fn () => TestStringEnum::Case1()->value = 'newValue');
        /** @phpstan-ignore-next-line */
        $this->assertError($expectedError, fn () => TestStringEnum::Case2()->value = 'value2');
        /** @phpstan-ignore-next-line */
        $this->assertError($expectedError, fn () => TestIntEnum::Case1()->name = 'newName');
        /** @phpstan-ignore-next-line */
        $this->assertError($expectedError, fn () => TestIntEnum::Case1()->value = 1);
        /** @phpstan-ignore-next-line */
        $this->assertError($expectedError, fn () => TestIntEnum::Case2()->value = 102);
    }

    /**
     * Test unexpected enum case handling.
     *
     * @return void
     */
    public function testUnexpectedCase(): void
    {
        $expectedError = UnexpectedEnumCaseCallError::class;
        /** @phpstan-ignore-next-line */
        $this->assertError($expectedError, fn () => TestStringEnum::Case1()->invalid);
        /** @phpstan-ignore-next-line */
        $this->assertError($expectedError, fn () => TestIntEnum::Case1()->invalid);
    }

    /**
     * Test external construction prevention.
     *
     * @return void
     */
    public function testCannotBeConstructedExternally(): void
    {
        $this->assertErrorByMessage(
            'Call to private',
            /**
             * @phpstan-ignore-next-line
             */
            fn () => new TestStringEnum(),
        );
    }

    /**
     * Test UnitEnum interface for 'cases'.
     *
     * @return void
     */
    public function testUnitEnumCases(): void
    {
        $this->assertEquals(
            [
                ['Case1' => 'value1'],
                ['Case2' => 'value2'],
            ],
            array_map(
                static function (TestStringEnum $testStringEnum) {
                    return [$testStringEnum->name => $testStringEnum->value];
                },
                TestStringEnum::cases(),
            ),
        );

        $this->assertEquals(
            [
                ['Case1' => 101],
                ['Case2' => 102],
            ],
            array_map(
                static function (TestIntEnum $testIntEnum) {
                    return [$testIntEnum->name => $testIntEnum->value];
                },
                TestIntEnum::cases(),
            ),
        );
    }

    /**
     * Test BackedEnum Interface for 'from'.
     *
     * @return void
     */
    public function testBackedEnumFrom(): void
    {
        $this->assertIsInt(TestIntEnum::from(101)->value);
        $this->assertIsString(TestStringEnum::from('value1')->value);
        $this->assertError(InvalidEnumCaseTypeError::class, fn () => TestIntEnum::from('101'));
        $this->assertError(InvalidEnumCaseTypeError::class, fn () => TestStringEnum::from(666));
        $this->assertError(UnexpectedEnumCaseTypeError::class, fn () => TestIntEnum::from(666));
        $this->assertError(UnexpectedEnumCaseTypeError::class, fn () => TestStringEnum::from('invalidValue'));
        $this->assertEquals(TestStringEnum::Case1()->value, TestStringEnum::from('value1')->value);
        $this->assertEquals(TestIntEnum::Case1()->value, TestIntEnum::from(101)->value);
    }

    /**
     * Test BackedEnum Interface for 'tryFrom'.
     *
     * @return void
     */
    public function testBackedEnumTryFrom(): void
    {
        $this->assertIsInt(TestIntEnum::tryFrom(101)->value ?? null);
        $this->assertIsString(TestStringEnum::tryFrom('value1')->value ?? null);
        $this->assertEquals(null, TestIntEnum::tryFrom('101'));
        $this->assertEquals(null, TestIntEnum::tryFrom(666));
        $this->assertEquals(null, TestStringEnum::tryFrom(666));
        $this->assertEquals(null, TestStringEnum::tryFrom('invalidValue'));
        $this->assertEquals(TestStringEnum::Case1()->value, TestStringEnum::tryFrom('value1')->value ?? null);
        $this->assertEquals(TestIntEnum::Case1()->value, TestIntEnum::tryFrom(101)->value ?? null);
    }

    /**
     * Test polyfill functions.
     *
     * @return void
     * @throws Exception
     */
    public function testFunctions(): void
    {
        $this->assertTrue(enum_exists(TestStringEnum::class));
        $this->assertTrue(enum_exists(TestIntEnum::class));
    }

    /**
     * Test serialization.
     *
     * @return void
     */
    public function testSerialization(): void
    {
        $case1 = TestIntEnum::Case1();
        $serializedCase1 = serialize($case1);
        /**
         * @var IntEnum $deserializedCase1
         */
        $deserializedCase1 = unserialize($serializedCase1);
        $this->assertSame(
            [
                TestIntEnum::Case1(),
                TestIntEnum::Case2(),
            ],
            $deserializedCase1::cases(),
        );
        $this->assertSame($case1->name, $deserializedCase1->name);
        $this->assertSame($case1->value, $deserializedCase1->value);
        $this->assertNotSame($case1, $deserializedCase1);
        $deserializedCase1 = deserialize_enum($serializedCase1);
        $this->assertSame($case1, $deserializedCase1);
    }

    /**
     * Test isset.
     *
     * @return void
     */
    public function testIsset(): void
    {
        $this->assertTrue(isset(TestStringEnum::Case1()->name));
        $this->assertTrue(isset(TestStringEnum::Case1()->value));
    }

    /**
     * Get actual instances.
     *
     * @param class-string<Enum> $className
     *
     * @return Enum[]
     * @throws ReflectionException
     */
    private function getActualInstances(string $className): array
    {
        /** @var Enum[] $instances */
        $instances = (new ReflectionClass($className))
            ->getStaticPropertyValue('instances');

        /** @var Enum[] $actual */
        $actual = array_values(
            array_map(
                /** @phpstan-ignore-next-line */
                static function (array $instances): Enum {
                    /** @var Enum $instance */
                    $instance = array_shift($instances);

                    return $instance;
                },
                $instances,
            )
        );

        return $actual;
    }

    /**
     * Assert an error.
     *
     * @param class-string<Error> $expectedError
     * @param Closure $expression
     *
     * @return void
     */
    private function assertError(string $expectedError, Closure $expression): void
    {
        $e = null;
        try {
            $expression();
        } catch (Throwable $e) {
        }

        $this->assertInstanceOf(
            $expectedError,
            $e,
            sprintf(
                'Expected an instance of %s. Received: %s',
                $expectedError,
                $e instanceof Throwable ? $e->getMessage() : 'Null',
            ),
        );
    }

    /**
     * Assort an error by message.
     *
     * @param string $expectedMessage
     * @param Closure $expression
     *
     * @return void
     */
    private function assertErrorByMessage(string $expectedMessage, Closure $expression): void
    {
        $e = null;
        try {
            $expression();
        } catch (Error $e) {
        }
        $this->assertInstanceOf(Error::class, $e, "Expected an instance of Error.");
        $this->assertNotFalse(
            strpos($e->getMessage(), $expectedMessage),
            "Expected error message '$expectedMessage' but actual is '{$e->getMessage()}'",
        );
    }
}
