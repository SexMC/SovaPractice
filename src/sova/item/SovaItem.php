<?php

namespace sova\item;

use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use sova\Loader;

class SovaItem extends Item{

    public function __construct(Item $item, string $name, array $lore = []){
        $this->setCustomName($name);
        if(count($lore) > 0){
            $this->setLore($lore);
        }

        parent::__construct(new ItemIdentifier($item->getId(), $item->getMeta()), $name);
    }

    public function getPlugin(): Loader{
        return Loader::getInstance();
    }
}