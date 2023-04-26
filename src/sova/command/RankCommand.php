<?php
namespace sova\command;

use core\abstract\AbstractCommand;
use CortexPE\Commando\args\BooleanArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use sova\command\subcommands\rank\RankListSubCommand;
use sova\player\rank\event\RankAlterEvent;
use sova\translation\Translation;

class RankCommand extends AbstractCommand{
    public function __construct(Plugin $plugin){
        parent::__construct($plugin, "rank", "", []);

        $this->setPermission("sova.command.rank");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
        $rankFactory = $this->getRankFactory();
        $player = $this->getPlayerNonNullable($args["player"]);
        if($player === null){
            $sender->sendMessage(Translation::translate("rank.command.invalid-player"));
            return;
        }
        $rank = $rankFactory->getRank($args["rank"]);

        if($rank === null){
            $sender->sendMessage(Translation::translate("rank.command.invalid"));
            return;
        }
        $ev = new RankAlterEvent($player, $rank, $args["save"] ?? false);
        $ev->call();

        $sender->sendMessage(Translation::translate("rank.command.success", [
            "player" => $player->getName(),
            "rank" => $rank->getName()
        ]));
    }

    protected function prepare(): void{
        //$this->addConstraint(new InGameRequiredConstraint($this));
        $this->registerSubCommand(new RankListSubCommand($this));

        $this->registerArgument(0, new RawStringArgument("player", false));
        $this->registerArgument(1, new RawStringArgument("rank", false));
        $this->registerArgument(2, new BooleanArgument("save", true));
    }
}