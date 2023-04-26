<?php
namespace sova\command;

use core\abstract\AbstractCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use sova\player\SovaPlayer;

class SpawnCommand extends AbstractCommand{

    public function __construct(Plugin $plugin){
        parent::__construct($plugin, "spawn", "", ["lobby"]);
    }

    protected function prepare(): void{
        $this->addConstraint(new InGameRequiredConstraint($this));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
        if($this->isNull($sender)){
            return;
        }
        /** @var SovaPlayer $sender */
        $cooldownHandler = $sender->getCooldownHandler();

        if($cooldownHandler->isTagged()) return;
        if ($sender->isFrozen()) return;

        $sender->initialize();
    }
}