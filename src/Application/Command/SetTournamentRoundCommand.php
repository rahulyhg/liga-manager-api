<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Application\TypeAssert;
use HexagonalPlayground\Application\Value\DatePeriod;
use HexagonalPlayground\Application\Value\TeamIdPair;

class SetTournamentRoundCommand implements CommandInterface
{
    use AuthenticationAware;

    /** @var string */
    private $tournamentId;

    /** @var int */
    private $round;

    /** @var TeamIdPair[] */
    private $teamIdPairs;

    /** @var DatePeriod */
    private $datePeriod;

    /**
     * @param string $tournamentId
     * @param int $round
     * @param TeamIdPair[] $teamIdPairs
     * @param DatePeriod $datePeriod
     */
    public function __construct($tournamentId, $round, array $teamIdPairs, DatePeriod $datePeriod)
    {
        TypeAssert::assertString($tournamentId, 'tournamentId');
        TypeAssert::assertInteger($round, 'round');
        TypeAssert::assertArray($teamIdPairs, 'teamIdPairs');

        $this->tournamentId = $tournamentId;
        $this->round        = $round;
        $this->datePeriod   = $datePeriod;
        $this->teamIdPairs  = array_map(function (TeamIdPair $idPair) {
            return $idPair;
        }, $teamIdPairs);
    }

    /**
     * @return TeamIdPair[]
     */
    public function getTeamIdPairs(): array
    {
        return $this->teamIdPairs;
    }

    /**
     * @return string
     */
    public function getTournamentId(): string
    {
        return $this->tournamentId;
    }

    /**
     * @return int
     */
    public function getRound(): int
    {
        return $this->round;
    }

    /**
     * @return DatePeriod
     */
    public function getDatePeriod(): DatePeriod
    {
        return $this->datePeriod;
    }
}