<?php namespace pvp\challenges;

use pvp\PvP;
use pvp\challenges\commands\ChallengesCommand;

class Challenges{

	public array $challenges = [
		1 => [],
		2 => [],
		3 => [],
		4 => [],
		5 => [],
		6 => [],
		7 => [],
		8 => [],
		9 => [],
		10 => [],
		11 => [],
		12 => [],
		13 => [],
		14 => [],
		15 => []
	];

	public function __construct(public PvP $plugin){
		/**foreach([
			"CREATE TABLE challenge_data(xuid BIGINT(16) NOT NULL UNIQUE, completed INT NOT NULL DEFAULT '0', data VARCHAR(30000))",
		] as $query) PvP::getInstance()->getDatabase("here")->query($query);*/

		$plugin->getServer()->getCommandMap()->register("challenges", new ChallengesCommand($plugin, "challenges", "Access your challenge progress"));
		//$plugin->getServer()->getPluginManager()->registerEvents(new ChallengeListener($plugin, $this), $plugin);

		//$this->loadChallenges();
	}

	public function loadChallenges() : void{
		foreach(ChallengeData::CHALLENGES as $level => $challenges){
			foreach($challenges as $id => $data){
				$name = $data["name"];
				$description = $data["description"];
				$techits = $data["techits"];
				$difficulty = $data["difficulty"];
				$class = Session::CLASS_NAMESPACE . $level . "\\" . $data["class"];
				$progress = $data["progress"];

				$this->challenges[$level][$id] = new $class($id, $name, $class, $description, $level, $techits, $difficulty, $progress);
			}
		}
	}

	public function getChallengeCount() : int{
		$count = 0;
		foreach($this->challenges as $level){
			foreach($level as $challenge){
				$count++;
			}
		}
		return $count;
	}

	public function getChallenges(int $level) : array{
		$challenges = [];
		$mc = $this->challenges[$level] ?? [];
		foreach($mc as $id => $challenge){
			$challenges[$id] = clone $challenge;
		}
		return $challenges;
	}

	public function getChallenge(int $id) : ?Challenge{
		for($i = 1; $i <= 15; $i++){
			$challenges = $this->getChallenges($i);
			foreach($challenges as $challenge){
				if($challenge->getId() === $id){
					return $challenge;
				}
			}
		}
		return null;
	}

}