<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Import;

use HexagonalPlayground\Application\InputParser;

class L98FileParser
{
    /** @var array */
    private $iniData;

    public function __construct(string $path)
    {
        $data = parse_ini_file($path, true);
        if (!is_array($data)) {
            throw new \Exception('Cannot parse L98 file');
        }
        $this->iniData = $data;
    }

    private function getSection(string $key): ?array
    {
        return isset($this->iniData[$key]) ? $this->iniData[$key] : null;
    }

    private function getValue(string $sectionKey, string $valueKey): ?string
    {
        $section = $this->getSection($sectionKey);
        return ($section !== null && isset($section[$valueKey])) ? $section[$valueKey] : null;
    }

    /**
     * @return L98MatchModel[]
     */
    public function getMatches(): array
    {
        $result = [];
        $matchDay = 1;
        while ($round = $this->getSection(sprintf('Round%d', $matchDay))) {
            $matchIndex = 1;
            while (isset($round['TA' . $matchIndex])) {
                $result[] = new L98MatchModel(
                    InputParser::parseInteger($round['TA' . $matchIndex]),
                    InputParser::parseInteger($round['TB' . $matchIndex]),
                    InputParser::parseInteger($round['GA' . $matchIndex]),
                    InputParser::parseInteger($round['GB' . $matchIndex]),
                    $round['AT' . $matchIndex] !== '' ? InputParser::parseInteger($round['AT' . $matchIndex]) : null,
                    $matchDay
                );

                $matchIndex++;
            }
            $matchDay++;
        }
        return $result;
    }

    /**
     * @return L98TeamModel[]
     */
    public function getTeams(): array
    {
        $result = [];
        $i = 1;
        while ($name = $this->getValue('Teams', (string)$i)) {
            if ($name !== 'Freilos') {
                $result[] = new L98TeamModel($i, $name);
            }
            $i++;
        }
        return $result;
    }

    public function getSeason(): L98SeasonModel
    {
        $name = $this->getValue('Options', 'Name');
        return new L98SeasonModel($name);
    }
}