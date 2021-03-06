<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\Bus\CommandBus;
use HexagonalPlayground\Application\Command\AddTeamToSeasonCommand;
use HexagonalPlayground\Application\Command\CreateMatchesForSeasonCommand;
use HexagonalPlayground\Application\Command\CreatePitchCommand;
use HexagonalPlayground\Application\Command\CreateSeasonCommand;
use HexagonalPlayground\Application\Command\CreateTeamCommand;
use HexagonalPlayground\Application\Command\CreateTournamentCommand;
use HexagonalPlayground\Application\Command\CreateUserCommand;
use HexagonalPlayground\Application\Command\SetTournamentRoundCommand;
use HexagonalPlayground\Application\Command\StartSeasonCommand;
use HexagonalPlayground\Application\Value\DatePeriod;
use HexagonalPlayground\Application\Value\TeamIdPair;
use HexagonalPlayground\Domain\User;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LoadFixturesCommand extends Command
{
    /** @var CommandBus */
    private $commandBus;

    /**
     * @param CommandBus $commandBus
     */
    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('app:load-fixtures');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->createAdmin();
        $seasonIds = $this->createSeasons();
        $teamIds   = $this->createTeams();
        $this->createTeamManagers($teamIds);
        $this->createPitches();
        $this->linkTeamsWithSeasons($teamIds, $seasonIds);
        $this->startSeason(array_shift($seasonIds), count($teamIds));
        $tournamentIds = $this->createTournaments();
        $this->createTournamentRounds($tournamentIds, $teamIds);
        $output->writeln('Fixtures successfully loaded');
        return 0;
    }

    private function createTournaments(): array
    {
        $ids = [];
        foreach (['A', 'B', 'C'] as $char) {
            $command = new CreateTournamentCommand(null, 'Tournament ' . $char);
            $this->commandBus->execute($command->withAuthenticatedUser($this->getCliUser()));
            $ids[] = $command->getId();
        }

        return $ids;
    }

    private function createTournamentRounds(array $tournamentIds, array $teamIds)
    {
        foreach ($tournamentIds as $tournamentId) {
            $pairs = [];
            foreach (array_chunk($teamIds, 2) as $chunk) {
                if (count($chunk) === 2) {
                    $pairs[] = new TeamIdPair($chunk[0], $chunk[1]);
                }
            }
            $start   = new \DateTimeImmutable('next saturday');
            $period  = new DatePeriod($start, $start->modify('next sunday'));
            $command = new SetTournamentRoundCommand($tournamentId, 1, $pairs, $period);
            $this->commandBus->execute($command->withAuthenticatedUser($this->getCliUser()));
        }
    }

    private function createSeasons(): array
    {
        $years = ['17/18', '18/19', '19/20'];
        $ids   = [];
        foreach ($years as $year) {
            $command = new CreateSeasonCommand(null, 'Season ' . $year);
            $this->commandBus->execute($command->withAuthenticatedUser($this->getCliUser()));
            $ids[] = $command->getId();
        }

        return $ids;
    }

    private function createPitches(): void
    {
        $colors = ['Red', 'Blue'];
        foreach ($colors as $color) {
            $command = new CreatePitchCommand(null, 'Pitch ' . $color, 12.34, 23.45);
            $this->commandBus->execute($command->withAuthenticatedUser($this->getCliUser()));
        }
    }

    private function createTeams(): array
    {
        $ids = [];
        for ($i = 1; $i <= 8; $i++) {
            $teamName = sprintf('Team No. %02d', $i);
            $command  = new CreateTeamCommand(null, $teamName);
            $this->commandBus->execute($command->withAuthenticatedUser($this->getCliUser()));
            $ids[] = $command->getId();
        }

        return $ids;
    }

    private function createAdmin(): void
    {
        $command = new CreateUserCommand(
            null,
            'admin@example.com',
            '123456',
            'admin',
            'admin',
            User::ROLE_ADMIN,
            []
        );
        $this->commandBus->execute($command->withAuthenticatedUser($this->getCliUser()));
    }

    private function createTeamManagers(array $teamIds): void
    {
        for ($i = 1; $i <= 8; $i++) {
            $command = new CreateUserCommand(
                null,
                'user' . $i . "@example.com",
                '123456',
                'user' . $i,
                'admin' . $i,
                User::ROLE_TEAM_MANAGER,
                [array_shift($teamIds)]
            );

            $this->commandBus->execute($command->withAuthenticatedUser($this->getCliUser()));
        }
    }

    private function linkTeamsWithSeasons(array $teamIds, array $seasonIds): void
    {
        foreach ($seasonIds as $seasonId) {
            foreach ($teamIds as $teamId) {
                $command = new AddTeamToSeasonCommand($seasonId, $teamId);
                $this->commandBus->execute($command->withAuthenticatedUser($this->getCliUser()));
            }
        }
    }

    private function startSeason(string $seasonId, int $teamCount): void
    {
        $command = new CreateMatchesForSeasonCommand($seasonId, $this->generateMatchDayDates($teamCount));
        $this->commandBus->execute($command->withAuthenticatedUser($this->getCliUser()));
        $command = new StartSeasonCommand($seasonId);
        $this->commandBus->execute($command->withAuthenticatedUser($this->getCliUser()));
    }

    private function generateMatchDayDates(int $teamCount): array
    {
        $start = new \DateTimeImmutable('next saturday');
        $end   = $start->modify('next sunday');
        $result = [];
        $n = $teamCount - 1;
        for ($i = 0; $i < $n; $i++) {
            $start = $start->modify('+7 days');
            $end   = $end->modify('+7 days');
            $result[] = new DatePeriod($start, $end);
        }

        return $result;
    }
}
