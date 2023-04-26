<?php
namespace sova\forms\arena;

use cosmicpe\form\entries\simple\Button;
use cosmicpe\form\SimpleForm;
use cosmicpe\form\types\Icon;
use pocketmine\player\Player;
use sova\player\arena\Arena;
use sova\player\arena\event\ArenaTeleportEvent;
use sova\translation\Translation;

class ArenaForm extends SimpleForm{
    public function __construct(array $arenas){
        parent::__construct("FFA Arenas");

        array_map(function (Arena $arena) use ($arenas) {
            $formFunctions = $arena->getFormFunctions();

            $buttonText = Translation::translate("forms.arena.button", [
                "arena" => $arena->getName(),
                "players" => $arena->getPlayersCount(),
                "max" => $arena->getMaxPlayers()
            ]);

            $this->addButton(new Button($buttonText,  new Icon(Icon::PATH, $formFunctions->getIcon())), static function(Player $player, int $index) use ($arena): void{
                /**@var Arena $arena */
                if($arena === null){
                    return;
                }

                if($arena->isFull()){
                    return;
                }

                $event = new ArenaTeleportEvent($player, $arena);
                $event->call();
            });
        }, $arenas);
    }
}