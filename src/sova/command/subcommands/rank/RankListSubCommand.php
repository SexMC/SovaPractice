<?php

declare(strict_types=1);
namespace sova\command\subcommands\rank;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use sova\command\RankCommand;
use sova\translation\Translation;
use function array_slice;
use function count;

class RankListSubCommand extends BaseSubCommand{
    public function __construct(protected RankCommand $command){
        parent::__construct("list", "");
    }

    protected function prepare(): void{
        $this->registerArgument(0, new IntegerArgument("page", true));
    }

    protected function getBaseCommand(): RankCommand{
        return $this->command;
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
        $rankFactory = $this->getBaseCommand()->getRankFactory();

        $ranks = $rankFactory->getRanks();

        $page = $args["page"] ?? 1;
        if ($page <= 0) {
            $page = 1;
        }

        $max = ceil(count($ranks) / 5);
        if ($page >= $max) {
            $page = $max;
        }
        $sender->sendMessage(Translation::translate("rank.command.rank.page.header", ["page" => $page, "max_page" => $max]));
        foreach (array_slice($ranks, (int)($page - 1) * 5, 5) as $rank) {
            $sender->sendMessage(Translation::translate("rank.command.rank.page.entry", ["rank" => $rank->getName(), "id" => $rank->getId()]));

            foreach ($rank->getInheritance() as $inheritance) {
                $sender->sendMessage(Translation::translate("rank.command.rank.page.inheritance", ["rank" => $inheritance->getName()]));
            }
        }
    }
}