<?php declare(strict_types=1);

use Codeception\Test\Unit;
use Tsitsulin\Enum\Enum;
use Tsitsulin\Enum\Errors\EnumCaseCannotBeModifiedError;
use Tsitsulin\Enum\Errors\InvalidEnumCaseTypeError;
use Tsitsulin\Enum\Errors\UnexpectedEnumCaseCallError;
use Tsitsulin\Enum\Errors\UnexpectedEnumCaseTypeError;
use Tsitsulin\Enum\IntEnum;
use Tsitsulin\Enum\StringEnum;
use Tsitsulin\Enum\Tests\TestIntEnum;
use Tsitsulin\Enum\Tests\TestIntEnum2;
use Tsitsulin\Enum\Tests\TestIntPrivateEnum;
use Tsitsulin\Enum\Tests\TestInvalidIntEnum;
use Tsitsulin\Enum\Tests\TestInvalidStringEnum;
use Tsitsulin\Enum\Tests\TestStringEnum;
use Tsitsulin\Enum\Tests\TestStringEnum2;
use Tsitsulin\Enum\Tests\TestStringPrivateEnum;

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
     * Test typing.
     */
    public function testTyping()
    {
        $foo = new class {
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
     * @throws ReflectionException
     */
    public function testEncapsulation()
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
        $this->assertEquals([
            $testStringEnum,
            $testStringEnum2,
        ], $this->getActualInstances(StringEnum::class));
        $this->assertEquals([
            $testIntEnum,
            $testIntEnum2,
        ], $this->getActualInstances(IntEnum::class));
        // There is only private enum instance
        $this->assertEquals([
            $testStringPrivateEnum,
        ], $this->getActualInstances(TestStringPrivateEnum::class));
        $this->assertEquals([
            $testIntPrivateEnum,
        ], $this->getActualInstances(TestIntPrivateEnum::class));
    }

    /**
     * Test equality.
     */
    public function testEquality()
    {
        $this->assertTrue(TestStringEnum::Case1() === TestStringEnum::Case1());
    }

    /**
     * Test invalid enum.
     */
    public function testInvalidEnum()
    {
        $expectedError = InvalidEnumCaseTypeError::class;
        $this->assertError($expectedError, fn() => TestInvalidStringEnum::Case1()->value);
        $this->assertError($expectedError, fn() => TestInvalidIntEnum::Case1()->value);
        $this->assertError($expectedError, fn() => TestInvalidStringEnum::Case1());
        $this->assertError($expectedError, fn() => TestInvalidIntEnum::Case1());
    }

    /**
     * Test access to enum.
     */
    public function testAccess()
    {
        $this->assertErrorByMessage(
            'Cannot access protected const',
            fn() => TestStringEnum::Case1,
        );
    }

    /**
     * Test access to name and value.
     */
    public function testAccessToNameAndValue()
    {
        $this->assertTrue(TestStringEnum::Case1()->name === 'Case1');
        $this->assertTrue(TestStringEnum::Case2()->value === 'value2');
    }

    /**
     * Test name or value modification prevention.
     */
    public function testNameOrValueCannotBeModified()
    {
        $expectedError = EnumCaseCannotBeModifiedError::class;
        $this->assertError($expectedError, fn() => TestStringEnum::Case1()->name = 'newName');
        $this->assertError($expectedError, fn() => TestStringEnum::Case1()->value = 'newValue');
        $this->assertError($expectedError, fn() => TestStringEnum::Case2()->value = 'value2');
        $this->assertError($expectedError, fn() => TestIntEnum::Case1()->name = 'newName');
        $this->assertError($expectedError, fn() => TestIntEnum::Case1()->value = 1);
        $this->assertError($expectedError, fn() => TestIntEnum::Case2()->value = 102);
    }

    /**
     * Test unexpected enum case handling.
     */
    public function testUnexpectedCase()
    {
        $expectedError = UnexpectedEnumCaseCallError::class;
        $this->assertError($expectedError, fn() => TestStringEnum::Case1()->invalid);
        $this->assertError($expectedError, fn() => TestIntEnum::Case1()->invalid);
    }

    /**
     * Test external construction prevention.
     */
    public function testCannotBeConstructedExternally()
    {
        $this->assertErrorByMessage(
            'Call to private',
            fn() => new TestStringEnum(),
        );
    }

    /**
     * Test UnitEnum interface for 'cases'.
     */
    public function testUnitEnumCases()
    {
        $this->assertEquals([
            ['Case1' => 'value1'],
            ['Case2' => 'value2'],
        ], array_map(function (TestStringEnum $testStringEnum) {
            return [$testStringEnum->name => $testStringEnum->value];
        }, TestStringEnum::cases()));

        $this->assertEquals([
            ['Case1' => 101],
            ['Case2' => 102],
        ], array_map(function (TestIntEnum $testIntEnum) {
            return [$testIntEnum->name => $testIntEnum->value];
        }, TestIntEnum::cases()));
    }

    /**
     * Test BackedEnum Interface for 'from'.
     */
    public function testBackedEnumFrom()
    {
        $expectedError = UnexpectedEnumCaseTypeError::class;
        $this->assertError($expectedError, fn() => TestStringEnum::from(101));
        $this->assertError($expectedError, fn() => TestStringEnum::from('invalidValue'));
        $this->assertError($expectedError, fn() => TestIntEnum::from('101'));
        $this->assertError($expectedError, fn() => TestIntEnum::from(1));
        $this->assertEquals(TestStringEnum::Case1()->value, TestStringEnum::from('value1')->value);
        $this->assertEquals(TestIntEnum::Case1()->value, TestIntEnum::from(101)->value);
    }

    /**
     * Test BackedEnum Interface for 'tryFrom'.
     */
    public function testBackedEnumTryFrom()
    {
        $this->assertEquals(null, TestStringEnum::tryFrom(101));
        $this->assertEquals(null, TestStringEnum::tryFrom('invalidValue'));
        $this->assertEquals(null, TestIntEnum::tryFrom('101'));
        $this->assertEquals(null, TestIntEnum::tryFrom(1));
        $this->assertEquals(TestStringEnum::Case1()->value, TestStringEnum::tryFrom('value1')->value);
        $this->assertEquals(TestIntEnum::Case1()->value, TestIntEnum::tryFrom(101)->value);
    }

    /**
     * Test polyfill functions.
     */
    public function testFunctions()
    {
        $this->assertTrue(enum_exists(TestStringEnum::class));
        $this->assertTrue(enum_exists(TestIntEnum::class));
    }

    /**
     * Get actual instances.
     *
     * @param string<Enum> $className
     * @return Enum[]
     * @throws ReflectionException
     */
    private function getActualInstances(string $className): array
    {
        return array_values(array_map(function (array $item) {
            return array_shift($item);
        },
            (new ReflectionClass($className))
                ->getStaticPropertyValue('instances')
        ));
    }

    /**
     * Assert an error.
     *
     * @param string $expectedError
     * @param Closure $expression
     */
    private function assertError(string $expectedError, Closure $expression)
    {
        $e = null;
        try {
            $expression();
        } catch (Throwable $e) {
        }
        $this->assertTrue(
            $e instanceof $expectedError,
            sprintf(
                'Expected an instance of %s. Received: %s',
                $expectedError,
                $e instanceof Throwable ? $e->getMessage() : 'Null'
            )
        );
    }

    /**
     * Assort an error by message.
     *
     * @param string $expectedMessage
     * @param Closure $expression
     */
    private function assertErrorByMessage(string $expectedMessage, Closure $expression)
    {
        $e = null;
        try {
            $expression();
        } catch (Error $e) {
        }
        $this->assertTrue($e instanceof Error, "Expected an instance of Error.");
        $this->assertTrue(strpos($e->getMessage(), $expectedMessage) !== false, "Expected error message '$expectedMessage' but actual is '{$e->getMessage()}'");
    }
}