<?php namespace pvp\challenges\ui;

use pocketmine\player\Player;

use pvp\PvP;
use pvp\challenges\Session;

use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;

use core\utils\TextFormat;

class LevelsUi extends SimpleForm{

	public $level;
	public $session;
	public $challenges = [];

	public function __construct(int $level, Session $session){
		$this->level = $level;
		$this->session = $session;

		$this->challenges = $challenges = array_values($session->getLevelSession($level)->getChallenges());
		$total = count(SkyBlock::getInstance()->getChallenges()->getChallenges($level));
		$complete = 0;
		foreach($challenges as $challenge){
			if($challenge->isCompleted()) $complete++;
		}

		parent::__construct("Level " . $level . " Challenges", "You have " . $complete . "/" . $total . " challenges completed.");

		foreach($this->challenges as $challenge){
			$this->addButton(new Button(($challenge->isCompleted() ? TextFormat::GREEN : TextFormat::RED) . $challenge->getName()));
		}
		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player){
		foreach($this->challenges as $key => $challenge){
			if($key == $response){
				$player->showModal(new SingleUi($challenge, $this));
				return;
			}
		}
		$player->showModal(new ChallengeUi($player));
	}

}