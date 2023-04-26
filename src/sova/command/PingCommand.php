<?php
namespace sova\command;

use core\abstract\AbstractCommand;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use sova\player\SovaPlayer;
use sova\translation\Translation;

class PingCommand extends AbstractCommand{

    public function __construct(Plugin $plugin){
        parent::__construct($plugin, "ping", "", ["latency"]);
    }

    protected function prepare(): void{
        $this->addConstraint(new InGameRequiredConstraint($this));
        $this->registerArgument(0, new RawStringArgument("player", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
        if($this->isNull($sender)){
            return;
        }
        /** @var SovaPlayer $player */
        $player = isset($args["player"]) ? $this->getPlayerNonNullable($args["player"]) : $sender;
        if($player === null) return;

        $sender->sendMessage(Translation::translate("command.ping", [
            "player" => $player->getName(),
            "ping" => $player?->getNetworkSession()?->getPing()
        ]));
    }
}