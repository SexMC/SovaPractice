<?php
namespace sova\item\lobby;

use pocketmine\item\ItemUseResult;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use sova\forms\lobby\SettingsForm;
use sova\item\SovaItem;
use sova\player\SovaPlayer;
use sova\translation\Translation;

class SettingsItem extends SovaItem{

    public function __construct(){
        parent::__construct(VanillaItems::CLOCK(), Translation::translate("item.lobby.settings"), []);
    }

    public function onClickAir(Player $player, Vector3 $directionVector): ItemUseResult{

        if ($player->isInLobby()) {
            $player->sendForm(new SettingsForm($player));
            return ItemUseResult::SUCCESS();
        }
        return ItemUseResult::FAIL();
    }
}