<?php

namespace core\abstract;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use sova\Loader;
use sova\player\rank\RankFactory;
use sova\player\SovaPlayer;

abstract class AbstractCommand extends BaseCommand{

    protected RankFactory $rankFactory;
    public function __construct(Plugin $plugin, string $name, string $description = "", array $aliases = []){
        $this->rankFactory = Loader::getInstance()->getRankFactory();

        parent::__construct($plugin, $name, $description, $aliases);
    }

    abstract public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void;

    public function isNull(Player|CommandSender $sender) : bool{
        return !$sender instanceof SovaPlayer;
    }

    public function getPlugin():Loader{
        return Loader::getInstance();
    }

    public function getRankFactory():RankFactory{
        return $this->rankFactory;
    }

    public function getPlayerNonNullable(string $name): ?Player{
        return $this->getPlugin()->getServer()->getPlayerByPrefix($name);
    }
}