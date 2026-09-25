<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use Core\Validation\InvalidRuleException;
use Core\Validation\Validator;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    private Validator $validator;

    protected function setUp(): void
    {
        $this->validator = new Validator();
    }

    public function testRequiredFailsOnMissingBlankAndWhitespace(): void
    {
        $result = $this->validator->validate(
            ['b' => '', 'c' => '  '],
            ['a' => ['required'], 'b' => ['required'], 'c' => ['required']]
        );

        self::assertSame(['a', 'b', 'c'], array_keys($result->errors()));
    }

    public function testNonRequiredRulesAreSkippedForBlankOptionalFields(): void
    {
        $result = $this->validator->validate(
            ['e' => ''],
            ['e' => ['email'], 'x' => ['integer', 'min:1']]
        );

        self::assertTrue($result->passes());
    }

    public function testFirstFailingRuleWinsAndMessageUsesReadableLabel(): void
    {
        $result = $this->validator->validate(
            ['first_name' => ''],
            ['first_name' => ['required', 'minLength:3']]
        );

        self::assertSame('First name is required.', $result->errors()['first_name']);
    }

    public function testArrayInputCannotSlipThroughRequiredOrEmail(): void
    {
        $result = $this->validator->validate(
            ['a' => ['x'], 'e' => ['x']],
            ['a' => ['required'], 'e' => ['email']]
        );

        self::assertSame(['a', 'e'], array_keys($result->errors()));
    }

    public function testIntegerMinMaxAndMultibyteMinLength(): void
    {
        $rules = ['n' => ['integer', 'min:1', 'max:5'], 's' => ['minLength:3']];

        self::assertTrue($this->validator->validate(['n' => '3', 's' => 'éàü'], $rules)->passes());
        self::assertSame(['n'], array_keys($this->validator->validate(['n' => '0'], $rules)->errors()));
        self::assertSame(['n'], array_keys($this->validator->validate(['n' => '6'], $rules)->errors()));
        self::assertSame(['n'], array_keys($this->validator->validate(['n' => '1.5'], $rules)->errors()));
        self::assertSame(['s'], array_keys($this->validator->validate(['s' => 'éà'], $rules)->errors()));
    }

    public function testDateAndFutureDate(): void
    {
        $rules = ['d' => ['date', 'futureDate']];
        $today = (new DateTimeImmutable('today'))->format('Y-m-d');
        $yesterday = (new DateTimeImmutable('yesterday'))->format('Y-m-d');

        self::assertTrue($this->validator->validate(['d' => $today], $rules)->passes());
        self::assertFalse($this->validator->validate(['d' => $yesterday], $rules)->passes());
        self::assertFalse($this->validator->validate(['d' => '2030-02-31'], $rules)->passes());
        self::assertFalse($this->validator->validate(['d' => 'tomorrow'], $rules)->passes());
    }

    /**
     * @dataProvider malformedRuleProvider
     */
    public function testMalformedRuleStringsFailLoudly(array $rules): void
    {
        $this->expectException(InvalidRuleException::class);
        $this->validator->validate(['a' => 'x'], $rules);
    }

    public static function malformedRuleProvider(): array
    {
        return [
            'unknown rule' => [['a' => ['nope']]],
            'missing parameter' => [['a' => ['min']]],
            'parameter on a parameterless rule' => [['a' => ['required:3']]],
            'non-numeric parameter' => [['a' => ['min:abc']]],
        ];
    }
}
