<?php

declare(strict_types=1);

namespace unit\Codeception\Module;

use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Codeception\Module\Asserts;
use Codeception\PHPUnit\TestCase;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Constraint\IsEqual;

final class CodeceptionAliasTest extends TestCase
{
    protected ?Asserts $module = null;

    public function _setUp(): void
    {
        $this->module = new Asserts(new ModuleContainer(new Di(), []));
    }

    public function _tearDown(): void
    {
        $this->module = null;
    }

    public function testAssertGreaterOrEquals(): void
    {
        $this->module->assertGreaterOrEquals(2, 2);
        $this->module->assertGreaterOrEquals(2, 3);

        $this->expectException(AssertionFailedError::class);
        $this->module->assertGreaterOrEquals(3, 2);
    }

    public function testAssertLessOrEquals(): void
    {
        $this->module->assertLessOrEquals(2, 2);
        $this->module->assertLessOrEquals(2, 1);

        $this->expectException(AssertionFailedError::class);
        $this->module->assertLessOrEquals(1, 2);
    }

    public function testAssertIsEmpty(): void
    {
        $this->module->assertIsEmpty([]);

        $this->expectException(AssertionFailedError::class);
        $this->module->assertIsEmpty([1]);
    }

    public function testAssertRegExp(): void
    {
        $this->module->assertRegExp('/^[0-9]$/', '1');

        $this->expectException(AssertionFailedError::class);
        $this->module->assertRegExp('/^[0-9]$/', 'a');
    }

    public function testAssertNotRegExp(): void
    {
        $this->module->assertNotRegExp('/^[0-9]$/', 'a');

        $this->expectException(AssertionFailedError::class);
        $this->module->assertNotRegExp('/^[0-9]$/', '1');
    }

    public function testAssertThatItsNot(): void
    {
        $this->module->assertThatItsNot(3, new IsEqual(4));

        $this->expectException(AssertionFailedError::class);
        $this->module->assertThatItsNot(4, new IsEqual(4));
    }

    public function testAssertFileNotExists(): void
    {
        $path = sys_get_temp_dir() . '/codeception-module-asserts-missing-' . uniqid('', true);
        $this->assertIsString($path);
        $this->assertFalse(file_exists($path));

        $this->module->assertFileNotExists($path);

        $this->assertTrue(file_exists(__FILE__));
        $this->expectException(AssertionFailedError::class);
        $this->module->assertFileNotExists(__FILE__);
    }
}
