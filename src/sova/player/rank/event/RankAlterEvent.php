<?php
namespace sova\player\rank\event;

use pocketmine\event\CancellableTrait;
use pocketmine\event\player\PlayerEvent;
use sova\player\rank\Rank;

class RankAlterEvent extends PlayerEvent{
    use CancellableTrait;

    protected $player;
    protected Rank $rank;
    protected bool $shouldSave = false;

    public function __construct($player, Rank $rank, bool $shouldSave){
        $this->player = $player;
        $this->rank = $rank;
    }

    public function getRank(): Rank{
        return $this->rank;
    }

    public function shouldSave(): bool{
        return $this->shouldSave;
    }
}