<?php

declare(strict_types=1);
namespace sova\player\kit;

use pocketmine\item\Armor;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\utils\TextFormat as C;
use sova\player\data\knockback\Knockback;

abstract class Kit {
    protected ?array $armor = [];
    protected ?array $inventory = [];
    protected ?array $effects = [];

    public function __construct(protected string $name, protected ?Knockback $knockback = null){
        $this->setArmor($this->loadArmor());
        $this->setInventory($this->loadInventory());
        $this->setEffects($this->loadEffects());

       foreach (array_merge($this->armor, $this->inventory) as $content){;
           /**@var Item $content */

           $content->setCustomName(C::colorize("&r&5&lSova"));

           $armorEnchantments = $this->getEnchantments()[0];
           $toolEnchantments = $this->getEnchantments()[1];

           if($content instanceof Armor){
               array_map(function (EnchantmentInstance $enchantment) use ($content): void{
                   $content->addEnchantment($enchantment);
               }, $armorEnchantments);

               $content->setUnbreakable();
           }elseif
           ($content instanceof Tool){
               array_map(function (EnchantmentInstance $enchantment) use ($content): void{
                   $content->addEnchantment($enchantment);
               }, $toolEnchantments);
               $content->setUnbreakable();
           }
       }
    }

    public function getName(): string{
        return $this->name;
    }

    abstract public function loadArmor(): array;

    abstract public function loadInventory(): array;

    abstract public function loadEffects(): array;

    abstract public function getEnchantments(): array;


    public function getArmor(): ?array{
        return $this->armor;
    }

    public function getInventory(): ?array{
        return $this->inventory;
    }

    public function getEffects(): ?array{
        return $this->effects;
    }

    public function getKnockback(): ?Knockback{
        return $this->knockback;
    }

    public function setArmor(?array $armor = null): void{
        $this->armor = $armor;
    }

    public function setInventory(?array $inventory = null): void{
        $this->inventory = $inventory;
    }

    public function setEffects(?array $effects = null): void{
        $this->effects = $effects;
    }

    public function setKnockback(?Knockback $knockback = null): void{
        $this->knockback = $knockback;
    }
}