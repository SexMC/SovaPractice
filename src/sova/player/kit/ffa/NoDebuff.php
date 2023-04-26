<?php
namespace sova\player\kit\ffa;

use pocketmine\item\VanillaItems;
use sova\player\kit\Kit;
use sova\player\kit\KitFactory;

class NoDebuff extends Kit{
    public function loadArmor(): array{

        return [
            VanillaItems::DIAMOND_HELMET(),
            VanillaItems::DIAMOND_CHESTPLATE(),
            VanillaItems::DIAMOND_LEGGINGS(),
            VanillaItems::DIAMOND_BOOTS()
        ];
    }

    public function loadInventory(): array{
        $items = [
            VanillaItems::DIAMOND_SWORD(),
            VanillaItems::ENDER_PEARL()->setCount(16),
        ];
        for($i = 0; $i <= 35; $i++){
            $items[] = VanillaItems::STRONG_HEALING_SPLASH_POTION();
        }

        return $items;
    }

    public function loadEffects(): array{
        return [
            KitFactory::parseEffect("speed", 9999)
        ];
    }

    public function getEnchantments(): array{
        return [
            0 => [KitFactory::parseEnchant("unbreaking", 10)],
            1 => [KitFactory::parseEnchant("unbreaking", 10)]
        ];

    }
}