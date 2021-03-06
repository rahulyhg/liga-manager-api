<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Util;

use Ramsey\Uuid\Uuid as RamseyUuid;

class Uuid
{
    private function __construct()
    {
        // Cannot be instantiated - static methods only
    }

    public static function create()
    {
        return RamseyUuid::uuid4()->toString();
    }
}