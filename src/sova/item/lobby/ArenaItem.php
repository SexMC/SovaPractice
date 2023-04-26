<?php
namespace sova\item\lobby;

use pocketmine\item\ItemUseResult;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use sova\forms\arena\ArenaForm;
use sova\item\SovaItem;
use sova\player\SovaPlayer;
use sova\translation\Translation;

class ArenaItem extends SovaItem{

    public function __construct(){
        parent::__construct(VanillaItems::IRON_SWORD(), Translation::translate("item.lobby.ffa"), []);
    }

    public function onClickAir(Player $player, Vector3 $directionVector): ItemUseResult{
        /**@var SovaPlayer $player */

        $arenaFactory = $this->getPlugin()->getArenaFactory();

        if ($player->isInLobby()) {
            $player->sendForm(new ArenaForm(
                $arenaFactory->getArenas()
                ?? []
            ));
            return ItemUseResult::SUCCESS();
        }
        return ItemUseResult::FAIL();
    }
}