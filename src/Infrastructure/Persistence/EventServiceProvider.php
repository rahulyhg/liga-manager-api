<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence;

use HexagonalPlayground\Application\Bus\HandlerResolver;
use HexagonalPlayground\Application\EventSerializer;
use HexagonalPlayground\Application\EventStoreInterface;
use HexagonalPlayground\Application\EventStoreSubscriber;
use HexagonalPlayground\Domain\EventPublisher;
use HexagonalPlayground\Domain\MatchLocated;
use HexagonalPlayground\Domain\MatchResultSubmitted;
use HexagonalPlayground\Domain\MatchScheduled;
use MongoDB\Client;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class EventServiceProvider implements ServiceProviderInterface
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
        $container->extend(HandlerResolver::class, function($handlerResolver) use ($container) {
            EventPublisher::getInstance()->addSubscriber(
                new EventStoreSubscriber($container[EventStoreInterface::class])
            );
            return $handlerResolver;
        });
        $container[EventStoreInterface::class] = function () use ($container) {
            $client = new Client('mongodb://' . getenv('MONGO_HOST'));
            $db = $client->{getenv('MONGO_DATABASE')};
            return new MongoEventStore($db->events, $container[EventSerializer::class]);
        };
        $container[EventSerializer::class] = function () {
            return new EventSerializer([
                'match:result:submitted' => function ($id, $occurredAt, $payload) {
                    return new MatchResultSubmitted($id, $occurredAt, $payload);
                },
                'match:located' => function ($id, $occurredAt, $payload) {
                    return new MatchLocated($id, $occurredAt, $payload);
                },
                'match:scheduled' => function ($id, $occurredAt, $payload) {
                    return new MatchScheduled($id, $occurredAt, $payload);
                }
            ]);
        };
    }
}