<?php

declare(strict_types=1);

namespace unit\Codeception\Module;

use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Codeception\Module\Asserts;
use Codeception\PHPUnit\TestCase;
use Error;
use Exception;
use PHPUnit\Framework\AssertionFailedError;
use RuntimeException;
use stdClass;
use Support\Data\SqlStatePdoException;
use TypeError;

final class ExpectThrowableTest extends TestCase
{
    protected ?Asserts $module = null;

    public function _setUp(): void
    {
        require_once codecept_data_dir() . '/SqlStatePdoException.php';
        $this->module = new Asserts(new ModuleContainer(new Di(), []));
    }

    public function _tearDown(): void
    {
        $this->module = null;
    }

    public function testClassNameMatchesExactClass(): void
    {
        $this->module->expectThrowable(RuntimeException::class, function (): void {
            throw new RuntimeException('x', 7);
        });
    }

    public function testClassNameMatchesSubclass(): void
    {
        $this->module->expectThrowable(Exception::class, function (): void {
            throw new RuntimeException('sub');
        });
    }

    public function testClassNameRejectsParent(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(
            "Exception of class 'RuntimeException' expected to be thrown, but class 'Exception' was caught"
        );

        $this->module->expectThrowable(RuntimeException::class, function (): void {
            throw new Exception('parent');
        });
    }

    public function testClassNameIgnoresMessageAndCode(): void
    {
        $this->module->expectThrowable(Exception::class, function (): void {
            throw new Exception('other', 9);
        });
    }

    public function testClassNameMatchesInterface(): void
    {
        $this->module->expectThrowable(\Throwable::class, function (): void {
            throw new Exception('iface');
        });
        $this->module->expectThrowable(\Throwable::class, function (): void {
            throw new TypeError('iface error');
        });
    }

    public function testErrorIsCaughtWhenExpected(): void
    {
        $this->module->expectThrowable(Error::class, function (): void {
            throw new TypeError('err');
        });
        $this->module->expectThrowable(TypeError::class, function (): void {
            throw new TypeError('err');
        });
    }

    public function testErrorIsRejectedWhenExceptionExpected(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(
            "Exception of class 'Exception' expected to be thrown, but class 'TypeError' was caught"
        );

        $this->module->expectThrowable(Exception::class, function (): void {
            throw new TypeError('not exception');
        });
    }

    public function testNonThrowableClassNameFailsClosed(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(
            "Exception of class 'stdClass' expected to be thrown, but class 'Exception' was caught"
        );

        $this->module->expectThrowable(stdClass::class, function (): void {
            throw new Exception('nope');
        });
    }

    public function testInstanceMatchesMessageAndIntCode(): void
    {
        $this->module->expectThrowable(new Exception('here', 200), function (): void {
            throw new Exception('here', 200);
        });
    }

    public function testInstanceDefaultCodeZeroIsChecked(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(
            "Exception of class 'Exception' expected to have code '0', but actual code was '5'"
        );

        $this->module->expectThrowable(new Exception('m'), function (): void {
            throw new Exception('m', 5);
        });
    }

    public function testInstanceEmptyMessageIsChecked(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(
            "Exception of class 'Exception' expected to have message '', but actual message was 'x'"
        );

        $this->module->expectThrowable(new Exception('', 1), function (): void {
            throw new Exception('x', 1);
        });
    }

    public function testMessageMismatchReportsMessage(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(
            "Exception of class 'Exception' expected to have message 'foo', but actual message was 'bar'"
        );

        $this->module->expectThrowable(new Exception('foo', 1), function (): void {
            throw new Exception('bar', 1);
        });
    }

    public function testCodeMismatchReportsCode(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(
            "Exception of class 'Exception' expected to have code '200', but actual code was '2'"
        );

        $this->module->expectThrowable(new Exception('here', 200), function (): void {
            throw new Exception('here', 2);
        });
    }

    public function testNothingThrownReportsClass(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(
            "Expected throwable of class 'RuntimeException' to be thrown, but nothing was caught"
        );

        $this->module->expectThrowable(RuntimeException::class, function (): void {
        });
    }

    public function testCallbackRunsAndMatchDoesNotRethrow(): void
    {
        $ran = false;
        $this->module->expectThrowable(RuntimeException::class, function () use (&$ran): void {
            $ran = true;
            throw new RuntimeException('ok');
        });
        $this->assertTrue($ran);
        $this->assertTrue(true);
    }

    public function testSuccessRecordsAssertion(): void
    {
        $module = new class(new ModuleContainer(new Di(), [])) extends Asserts {
            public int $assertTrueCalls = 0;

            public function assertTrue($condition, string $message = ''): void
            {
                if ($condition === true) {
                    $this->assertTrueCalls++;
                }
                parent::assertTrue($condition, $message);
            }
        };

        $module->expectThrowable(Exception::class, function (): void {
            throw new Exception();
        });

        $this->assertSame(1, $module->assertTrueCalls);
    }

    public function testStringCodeMatches(): void
    {
        $expected = new SqlStatePdoException('table missing', '42S02');
        $this->assertIsString($expected->getCode());
        $this->assertSame('42S02', $expected->getCode());

        $this->module->expectThrowable($expected, function (): void {
            throw new SqlStatePdoException('table missing', '42S02');
        });
    }

    public function testStringCodeMismatchReportsRawCodes(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(
            "Exception of class '" . SqlStatePdoException::class . "' expected to have code '42S02', but actual code was 'HY000'"
        );

        $this->module->expectThrowable(
            new SqlStatePdoException('msg', '42S02'),
            function (): void {
                throw new SqlStatePdoException('msg', 'HY000');
            }
        );
    }
}
