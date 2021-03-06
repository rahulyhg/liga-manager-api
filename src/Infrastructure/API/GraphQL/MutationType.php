<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use HexagonalPlayground\Application\Command\AddRankingPenaltyCommand;
use HexagonalPlayground\Application\Command\AddTeamToSeasonCommand;
use HexagonalPlayground\Application\Command\CancelMatchCommand;
use HexagonalPlayground\Application\Command\ChangeUserPasswordCommand;
use HexagonalPlayground\Application\Command\CreateMatchesForSeasonCommand;
use HexagonalPlayground\Application\Command\CreatePitchCommand;
use HexagonalPlayground\Application\Command\CreateSeasonCommand;
use HexagonalPlayground\Application\Command\CreateTeamCommand;
use HexagonalPlayground\Application\Command\CreateTournamentCommand;
use HexagonalPlayground\Application\Command\CreateUserCommand;
use HexagonalPlayground\Application\Command\DeletePitchCommand;
use HexagonalPlayground\Application\Command\DeleteSeasonCommand;
use HexagonalPlayground\Application\Command\DeleteTeamCommand;
use HexagonalPlayground\Application\Command\DeleteTournamentCommand;
use HexagonalPlayground\Application\Command\DeleteUserCommand;
use HexagonalPlayground\Application\Command\EndSeasonCommand;
use HexagonalPlayground\Application\Command\LocateMatchCommand;
use HexagonalPlayground\Application\Command\RemoveRankingPenaltyCommand;
use HexagonalPlayground\Application\Command\RemoveTeamFromSeasonCommand;
use HexagonalPlayground\Application\Command\RenameTeamCommand;
use HexagonalPlayground\Application\Command\RescheduleMatchDayCommand;
use HexagonalPlayground\Application\Command\ScheduleMatchCommand;
use HexagonalPlayground\Application\Command\SendPasswordResetMailCommand;
use HexagonalPlayground\Application\Command\SetTournamentRoundCommand;
use HexagonalPlayground\Application\Command\StartSeasonCommand;
use HexagonalPlayground\Application\Command\SubmitMatchResultCommand;
use HexagonalPlayground\Application\Command\UpdatePitchContactCommand;
use HexagonalPlayground\Application\Command\UpdateTeamContactCommand;
use HexagonalPlayground\Application\Command\UpdateUserCommand;

class MutationType extends ObjectType
{
    use SingletonTrait;

    public function __construct ()
    {
        $mapper = new MutationMapper();
        $config = [
            'fields' => array_reduce($this->getSupportedCommands(), function(array $merged, string $command) use ($mapper) {
                $merged += $mapper->getDefinition($command);
                return $merged;
            }, [])
        ];
        parent::__construct($config);
    }

    private function getSupportedCommands(): array
    {
        return [
            AddRankingPenaltyCommand::class,
            AddTeamToSeasonCommand::class,
            CancelMatchCommand::class,
            ChangeUserPasswordCommand::class,
            CreateMatchesForSeasonCommand::class,
            CreatePitchCommand::class,
            CreateSeasonCommand::class,
            CreateTeamCommand::class,
            CreateTournamentCommand::class,
            CreateUserCommand::class,
            DeletePitchCommand::class,
            DeleteSeasonCommand::class,
            DeleteTeamCommand::class,
            DeleteTournamentCommand::class,
            DeleteUserCommand::class,
            EndSeasonCommand::class,
            LocateMatchCommand::class,
            RemoveRankingPenaltyCommand::class,
            RemoveTeamFromSeasonCommand::class,
            RenameTeamCommand::class,
            RescheduleMatchDayCommand::class,
            ScheduleMatchCommand::class,
            SendPasswordResetMailCommand::class,
            SetTournamentRoundCommand::class,
            StartSeasonCommand::class,
            SubmitMatchResultCommand::class,
            UpdatePitchContactCommand::class,
            UpdateTeamContactCommand::class,
            UpdateUserCommand::class
        ];
    }
}