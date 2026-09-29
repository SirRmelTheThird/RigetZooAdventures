<?php

declare(strict_types=1);

namespace Exceptions\Http;

use Exceptions\System\UserFacingException;
use Core\Http\HttpStatus;

final class NotFoundException extends UserFacingException
{
    public function __construct(string $message)
    {
        parent::__construct($message, HttpStatus::NotFound);
    }
}
