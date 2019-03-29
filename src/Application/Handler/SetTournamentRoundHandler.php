<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\SetTournamentRoundCommand;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\Repository\TournamentRepositoryInterface;
use HexagonalPlayground\Domain\Match;
use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Domain\Tournament;
use HexagonalPlayground\Domain\Util\Assert;

class SetTournamentRoundHandler
{
    /** @var TournamentRepositoryInterface */
    private $tournamentRepository;

    /** @var TeamRepositoryInterface */
    private $teamRepository;

    /**
     * @param TournamentRepositoryInterface $tournamentRepository
     * @param TeamRepositoryInterface $teamRepository
     */
    public function __construct(TournamentRepositoryInterface $tournamentRepository, TeamRepositoryInterface $teamRepository)
    {
        $this->tournamentRepository = $tournamentRepository;
        $this->teamRepository = $teamRepository;
    }

    /**
     * @param SetTournamentRoundCommand $command
     */
    public function __invoke(SetTournamentRoundCommand $command)
    {
        IsAdmin::check($command->getAuthenticatedUser());
        Assert::false(empty($command->getTeamIdPairs()), 'Team pairs cannot be empty');
        Assert::false(count($command->getTeamIdPairs()) > 64, 'Request exceeds maximum amount of 64 team pairs');

        /** @var Tournament $tournament */
        $tournament = $this->tournamentRepository->find($command->getTournamentId());
        $tournament->clearMatchesForRound($command->getRound());
        $tournament->removeMatchDay($command->getRound());
        $round = $tournament->createMatchDay($command->getRound(), $command->getDatePeriod()->getStartDate(), $command->getDatePeriod()->getEndDate());
        foreach ($command->getTeamIdPairs() as $pair) {
            /** @var Team $homeTeam */
            $homeTeam = $this->teamRepository->find($pair->getHomeTeamId());
            /** @var Team $guestTeam */
            $guestTeam = $this->teamRepository->find($pair->getGuestTeamId());

            $round->addMatch(new Match($round, $homeTeam, $guestTeam));
        }
        $this->tournamentRepository->save($tournament);
    }
}