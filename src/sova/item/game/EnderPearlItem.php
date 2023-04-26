<?php

namespace sova\item\game;

use pocketmine\entity\Location;
use pocketmine\entity\projectile\Throwable;
use pocketmine\item\EnderPearl;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use sova\entity\PearlEntity;
use sova\player\SovaPlayer;

class EnderPearlItem extends EnderPearl{

    public function __construct(){
        parent::__construct(new ItemIdentifier(ItemIds::ENDER_PEARL, 0), "Ender Pearl");
    }

    public function getThrowForce() : float{
        return 2.35;
    }

    public function onClickAir(Player $player, Vector3 $directionVector) : ItemUseResult{
        /** @var SovaPlayer $player */
        if ($player->isCreative()) return ItemUseResult::SUCCESS();

        if($player->getCooldownHandler()->isPearl()) return ItemUseResult::FAIL();

        $player->getCooldownHandler()->setPearl();
        return parent::onClickAir($player, $directionVector);
    }


    protected function createEntity(Location $location, Player $thrower) : Throwable{
        return new PearlEntity($location, $thrower);
    }
}