<?php
namespace sova\player\data\cooldowns;

use pocketmine\scheduler\CancelTaskException;

trait CooldownTrait{

    public function check(): void{
        if($this === null || (!$this->isOnline())){
            throw new CancelTaskException();
        }

        $cooldownHandler = $this->getCooldownHandler();

        if($cooldownHandler === null){
            return;
        }
        if($cooldownHandler->isPearl()){
            $currentTick = microtime(true);
            $pearl = $cooldownHandler->getPearl();
            $pearl-= $currentTick;
            $progress = $pearl / 10;
            $level = round($pearl);

            $this->getXpManager()->setXpAndProgress($level, $progress);
        }
    }
}