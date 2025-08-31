<?php namespace pvp\levels;

use pvp\PvP;

use core\session\component\{
	ComponentRequest,
	SaveableComponent
};
use core\session\mysqli\data\MySqlQuery;
use core\utils\TextFormat;

class LevelsComponent extends SaveableComponent{

	public int $level = 1;
	public int $experience = 0;

	public function getName() : string{
		return "levels";
	}

	public function getLevel() : int{
		return $this->level;
	}

	public function setLevel(int $level) : void{
		$this->level = $level;
		$this->setChanged();
	}

	public function canLevelUp() : bool{
		return $this->getExperience() >= $this->getExperienceNeeded($this->getLevel());
	}

	public function levelUp() : void{
		$this->setLevel($this->getLevel() + 1);
		$this->setExperience($this->getExperience() - $this->getExperienceNeeded($this->getLevel()));
		$this->getPlayer()?->sendTitle(TextFormat::EMOJI_CONFETTI . TextFormat::LIGHT_PURPLE . TextFormat::BOLD . " LEVEL UP! " . TextFormat::RESET . TextFormat::EMOJI_CONFETTI);
		PvP::getInstance()->getLevels()->getPrizePool()->checkPrizePool($this->getPlayer(), $this->getLevel());
	}

	public function getExperience() : int{
		return $this->experience;
	}

	public function setExperience(int $exp) : void{
		$this->experience = $exp;
		$this->setChanged();
	}

	public function addExperience(int $exp, bool $checkLevel = true) : void{
		$this->experience += $exp;
		$this->setChanged();
		$this->getPlayer()?->sendMessage(TextFormat::GREEN . "+" . $exp . " XP");
		if($checkLevel) if($this->canLevelUp()) $this->levelUp();
	}

	public function getExperienceNeeded(int $level) : int{
		return 1250 * $level;
	}

	public function createTables() : void{
		$db = $this->getSession()->getSessionManager()->getDatabase();
		foreach([
			"CREATE TABLE IF NOT EXISTS levels(xuid BIGINT(16) NOT NULL UNIQUE, level INT NOT NULL DEFAULT 1, exp INT NOT NULL DEFAULT 0)",
		] as $query) $db->query($query);
	}

	public function loadAsync() : void{
		$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery("main", "SELECT * FROM levels WHERE xuid=?", [$this->getXuid()]));
		$this->newRequest($request, ComponentRequest::TYPE_LOAD);
		parent::loadAsync();
	}

	public function finishLoadAsync(?ComponentRequest $request = null) : void{
		$rows = $request->getQuery()->getResult()->getRows();
		if(count($rows) > 0){
			$data = array_shift($rows);
			$this->level = $data["level"];
			$this->experience = $data["exp"];
		}

		parent::finishLoadAsync($request);
		echo $this->getName() . " component finished loading async", PHP_EOL;
	}

	public function verifyChange() : bool{
		$verify = $this->getChangeVerify();
		return $this->getLevel() != $verify["level"] ||
			$this->getExperience() != $verify["exp"];
	}

	public function saveAsync() : void{
		if(!$this->hasChanged() || !$this->isLoaded()) return;

		$this->setChangeVerify([
			"level" => $this->getLevel(),
			"exp" => $this->getExperience()
		]);
		
		$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery("main", "INSERT INTO levels(xuid, level, exp) VALUES(?, ?, ?) ON DUPLICATE KEY UPDATE level=VALUES(level), exp=VALUES(exp)", [$this->getXuid(), $this->getLevel(), $this->getExperience()]));
		$this->newRequest($request, ComponentRequest::TYPE_SAVE);
		parent::saveAsync();
		$this->finishSaveAsync();
	}

	public function finishSaveAsync() : void{
		parent::finishSaveAsync();

		echo $this->getName() . " component finished saving async", PHP_EOL;
	}

	public function save() : bool{
		if(!$this->hasChanged() || !$this->isLoaded()) return false;

		echo $this->getName() . " component saved on main thread", PHP_EOL;
		return parent::save();
	}

}