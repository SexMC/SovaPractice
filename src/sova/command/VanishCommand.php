<?php

namespace sova\command;


use core\abstract\AbstractCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use pocketmine\block\VanillaBlocks;
use pocketmine\command\CommandSender;
use pocketmine\item\VanillaItems;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use sova\player\SovaPlayer;

class VanishCommand extends AbstractCommand {

    public function __construct(Plugin $plugin){
        parent::__construct($plugin, "vanish", "", []);

        $this->setPermission("sova.command.vanish");
    }

    protected function prepare(): void{
        $this->addConstraint(new InGameRequiredConstraint($this));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
        if($this->isNull($sender)) return;

        if ($sender instanceof SovaPlayer) {
            if (!$sender->isVanished()) {
                $sender->toggleVanish();
                $sender->sendActionBarMessage(TextFormat::GREEN . "Enabled Vanish");

                $sender->getInventory()->clearAll();
                $sender->getInventory()->setContents([
                    2 => VanillaItems::BLAZE_ROD()->setCustomName(TextFormat::RED . "Ban Stick" . TextFormat::GRAY . " (Hit Player)"),
                    4 => VanillaBlocks::PACKED_ICE()->asItem()->setCustomName(TextFormat::RED . "Freeze Block" . TextFormat::GRAY . " (Hit Player)"),
                    6 => VanillaItems::COMPASS()->setCustomName(TextFormat::RED . "Teleport Compass" . TextFormat::GRAY . " (Right-Click)")
                ]);

                foreach ($this->getPlugin()->getServer()->getOnlinePlayers() as $player) {
                    $player->hidePlayer($sender);
                }

                $sender->setFlying(true);
                $sender->setAllowFlight(true);
                return;
            }
            $sender->toggleVanish();
            $sender->sendActionBarMessage(TextFormat::RED . "Disabled Vanish");

            $sender->getInventory()->clearAll();
            $this->getPlugin()->getServer()->dispatchCommand($sender, "rekit");

            foreach ($this->getPlugin()->getServer()->getOnlinePlayers() as $player) {
                $player->showPlayer($sender);
            }

            $sender->setFlying(false);
            $sender->setAllowFlight(false);
        }
    }
}