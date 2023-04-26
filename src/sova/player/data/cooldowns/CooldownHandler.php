<?php
namespace sova\player\data\cooldowns;

use pocketmine\scheduler\ClosureTask;
use sova\player\SovaPlayer;

class CooldownHandler{
    protected ?int $combat = 0;
    protected ?int $pearl = 0;

    public function __construct(protected SovaPlayer $player){
        $plugin = $player->getPlugin();

        $plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(fn() => $player->check()), 1);
    }

    public function setPearl(): void{
        $this->pearl = time() + 10;
    }

    public function isPearl(): bool{
        return $this->pearl > time();
    }

    public function getPearl(): int{
        return $this->pearl;
    }

    public function setTagged(): void{
        $this->combat = time() + 15;
    }

    public function isTagged(): bool{
        return $this->combat >= time() AND $this->combat !== 0;
    }

    public function getCombatAsPrettyInt(): int{
        return $this->combat - time();
    }
}