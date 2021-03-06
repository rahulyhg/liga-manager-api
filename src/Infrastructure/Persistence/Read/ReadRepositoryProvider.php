<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Infrastructure\Environment;
use HexagonalPlayground\Infrastructure\Persistence\QueryLogger;
use mysqli;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ReadRepositoryProvider implements ServiceProviderInterface
{

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $container A container instance
     */
    public function register(Container $container)
    {
        $container[ReadDbAdapterInterface::class] = function() use ($container) {
            $mysqli = new mysqli(
                Environment::get('MYSQL_HOST'),
                Environment::get('MYSQL_USER'),
                Environment::get('MYSQL_PASSWORD'),
                Environment::get('MYSQL_DATABASE')
            );
            $mysqli->set_charset('utf8');
            $db = new MysqliReadDbAdapter($mysqli);
            $db->setLogger(new QueryLogger($container['logger']));
            return $db;
        };
        $container[TeamRepository::class] = function() use ($container) {
            return new TeamRepository($container[ReadDbAdapterInterface::class]);
        };
        $container[SeasonRepository::class] = function() use ($container) {
            return new SeasonRepository($container[ReadDbAdapterInterface::class]);
        };
        $container[PitchRepository::class] = function() use ($container) {
            return new PitchRepository($container[ReadDbAdapterInterface::class]);
        };
        $container[MatchRepository::class] = function() use ($container) {
            return new MatchRepository($container[ReadDbAdapterInterface::class]);
        };
        $container[TournamentRepository::class] = function () use ($container) {
            return new TournamentRepository($container[ReadDbAdapterInterface::class]);
        };
        $container[UserRepository::class] = function () use ($container) {
            return new UserRepository($container[ReadDbAdapterInterface::class]);
        };
        $container[EventRepository::class] = function () use ($container) {
            return new EventRepository($container[ReadDbAdapterInterface::class]);
        };
    }
}