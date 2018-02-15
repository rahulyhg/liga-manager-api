<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Command\AddTeamToSeasonCommand;
use HexagonalPlayground\Application\Command\CreateMatchesForSeasonCommand;
use HexagonalPlayground\Application\Command\DeleteSeasonCommand;
use HexagonalPlayground\Application\Command\RemoveTeamFromSeasonCommand;
use HexagonalPlayground\Application\Command\StartSeasonCommand;
use HexagonalPlayground\Application\Exception\InvalidStateException;
use Slim\Http\Response;

class SeasonCommandController extends CommandController
{
    /**
     * @param string $seasonId
     * @return Response
     */
    public function createMatches(string $seasonId) : Response
    {
        try {
            $this->commandBus->execute(new CreateMatchesForSeasonCommand($seasonId));
        } catch (InvalidStateException $e) {
            return $this->createBadRequestResponse($e->getMessage());
        }
        return new Response(204);
    }

    /**
     * @param string $seasonId
     * @return Response
     */
    public function start(string $seasonId) : Response
    {
        try {
            $this->commandBus->execute(new StartSeasonCommand($seasonId));
        } catch (InvalidStateException $e) {
            return $this->createBadRequestResponse($e->getMessage());
        }
        return new Response(204);
    }

    /**
     * @param string $seasonId
     * @return Response
     */
    public function delete(string $seasonId) : Response
    {
        try {
            $this->commandBus->execute(new DeleteSeasonCommand($seasonId));
        } catch (InvalidStateException $e) {
            return $this->createBadRequestResponse($e->getMessage());
        }
        return new Response(204);
    }

    /**
     * @param string $seasonId
     * @param string $teamId
     * @return Response
     */
    public function addTeam(string $seasonId, string $teamId) : Response
    {
        try {
            $this->commandBus->execute(new AddTeamToSeasonCommand($seasonId, $teamId));
        } catch (InvalidStateException $e) {
            return $this->createBadRequestResponse($e->getMessage());
        }
        return new Response(204);
    }

    /**
     * @param string $seasonId
     * @param string $teamId
     * @return Response
     */
    public function removeTeam(string $seasonId, string $teamId) : Response
    {
        try {
            $this->commandBus->execute(new RemoveTeamFromSeasonCommand($seasonId, $teamId));
        } catch (InvalidStateException $e) {
            return $this->createBadRequestResponse($e->getMessage());
        }
        return new Response(204);
    }
}
