<?php

declare(strict_types=1);
namespace sova\player\kit;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\StringToEffectParser;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use sova\Loader;
use sova\player\data\knockback\Knockback;
use sova\player\kit\ffa\NoDebuff;
use sova\player\SovaPlayer;

class KitFactory {
    static private array $kits = [];

    /**
     * TODO: probably recode this for support for duels.
     */
    public static function init(Loader $plugin): void{
        $kits = [
            new NoDebuff("NoDebuff"),
            new NoDebuff("NoDebuffCrits"),
            new NoDebuff("NoDebuffModern")
        ];

        self::register($kits);
        self::registerKnockback((new Config($plugin->getDataFolder() . "knockback.yml", Config::YAML))->get("kits"));
    }

    static function registerKnockback(array $knockbackData = []): void{
        foreach ($knockbackData as $name => $data) {
            try {
                $kit = self::get($name);
                $kit->setKnockback(new Knockback($data["horizontal"], $data["vertical"], $data["horizontal"], $data["vertical"], (bool) $data["criticals"], (bool)$data["heightLimiter"]));
            } catch (\Exception $exception) {
                var_dump($exception->getMessage());
                continue;
            }
        }
    }

    public static function parseEnchant(string $enchantment, int $level = 1): EnchantmentInstance{
        $enchant = StringToEnchantmentParser::getInstance()->parse($enchantment);
        if($enchant === null){
            throw new \InvalidArgumentException("Invalid enchantment $enchantment");
        }
        return new EnchantmentInstance($enchant, $level);
    }

    public static function parseEffect(string $effect, int $duration, int $amplifier = 0): EffectInstance{
        $effect = StringToEffectParser::getInstance()->parse($effect);
        if($effect === null){
            throw new \InvalidArgumentException("Invalid effect $effect");
        }
        return new EffectInstance($effect,20 * $duration, $amplifier,false);
    }

    public static function applyKit(Player $player, Kit $kit): void{
        $player->setHealth($player->getMaxHealth());

        $armorInventory = $player->getArmorInventory();
        $playerInventory = $player->getInventory();
        

        $armorInventory->setContents($kit->getArmor());
        $playerInventory->setContents($kit->getInventory());

        foreach ($kit->getEffects() as $effect){
            $player->getEffects()->add(clone $effect);
        }

        /**@var SovaPlayer $player */
        if($player->getKnockback() === $kit->getKnockback()){
            return;
        }
        $player->setKnockback($kit->getKnockback());
    }

    public static function register(array $kitsData = []): void{
        /** @var Kit $kit */
        foreach($kitsData as $kit){
            self::$kits[$kit->getName()] = $kit;
        }
    }

    public static function get(string $name): ?Kit{
        return self::$kits[$name] ?? null;
    }
}