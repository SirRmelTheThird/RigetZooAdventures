<?php

declare(strict_types=1);

namespace Core\Validation;

use LogicException;

final class InvalidRuleException extends LogicException
{
    public static function unknown(string $name): self
    {
        return new self("Unknown validation rule: {$name}");
    }

    public static function missingParameter(Rule $rule): self
    {
        return new self("Rule '{$rule->value}' requires a parameter");
    }

    public static function unexpectedParameter(Rule $rule): self
    {
        return new self("Rule '{$rule->value}' does not accept a parameter");
    }

    public static function invalidParameter(Rule $rule, string $parameter): self
    {
        return new self("Rule '{$rule->value}' requires an integer parameter, got '{$parameter}'");
    }
}
