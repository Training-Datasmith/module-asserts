<?php

declare (strict_types=1);
namespace Codeception\Module;

use function get_debug_type;
use Throwable;
/**
 * Special module for using asserts in your tests.
 */
class Asserts extends Abstract_Asserts
{
    /**
     * Handles and checks throwables (Exceptions/Errors) called inside the callback function.
     * Either throwable class name or throwable instance should be provided.
     *
     * ```php
     * <?php
     * $I->expectThrowable(MyThrowable::class, function() {
     *     $this->doSomethingBad();
     * });
     *
     * $I->expectThrowable(new MyException(), function() {
     *     $this->doSomethingBad();
     * });
     * ```
     *
     * If you want to check message or throwable code, you can pass them with throwable instance:
     * ```php
     * <?php
     * // will check that throwable MyError is thrown with "Don't do bad things" message
     * $I->expectThrowable(new MyError("Don't do bad things"), function() {
     *     $this->doSomethingBad();
     * });
     * ```
     */
    public function expect_throwable(string|Throwable $throwable, callable $callback): void
    {
        if (is_object($throwable)) {
            $class = $throwable::class;
            $msg = $throwable->get_message();
            $code = (int) $throwable->get_code();
        } else {
            $class = $throwable;
            $msg = null;
            $code = null;
        }
        try {
            $callback();
        } catch (Throwable $t) {
            $this->check_throwable($t, $class, $msg, $code);
            return;
        }
        $this->fail("Expected throwable of class '{$class}' to be thrown, but nothing was caught");
    }
    /**
     * Check if the given throwable matches the expected data,
     * fail (throws an exception) if it does not.
     */
    protected function check_throwable(Throwable $throwable, string $expected_class, ?string $expected_msg, int|null $expected_code = null): void
    {
        if (!$throwable instanceof $expected_class) {
            $this->fail(sprintf("Exception of class '%s' expected to be thrown, but class '%s' was caught", $expected_class, get_debug_type($throwable)));
        }
        if (null !== $expected_msg && $throwable->get_message() !== $expected_msg) {
            $this->fail(sprintf("Exception of class '%s' expected to have message '%s', but actual message was '%s'", $expected_class, $expected_msg, $throwable->get_message()));
        }
        if (null !== $expected_code && $throwable->get_code() !== $expected_code) {
            $this->fail(sprintf("Exception of class '%s' expected to have code '%s', but actual code was '%s'", $expected_class, $expected_code, $throwable->get_code()));
        }
        $this->assert_true(true);
        // increment assertion counter
    }
}