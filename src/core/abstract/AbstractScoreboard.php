<?php

namespace core\abstract;

use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\player\Player;

class AbstractScoreboard{

    private const SORT_ASCENDING = 0;
    private const SORT_DESCENDING = 1;
    private const SLOT_SIDEBAR = "sidebar";

    private array $lines;
    private string $title;

    private Player $player;
    private bool $isScoreboard = true;

    public function __construct(Player $player, string $title){
        $this->player = $player;
        $this->title = $title;
        $this->lines = [];
        $this->initScoreboard();
    }

    public function sendScore(Player $player) : void{
        $this->player = $player;
        $this->lines = [];
        $this->initScoreboard();
    }

    private function initScoreboard() : void{
        $this->isScoreboard = true;
        $pkt = new SetDisplayObjectivePacket();
        $pkt->objectiveName = $this->player->getName();
        $pkt->displayName = $this->title;
        $pkt->sortOrder = self::SORT_ASCENDING;
        $pkt->displaySlot = self::SLOT_SIDEBAR;
        $pkt->criteriaName = "dummy";

        $this->player->getNetworkSession()->sendDataPacket($pkt);
    }

    public function clearScoreboard() : void{
        $packet = new SetScorePacket();
        $packet->entries = $this->lines;
        $packet->type = SetScorePacket::TYPE_REMOVE;
        $this->player->getNetworkSession()->sendDataPacket($packet);
        $this->lines = [];
    }

    public function addLine(int $id, string $line) : void{
        $entry = new ScorePacketEntry();
        $entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;

        if(isset($this->lines[$id])){
            $pkt = new SetScorePacket();
            $pkt->entries[] = $this->lines[$id];
            $pkt->type = SetScorePacket::TYPE_REMOVE;
            $this->player->getNetworkSession()->sendDataPacket($pkt);
            unset($this->lines[$id]);
        }

        $entry->score = $id;
        $entry->scoreboardId = $id;
        $entry->actorUniqueId = $this->player->getId();
        $entry->objectiveName = $this->player->getName();
        $entry->customName = $line;
        $this->lines[$id] = $entry;

        $pkt = new SetScorePacket();

        $pkt->entries[] = $entry;
        $pkt->type = SetScorePacket::TYPE_CHANGE;
        $this->player->getNetworkSession()->sendDataPacket($pkt);
    }

    public function removeLine(int $id) : void{
        if(isset($this->lines[$id])){
            $line = $this->lines[$id];
            $packet = new SetScorePacket();
            $packet->entries[] = $line;
            $packet->type = SetScorePacket::TYPE_REMOVE;
            $this->player->getNetworkSession()->sendDataPacket($packet);
        }

        unset($this->lines[$id]);
    }

    public function setLines(array $lines) : void{
        $this->isScoreboard = true;
        $this->clearScoreboard();
        foreach($lines as $id => $line){
            $this->addLine($id, $line);
        }
    }

    public function isScoreboard() : bool{
        return $this->isScoreboard;
    }

    public function removeScoreboard() : void{
        $pkt = new RemoveObjectivePacket();
        $pkt->objectiveName = $this->player->getName();
        $this->player->getNetworkSession()->sendDataPacket($pkt);
    }
}