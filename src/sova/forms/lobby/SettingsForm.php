<?php
namespace sova\forms\lobby;

use cosmicpe\form\entries\custom\ToggleEntry;
use cosmicpe\form\CustomForm;
use cosmicpe\form\types\Icon;
use pocketmine\player\Player;
use sova\player\arena\Arena;
use sova\player\arena\event\ArenaTeleportEvent;
use sova\translation\Translation;

class SettingsForm extends CustomForm{
    public function __construct(Player $player){
        parent::__construct("Settings");

        $buttonText = Translation::translate("forms.settings.scoreboard");
        $scoreboardOld = $player->getScoreboardView();

        $this->addEntry(new ToggleEntry($buttonText, $scoreboardOld, static function(Player $player, CustomFormEntry $entry, mixed $value) use ($scoreboardOld): void {

            $player->setScoreboardView($value);

            if($scoreboardOld and !$value){
                $player->removeScoreboard();
                return;
            } 

            if(!$scoreboardOld and $value){
                $player->initScoreboard();
                return;
            }
        }));}}