<?php

namespace HexagonalPlayground\Application\Command;


class DeleteTeamCommand implements CommandInterface
{
    /** @var string */
    private $teamId;

    public function __construct(string $teamId)
    {
        $this->teamId = $teamId;
    }

    /**
     * @return string
     */
    public function getTeamId(): string
    {
        return $this->teamId;
    }
}