# Architecture: module-asserts (Codeception)

## Purpose

A Codeception module that exposes PHPUnit assertion methods directly in Codeception tests. Allows `$I->assertEquals(...)`, `$I->assertNotEmpty(...)`, etc. without wrapping them in unit test classes.

## Directory Structure

```
src/Codeception/Module/
  Abstract_Asserts.php   — Reflection-based bridge: delegates $I->assertXxx() to PHPUnit's Assert class
  Asserts.php            — Concrete module: registers itself with Codeception, exposes all assertions
tests/
  unit/                  — Unit tests for the module itself
```

## Key Design Decisions

- **Reflection delegation**: `Abstract_Asserts` uses `__call()` to forward unknown method calls to `PHPUnit\Framework\Assert::staticMethodName()`, meaning all current and future PHPUnit assertions are automatically available without maintenance
- **No assertion duplication**: The module does not reimplement assertions; it is purely a bridge

## Extension Points

- Extend `Asserts` to add custom domain-specific assertions alongside the PHPUnit ones
