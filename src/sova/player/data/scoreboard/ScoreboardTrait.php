<?php
namespace sova\player\data\scoreboard;

use pocketmine\scheduler\CancelTaskException;
use pocketmine\utils\TextFormat as C;

trait ScoreboardTrait{

    public function display(): void{
        if($this === null || (!$this->isOnline())){
            throw new CancelTaskException();
        }
        if($this->isInLobby()) {
            $this->displayLobby();
            return;
        }

        if($this->getFFA() !== null) {
            $this->displayFFA();
        }
    }

    public function displayFFA(): void
    {
        $scoreboard = $this->getScoreboard();

        if ($scoreboard === null) {
            return;
        }
        $cooldownHandler = $this->getCooldownHandler();
        $lines = [
            "",
            C::colorize("&r&f Kills: &r&5" . $this->getKills()),
            C::colorize("&r&f Deaths: &r&5" . $this->getDeaths()),
            C::colorize("&f"),
        ];
        if($cooldownHandler->isTagged()){
            $lines[] = C::colorize(" &r&fCombat: &r&5" . $cooldownHandler->getCombatAsPrettyInt());
        }
        $lines[] = C::colorize("&r&7 sovamc.club");
        $lines[] = C::colorize("&d");
        $scoreboard->setLines($lines);
    }

    public function displayLobby(): void{
        $scoreboard = $this->getScoreboard();

        $lines = [
            "",
            C::colorize("&r&f Online: &r&5" . count($this->getPlugin()->getServer()->getOnlinePlayers())),
            C::colorize("&r&f Playing: &r&5" . $this->getPlayersInFFA()),
            C::colorize("&f"),

            C::colorize("&r&7 sovamc.club"),
            C::colorize("&d")
        ];

        $scoreboard->setLines($lines);
    }

    public function getPlayersInFFA(): int{
        $count = 0;
        $arenaFactory = $this->getPlugin()->getArenaFactory();

        foreach ($arenaFactory->getArenas() as $arena){
            $count += $arena->getPlayersCount();
        }
        return $count;
    }
}