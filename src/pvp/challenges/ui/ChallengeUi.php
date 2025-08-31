<?php namespace pvp\challenges\ui;

use pocketmine\player\Player;

use pvp\PvP;

use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;

use core\utils\TextFormat;

class ChallengeUi extends SimpleForm{

	public function __construct(Player $player, int $page = 1){
		parent::__construct("Challenges", "View your challenges below!");

		return; //todo: way to calculate which challenges to show along with page
		$isession = PvP::getInstance()->getIslands()->getSessionManager()->getSession($player);
		$csession = PvP::getInstance()->getChallenges()->getSessionManager()->getSession($player);

		if($isession->hasIsland()){
			for($i = 1; $i <= $isession->getIsland()->getSizeLevel(); $i++){
				$challenges = $csession->getLevelSession($i)->getChallenges();
				$total = count(SkyBlock::getInstance()->getChallenges()->getChallenges($i));
				$complete = 0;
				foreach($challenges as $challenge){
					if($challenge->isCompleted()) $complete++;
				}
				$this->addButton(new Button("Level " . $i . " Challenges" . "\n" . $complete . "/" . $total . " completed"));
			}
		}
	}

	public function handle($response, Player $player){
		$isession = SkyBlock::getInstance()->getIslands()->getSessionManager()->getSession($player);
		$csession = SkyBlock::getInstance()->getChallenges()->getSessionManager()->getSession($player);

		$level = $response + 1;

		if(!$isession->hasIsland()){
			$player->sendMessage(TextFormat::RI . "You must have an island to do this!");
			return;
		}
		$island = $isession->getIsland();
		if($island->getSizeLevel() < $level){
			$player->sendMessage(TextFormat::RI . "Your island is not high enough level to access these challenges!");
			return;
		}

		$player->showModal(new LevelsUi($level, $csession));
	}

}