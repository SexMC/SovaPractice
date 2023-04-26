<?php
namespace sova\command;

use core\abstract\AbstractCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use sova\player\kit\KitFactory;
use sova\player\SovaPlayer;
use sova\translation\Translation;

class RekitCommand extends AbstractCommand{

    public function __construct(Plugin $plugin){
        parent::__construct($plugin, "rekit", "", []);
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

        $arena = $sender->getFFA();
        if($arena === null){
            return;
        }
        $kit = $arena->getMiscFunctions()->getKit();

        $sender->sendMessage(Translation::translate("rekit.command.success", []));
        KitFactory::applyKit($sender, $kit);
    }
}