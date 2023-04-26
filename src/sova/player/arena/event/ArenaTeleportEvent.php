<?php
namespace sova\player\arena\event;

use pocketmine\event\CancellableTrait;
use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;
use sova\player\arena\Arena;

class ArenaTeleportEvent extends PlayerEvent{
    use CancellableTrait;

    protected $player;
    protected Arena $arena;

    public function __construct(Player $player, Arena $arena){
        $this->player = $player;
        $this->arena = $arena;
    }

    public function getArena(): Arena{
        return $this->arena;
    }

    public function getPlayer(): Player{
        return $this->player;
    }

    public function isFull(): bool{
        return $this->getArena()->isFull();
    }
}