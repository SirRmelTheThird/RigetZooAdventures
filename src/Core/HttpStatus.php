<?php

declare(strict_types=1);

namespace Core;

enum HttpStatus: int
{
    case Ok = 200;
    case NoContent = 204;
    case Found = 302;
    case BadRequest = 400;
    case Unauthorized = 401;
    case PaymentRequired = 402;
    case Forbidden = 403;
    case NotFound = 404;
    case Unprocessable = 422;
    case InternalServerError = 500;
}
