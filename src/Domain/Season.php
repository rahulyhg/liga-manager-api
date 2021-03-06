<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use HexagonalPlayground\Domain\Event\Publisher;
use HexagonalPlayground\Domain\Event\SeasonCreated;
use HexagonalPlayground\Domain\Event\SeasonEnded;
use HexagonalPlayground\Domain\Event\SeasonStarted;
use HexagonalPlayground\Domain\Util\Assert;

class Season extends Competition
{
    const STATE_PREPARATION = 'preparation';
    const STATE_PROGRESS = 'progress';
    const STATE_ENDED = 'ended';

    /** @var Collection|Team[] */
    private $teams;

    /** @var Ranking|null */
    private $ranking;

    /** @var string */
    private $state;

    /** @var int */
    private $matchDayCount;

    /** @var int */
    private $teamCount;

    /**
     * @param string $id
     * @param string $name
     */
    public function __construct(string $id, string $name)
    {
        Assert::minLength($id, 1, "A season's id cannot be blank");
        Assert::minLength($name, 1, "A season's name cannot be blank");
        Assert::maxLength($name, 255, "A season's name cannot exceed 255 characters");
        $this->id = $id;
        $this->name = $name;
        $this->teams = new ArrayCollection();
        $this->matchDays = new ArrayCollection();
        $this->state = self::STATE_PREPARATION;
        $this->teamCount = 0;
        $this->updateMatchDayCount();
        Publisher::getInstance()->publish(SeasonCreated::create($this->id));
    }

    /**
     * @param Team $team
     */
    public function addTeam(Team $team): void
    {
        Assert::false($this->hasStarted(), 'Cannot add teams to season which has already started');
        if (!$this->teams->contains($team)) {
            $this->teams[] = $team;
            $this->teamCount++;
        }
    }

    /**
     * @param Team $team
     */
    public function removeTeam(Team $team): void
    {
        Assert::false($this->hasStarted(), 'Cannot remove teams from a season which has already started');
        if ($this->teams->contains($team)) {
            $this->teams->removeElement($team);
            $this->teamCount--;
        }
    }

    /**
     * @return Team[]
     */
    public function getTeams() : array
    {
        return $this->teams->toArray();
    }

    /**
     * Removes all teams from season
     */
    public function clearTeams(): void
    {
        Assert::false($this->hasStarted(), 'Cannot remove teams from a season which has already started');
        $this->teams->clear();
        $this->teamCount = 0;
    }

    /**
     * Removes all match days and their matches from season
     */
    public function clearMatchDays(): void
    {
        Assert::false($this->hasStarted(), 'Cannot remove matches from a season which has already started');
        foreach ($this->matchDays as $matchDay) {
            $matchDay->clearMatches();
        }
        $this->matchDays->clear();
        $this->updateMatchDayCount();
    }

    /**
     * @return bool
     */
    private function hasStarted() : bool
    {
        return ($this->ranking !== null);
    }

    /**
     * @return bool
     */
    public function hasEnded() : bool
    {
        return $this->state === self::STATE_ENDED;
    }

    /**
     * @return bool
     */
    public function isInProgress(): bool
    {
        return $this->state === self::STATE_PROGRESS;
    }

    /**
     * @return bool
     */
    private function hasMatches() : bool
    {
        return $this->matchDays->count() > 0;
    }

    /**
     * Initializes the season ranking
     */
    public function start(): void
    {
        Assert::false($this->hasStarted(), 'Cannot start a season which has already been started');
        Assert::true($this->hasMatches(), 'Cannot start a season which has no matches');
        $this->ranking = new Ranking($this);
        $this->state = self::STATE_PROGRESS;
        Publisher::getInstance()->publish(SeasonStarted::create($this->id));
    }

    /**
     * Finalizes the season
     */
    public function end(): void
    {
        Assert::true($this->hasStarted(), 'Cannot end a season which has not been started');
        $this->state = self::STATE_ENDED;
        Publisher::getInstance()->publish(SeasonEnded::create($this->id));
    }

    public function createMatchDay(int $number, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate): MatchDay
    {
        $matchDay = parent::createMatchDay($number, $startDate, $endDate);
        $this->updateMatchDayCount();
        return $matchDay;
    }

    /**
     * @return Ranking
     */
    public function getRanking(): Ranking
    {
        Assert::false(
            $this->ranking === null,
            'Cannot access ranking for a season which has not been started'
        );
        return $this->ranking;
    }

    /**
     * @return Match[]
     */
    public function getMatches(): array
    {
        $matches = [];
        foreach ($this->matchDays as $matchDay) {
            /** @var MatchDay $matchDay */
            $matches = array_merge($matches, $matchDay->getMatches());
        }
        return $matches;
    }

    private function updateMatchDayCount(): void
    {
        $this->matchDayCount = $this->matchDays->count();
    }
}
