<?php
namespace sova\player\arena\misc;

use pocketmine\world\World;
use sova\player\data\knockback\Knockback;
use sova\player\kit\Kit;

class MiscFunctions{

    public function __construct(private Kit $kit, protected ?World $world) {

    }

    public function getKit(): Kit{
        return $this->kit;
    }

    public function getWorld(): ?World{
        return $this->world;
    }

    public function getKnockback(): Knockback{
        return $this->getKit()->getKnockback();
    }
}