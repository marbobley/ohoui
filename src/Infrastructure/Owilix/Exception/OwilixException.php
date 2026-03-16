<?php

declare(strict_types=1);

namespace App\Infrastructure\Owilix\Exception;

use App\Exception\AppExceptionInterface;
use Exception;

class OwilixException extends Exception implements AppExceptionInterface {}
