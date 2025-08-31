<?php namespace skyblock\challenges\levels\level10;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class MineObsidianChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$block = $event->getBlock();
			if($block->getId() == Block::OBSIDIAN){
				$this->progress["obsidian"]["progress"]++;
				if($this->progress["obsidian"]["progress"] >= 16){
					$this->onCompleted($player);
				}
				return true;
			}
		}
		return false;
	}

}