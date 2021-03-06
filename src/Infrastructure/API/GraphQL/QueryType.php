<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Filter\EventFilter;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Infrastructure\Persistence\Read\EventRepository;
use HexagonalPlayground\Infrastructure\Persistence\Read\MatchRepository;
use HexagonalPlayground\Infrastructure\Persistence\Read\PitchRepository;
use HexagonalPlayground\Infrastructure\Persistence\Read\SeasonRepository;
use HexagonalPlayground\Infrastructure\Persistence\Read\TeamRepository;
use HexagonalPlayground\Infrastructure\Persistence\Read\TournamentRepository;
use HexagonalPlayground\Infrastructure\Persistence\Read\UserRepository;

class QueryType extends ObjectType
{
    use SingletonTrait;

    public function __construct()
    {
        $config = [
            'fields' => function () {
                return [
                    'season' => [
                        'type' => SeasonType::getInstance(),
                        'description' => 'Get a single season',
                        'args' => [
                            'id' => Type::nonNull(Type::string())
                        ],
                        'resolve' => function ($root, array $args, AppContext $context) {
                            /** @var SeasonRepository $repo */
                            $repo = $context->getContainer()->get(SeasonRepository::class);

                            try {
                                return $repo->findSeasonById($args['id']);
                            } catch (NotFoundException $e) {
                                return null;
                            }
                        }
                    ],
                    'allSeasons' => [
                        'type' => Type::listOf(SeasonType::getInstance()),
                        'description' => 'Get a list of all seasons',
                        'resolve' => function ($root, $args, AppContext $context) {
                            /** @var SeasonRepository $repo */
                            $repo = $context->getContainer()->get(SeasonRepository::class);

                            return $repo->findAllSeasons();
                        }
                    ],
                    'allTeams' => [
                        'type' => Type::listOf(TeamType::getInstance()),
                        'resolve' => function ($root, $args, AppContext $context) {
                            /** @var TeamRepository $repo */
                            $repo = $context->getContainer()->get(TeamRepository::class);

                            return $repo->findAllTeams();
                        }
                    ],
                    'team' => [
                        'type' => TeamType::getInstance(),
                        'args' => [
                            'id' => Type::nonNull(Type::string())
                        ],
                        'resolve' => function ($root, array $args, AppContext $context) {
                            /** @var TeamRepository $repo */
                            $repo = $context->getContainer()->get(TeamRepository::class);

                            try {
                                return $repo->findTeamById($args['id']);
                            } catch (NotFoundException $e) {
                                return null;
                            }
                        }
                    ],
                    'event' => [
                        'type' => EventType::getInstance(),
                        'args' => [
                            'id' => Type::nonNull(Type::string())
                        ],
                        'resolve' => function ($root, array $args, AppContext $context) {
                            /** @var EventRepository $repo */
                            $repo = $context->getContainer()->get(EventRepository::class);

                            try {
                                return $repo->findEventById($args['id']);
                            } catch (NotFoundException $e) {
                                return null;
                            }
                        }
                    ],
                    'latestEvents' => [
                        'type' => Type::listOf(EventType::getInstance()),
                        'args' => [
                            'start_date' => DateType::getInstance(),
                            'end_date' => DateType::getInstance(),
                            'type' => Type::string(),
                        ],
                        'resolve' => function ($root, array $args, AppContext $context) {
                            /** @var EventRepository $repo */
                            $repo = $context->getContainer()->get(EventRepository::class);

                            $filter = new EventFilter(
                                $args['start_date'] ?? null,
                                $args['end_date'] ?? null,
                                $args['type'] ?? null
                            );

                            return $repo->findLatestEvents($filter);
                        }
                    ],
                    'tournament' => [
                        'type' => TournamentType::getInstance(),
                        'args' => [
                            'id' => Type::nonNull(Type::string())
                        ],
                        'resolve' => function ($root, array $args, AppContext $context) {
                            /** @var TournamentRepository $repo */
                            $repo = $context->getContainer()->get(TournamentRepository::class);

                            try {
                                return $repo->findTournamentById($args['id']);
                            } catch (NotFoundException $e) {
                                return null;
                            }
                        }
                    ],
                    'allTournaments' => [
                        'type' => Type::listOf(TournamentType::getInstance()),
                        'resolve' => function ($root, $args, AppContext $context) {
                            /** @var TournamentRepository $repo */
                            $repo = $context->getContainer()->get(TournamentRepository::class);

                            return $repo->findAllTournaments();
                        }
                    ],
                    'match' => [
                        'type' => MatchType::getInstance(),
                        'args' => [
                            'id' => Type::nonNull(Type::string())
                        ],
                        'resolve' => function ($root, array $args, AppContext $context) {
                            /** @var MatchRepository $repo */
                            $repo = $context->getContainer()->get(MatchRepository::class);

                            try {
                                return $repo->findMatchById($args['id']);
                            } catch (NotFoundException $e) {
                                return null;
                            }
                        }
                    ],
                    'pitch' => [
                        'type' => PitchType::getInstance(),
                        'args' => [
                            'id' => Type::nonNull(Type::string())
                        ],
                        'resolve' => function ($root, array $args, AppContext $context) {
                            /** @var PitchRepository $repo */
                            $repo = $context->getContainer()->get(PitchRepository::class);

                            try {
                                return $repo->findPitchById($args['id']);
                            } catch (NotFoundException $e) {
                                return null;
                            }
                        }
                    ],
                    'allPitches' => [
                        'type' => Type::listOf(PitchType::getInstance()),
                        'resolve' => function ($root, $args, AppContext $context) {
                            /** @var PitchRepository $repo */
                            $repo = $context->getContainer()->get(PitchRepository::class);

                            return $repo->findAllPitches();
                        }
                    ],
                    'authenticatedUser' => [
                        'type' => UserType::getInstance(),
                        'resolve' => function ($root, $args, AppContext $context) {
                            return $context->getAuthenticatedUser()->jsonSerialize(false);
                        }
                    ],
                    'allUsers' => [
                        'type' => Type::listOf(UserType::getInstance()),
                        'resolve' => function ($root, array $args, AppContext $context) {
                            /** @var UserRepository $repo */
                            $repo = $context->getContainer()->get(UserRepository::class);
                            IsAdmin::check($context->getAuthenticatedUser());

                            return $repo->findAllUsers();
                        }
                    ]
                ];
            }
        ];
        parent::__construct($config);
    }
}
