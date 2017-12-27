<?php

namespace HexagonalDream\Application\Repository;

class PitchRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public function findAllPitches()
    {
        return $this->getDb()->fetchAll('SELECT * FROM `pitches`');
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function findPitchById(string $id)
    {
        return $this->getDb()->fetchFirstRow('SELECT * FROM `pitches` WHERE `id` = :id', ['id' => $id]);
    }
}
