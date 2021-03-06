<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Application\TypeAssert;
use HexagonalPlayground\Application\Value\DatePeriod;

class RescheduleMatchDayCommand implements CommandInterface
{
    use AuthenticationAware;

    /** @var string */
    private $matchDayId;

    /** @var DatePeriod */
    private $datePeriod;

    /**
     * @param string $matchDayId
     * @param DatePeriod $datePeriod
     */
    public function __construct($matchDayId, DatePeriod $datePeriod)
    {
        TypeAssert::assertString($matchDayId, 'matchDayId');
        $this->matchDayId = $matchDayId;
        $this->datePeriod = $datePeriod;
    }

    /**
     * @return string
     */
    public function getMatchDayId(): string
    {
        return $this->matchDayId;
    }

    /**
     * @return DatePeriod
     */
    public function getDatePeriod(): DatePeriod
    {
        return $this->datePeriod;
    }
}