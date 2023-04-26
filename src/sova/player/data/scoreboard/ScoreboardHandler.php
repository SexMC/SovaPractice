<?php
namespace sova\player\data\scoreboard;

use core\abstract\AbstractScoreboard;
use pocketmine\scheduler\ClosureTask;
use sova\player\SovaPlayer;

class ScoreboardHandler extends AbstractScoreboard{
    public function __construct(protected SovaPlayer $player, string $title){
        parent::__construct($player, $title);

        $plugin = $player->getPlugin();
        $plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(fn() => $player->display()), 20);

    }
}