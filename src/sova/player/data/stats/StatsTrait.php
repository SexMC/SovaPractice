<?php

declare(strict_types=1);
namespace sova\player\data\stats;

use sova\database\Statements;
use sova\player\data\cooldowns\CooldownHandler;

trait StatsTrait {
    protected int $kills = 0;
    protected int $deaths = 0;
    protected int $rankId = 0;

    protected function load(): void {
        $plugin = $this->getPlugin();
        $database = $plugin->getDatabase();
        $rankFactory = $plugin->getRankFactory();

        $database->getMysqlHandler()->executeSelect(Statements::FETCH_PLAYER_STATS, ["player" => $this->getName()], function (array $rows) use ($rankFactory): void {
            $results = $rows[0] ?? [];
            var_dump($results);

            $rank = $rankFactory->getRankById($results["rankID"] ?? 0);
            if ($rank === null) {
                $rank = $rankFactory->getDefaultRank();
            }
            $this->setRank($rank);
            $this->setRankTag();
            $rank->bindPermissionsToPlayer($this);
            $this->initScoreboard();
            $this->setCooldownHandler(new CooldownHandler($this));

            $this->setKills($results["kills"] ?? 0);
            $this->setDeaths($results["deaths"] ?? 0);
            $this->setRankId($results["rankID"] ?? 0);
        });
    }

    public function saveData(): void {
        $plugin = $this->getPlugin();
        $database = $plugin->getDatabase();

        if (
            $this->getName() == null ||
            $this->kills == 0 ||
            $this->deaths == 0 
        ) return;
        
        $database->getMysqlHandler()->executeInsert(Statements::SAVE_PLAYER_STATS, [
            "player" => $this->getName(),
            "kills" => $this->getKills(),
            "deaths" => $this->getDeaths(),
            "rankID" => $this->getRankId()
        ]);
    }

    public function setKills(int $kills): void {
        $this->kills = $kills;
    }

    public function setDeaths(int $deaths): void {
        $this->deaths = $deaths;
    }

    public function setRankId(int $rankId): void {
        $this->rankId = $rankId;
    }

    public function getKills(): int {
        return $this->kills;
    }

    public function getDeaths(): int {
        return $this->deaths;
    }

    public function getRankId(): int{
        return $this->rankId;
    }
}