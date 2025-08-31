<?php namespace pvp\challenges\ui;

use pocketmine\player\Player;

use pvp\PvP;
use pvp\challenges\Challenge;

use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;

class SingleUi extends SimpleForm{

	public $challenge;
	public $prev;

	public function __construct(Challenge $challenge, $prev){
		$this->challenge = $challenge;
		$this->prev = $prev;

		$string = "";
		foreach($challenge->getProgress() as $progress => $data){
			if(is_array($data)){
				$dstring = $data["progress"] . "/" . $data["needed"];
			}else{
				if($progress == "completed"){
					if($data){
						$dstring = "YES";
					}else{
						$dstring = "NO";
					}
				}else{
					$dstring = $data;
				}
			}
			$string .= ucfirst($progress) . ": " . $dstring . "\n";
		}
		parent::__construct($challenge->getName(), $challenge->getDescription() . "\n" . "\n" . "Challenge Progress:" . "\n" . "\n" . $string . "\n" . "Completed prize: " . $challenge->getTechits() . " Techits" . "\n" . "\n" . "Difficulty: " . $challenge->getDifficultyString());

		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player){
		$player->showModal($this->prev);
	}

}