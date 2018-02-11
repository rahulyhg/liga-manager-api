<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

class EndSeasonCommand implements CommandInterface
{
    /** @var string */
    private $seasonId;

    public function __construct(string $seasonId)
    {
        $this->seasonId = $seasonId;
    }

    public function getSeasonId() : string
    {
        return $this->seasonId;
    }
}