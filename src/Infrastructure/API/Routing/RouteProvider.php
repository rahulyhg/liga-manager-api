<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Routing;

use HexagonalPlayground\Infrastructure\API\Controller\MatchCommandController;
use HexagonalPlayground\Infrastructure\API\Controller\MatchQueryController;
use HexagonalPlayground\Infrastructure\API\Controller\PitchCommandController;
use HexagonalPlayground\Infrastructure\API\Controller\PitchQueryController;
use HexagonalPlayground\Infrastructure\API\Controller\SeasonCommandController;
use HexagonalPlayground\Infrastructure\API\Controller\SeasonQueryController;
use HexagonalPlayground\Infrastructure\API\Controller\TeamCommandController;
use HexagonalPlayground\Infrastructure\API\Controller\TeamQueryController;
use HexagonalPlayground\Infrastructure\API\Controller\TournamentCommandController;
use HexagonalPlayground\Infrastructure\API\Controller\TournamentQueryController;
use HexagonalPlayground\Infrastructure\API\Controller\UserCommandController;
use HexagonalPlayground\Infrastructure\API\Controller\UserQueryController;
use HexagonalPlayground\Infrastructure\API\Security\AuthenticationMiddleware;
use HexagonalPlayground\Infrastructure\API\Security\JsonSchemaValidator;
use Slim\App;

class RouteProvider
{
    public function registerRoutes(App $app)
    {
        $app->group('/api', function() use ($app) {
            $container = $app->getContainer();
            $anyAuth   = new AuthenticationMiddleware($container);
            $basicAuth = new AuthenticationMiddleware($container, true);
            $validator = new JsonSchemaValidator(getenv('APP_HOME') . '/public/swagger.json');

            $app->get('/team', function () use ($container) {
                /** @var TeamQueryController $controller */
                $controller = $container[TeamQueryController::class];
                return $controller->findAllTeams();
            });

            $app->get('/team/{id}', function ($request, $response, $args) use ($container) {
                /** @var TeamQueryController $controller */
                $controller = $container[TeamQueryController::class];
                return $controller->findTeamById($args['id']);
            })->setName('findTeamById');

            $app->put('/team/{id}/contact', function ($request, $response, $args) use ($container) {
                /** @var TeamCommandController $controller */
                $controller = $container[TeamCommandController::class];
                return $controller->updateContact($args['id'], $request);
            })->add($anyAuth);

            $app->get('/season/{id}/team', function ($request, $response, $args) use ($container) {
                /** @var TeamQueryController $controller */
                $controller = $container[TeamQueryController::class];
                return $controller->findTeamsBySeasonId($args['id']);
            });

            $app->post('/team', function ($request) use ($container) {
                /** @var TeamCommandController $controller */
                $controller = $container[TeamCommandController::class];
                return $controller->create($request);
            })->add($validator)->add($anyAuth);

            $app->delete('/team/{id}', function ($request, $response, $args) use ($container) {
                /** @var TeamCommandController $controller */
                $controller = $container[TeamCommandController::class];
                return $controller->delete($args['id']);
            })->add($anyAuth);

            $app->get('/season', function () use ($container) {
                /** @var SeasonQueryController $controller */
                $controller = $container[SeasonQueryController::class];
                return $controller->findAllSeasons();
            });

            $app->get('/season/{id}', function ($request, $response, $args) use ($container) {
                /** @var SeasonQueryController $controller */
                $controller = $container[SeasonQueryController::class];
                return $controller->findSeasonById($args['id']);
            })->setName('findSeasonById');

            $app->get('/season/{id}/ranking', function ($request, $response, $args) use ($container) {
                /** @var SeasonQueryController $controller */
                $controller = $container[SeasonQueryController::class];
                return $controller->findRanking($args['id']);
            });

            $app->get('/season/{id}/matches', function ($request, $response, $args) use ($container) {
                /** @var MatchQueryController $controller */
                $controller = $container[MatchQueryController::class];
                return $controller->findMatchesInSeason($args['id'], $request);
            });

            $app->get('/match/{id}', function ($request, $response, $args) use ($container) {
                /** @var MatchQueryController $controller */
                $controller = $container[MatchQueryController::class];
                return $controller->findMatchById($args['id']);
            });

            $app->get('/pitch', function () use ($container) {
                /** @var PitchQueryController $controller */
                $controller = $container[PitchQueryController::class];
                return $controller->findAllPitches();
            });

            $app->get('/pitch/{id}', function ($request, $response, $args) use ($container) {
                /** @var PitchQueryController $controller */
                $controller = $container[PitchQueryController::class];
                return $controller->findPitchById($args['id']);
            })->setName('findPitchById');

            $app->post('/pitch', function ($request) use ($container) {
                /** @var PitchCommandController $controller */
                $controller = $container[PitchCommandController::class];
                return $controller->create($request);
            })->add($anyAuth);

            $app->put('/pitch/{id}/contact', function ($request, $response, $args) use ($container) {
                /** @var PitchCommandController $controller */
                $controller = $container[PitchCommandController::class];
                return $controller->updateContact($args['id'], $request);
            })->add($anyAuth);

            $app->post('/season/{id}/start', function ($request, $response, $args) use ($container) {
                /** @var SeasonCommandController $controller */
                $controller = $container[SeasonCommandController::class];
                return $controller->start($args['id']);
            })->add($anyAuth);

            $app->delete('/season/{id}', function ($request, $response, $args) use ($container) {
                /** @var SeasonCommandController $controller */
                $controller = $container[SeasonCommandController::class];
                return $controller->delete($args['id']);
            })->add($anyAuth);

            $app->post('/season/{id}/matches', function ($request, $response, $args) use ($container) {
                /** @var SeasonCommandController $controller */
                $controller = $container[SeasonCommandController::class];
                return $controller->createMatches($args['id'], $request);
            })->add($validator)->add($anyAuth);

            $app->post('/match/{id}/kickoff', function ($request, $response, $args) use ($container) {
                /** @var MatchCommandController $controller */
                $controller = $container[MatchCommandController::class];
                return $controller->schedule($args['id'], $request);
            })->add($anyAuth);

            $app->post('/match/{id}/location', function ($request, $response, $args) use ($container) {
                /** @var MatchCommandController $controller */
                $controller = $container[MatchCommandController::class];
                return $controller->locate($args['id'], $request);
            })->add($anyAuth);

            $app->post('/match/{id}/result', function ($request, $response, $args) use ($container) {
                /** @var MatchCommandController $controller */
                $controller = $container[MatchCommandController::class];
                return $controller->submitResult($args['id'], $request);
            })->add($anyAuth);

            $app->post('/match/{id}/cancellation', function ($request, $response, $args) use ($container) {
                /** @var MatchCommandController $controller */
                $controller = $container[MatchCommandController::class];
                return $controller->cancel($args['id']);
            })->add($anyAuth);

            $app->put('/season/{season_id}/team/{team_id}', function ($request, $response, $args) use ($container) {
                /** @var SeasonCommandController $controller */
                $controller = $container[SeasonCommandController::class];
                return $controller->addTeam($args['season_id'], $args['team_id']);
            })->add($anyAuth);

            $app->delete('/season/{season_id}/team/{team_id}', function ($request, $response, $args) use ($container) {
                /** @var SeasonCommandController $controller */
                $controller = $container[SeasonCommandController::class];
                return $controller->removeTeam($args['season_id'], $args['team_id']);
            })->add($anyAuth);

            $app->post('/season', function ($request) use ($container) {
                /** @var SeasonCommandController $controller */
                $controller = $container[SeasonCommandController::class];
                return $controller->createSeason($request);
            })->add($validator)->add($anyAuth);

            $app->post('/tournament', function ($request) use ($container) {
                /** @var TournamentCommandController $controller */
                $controller = $container[TournamentCommandController::class];
                return $controller->create($request);
            })->add($validator)->add($anyAuth);

            $app->put('/tournament/{id}/round/{round}', function ($request, $response, $args) use ($container) {
                /** @var TournamentCommandController $controller */
                $controller = $container[TournamentCommandController::class];
                return $controller->setRound($args['id'], (int) $args['round'], $request);
            })->add($validator)->add($anyAuth);

            $app->get('/tournament', function () use ($container) {
                /** @var TournamentQueryController $controller */
                $controller = $container[TournamentQueryController::class];
                return $controller->findAllTournaments();
            });

            $app->get('/tournament/{id}', function ($request, $response, $args) use ($container) {
                /** @var TournamentQueryController $controller */
                $controller = $container[TournamentQueryController::class];
                return $controller->findTournamentById($args['id']);
            });

            $app->get('/tournament/{id}/matches', function ($request, $response, $args) use ($container) {
                /** @var MatchQueryController $controller */
                $controller = $container[MatchQueryController::class];
                return $controller->findMatchesInTournament($args['id']);
            });

            $app->get('/user/me', function () use ($container) {
                /** @var UserQueryController $controller */
                $controller = $container[UserQueryController::class];
                return $controller->getAuthenticatedUser();
            })->add($anyAuth);

            $app->put('/user/me/password', function ($request) use ($container) {
                /** @var UserCommandController $controller */
                $controller = $container[UserCommandController::class];
                return $controller->changePassword($request);
            })->add($basicAuth);

            $app->post('/user', function ($request) use ($container) {
                /** @var UserCommandController $controller */
                $controller = $container[UserCommandController::class];
                return $controller->createUser($request);
            })->add($anyAuth);

            $app->post('/user/me/password/reset', function ($request) use ($container) {
                /** @var UserCommandController $controller */
                $controller = $container[UserCommandController::class];
                return $controller->sendPasswordResetMail($request);
            });
        });
    }
}