<?php namespace pvp\challenges;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\BigEndianNbtSerializer as BigEndianNBTStream;

use pvp\PvP;
use pvp\challenges\levels\{
	LevelSession,

	level1\Level1Session,
	level2\Level2Session,
	level3\Level3Session,
	level4\Level4Session,
	level5\Level5Session,
	level6\Level6Session,
	level7\Level7Session,
	level8\Level8Session,
	level9\Level9Session,
	level10\Level10Session,
	level11\Level11Session,
	level12\Level12Session,
	level13\Level13Session,
	level14\Level14Session,
	level15\Level15Session
};

use core\utils\NewSaveableSession;

class Session extends NewSaveableSession{

	const CLASS_NAMESPACE = "\pvp\challenges\levels\level";

	public $done = 0;

	public $levelSessions = [];
	public $challenges = [];

	public function load() : void{
		return;
		parent::load();

		$db = SkyBlock::getInstance()->getDatabase("here");
		$xuid = $this->getXuid();

		$stmt = $db->prepare("SELECT done, data FROM challenge_data WHERE xuid=?");
		$stmt->bind_param("i", $xuid);
		$stmt->bind_result($done, $data);
		if($stmt->execute()){
			$stmt->fetch();
		}
		$stmt->close();

		//checks to generate data
		if($data === null){
			return;
		}

		$this->done = $done;

		try{
			$nbt = unserialize(zlib_decode($data));
			for($i = 1; $i <= $max; $i++){
				if($nbt->getTag("level_" . $i) !== null){
					$this->createLevelSession($i, $nbt->getTag("level_" . $i));
				}
			}
		}catch(\Exception $e){
			for($i = 1; $i <= $max; $i++){
				$this->createLevelSession($i);
			}
		}
	}

	public function getLevelSession(int $level) : LevelSession{
		if(!$this->isLoaded()) $this->load();
		return $this->levelSessions[$level] ?? $this->createLevelSession($level);
	}

	public function hasLevelUnlocked(int $level) : bool{
		if(!$this->isLoaded()) $this->load();
		return isset($this->levelSessions[$level]);
	}

	public function createLevelSession(int $level, ?CompoundTag $tag = null) : LevelSession{
		$class = self::CLASS_NAMESPACE . $level . "\Level" . $level . "Session";
		$this->levelSessions[$level] = new $class($tag == null ? CompoundTag::create() : $tag);
		return $this->levelSessions[$level];
	}

	public function getTotalChallengesCompleted(bool $cache = false) : int{
		if(!$this->isLoaded()) $this->load();

		if($cache){
			if($this->done == -1){
				return $this->getTotalChallengesCompleted();
			}
			return $this->done;
		}

		$count = 0;

		foreach($this->levelSessions as $level => $session){
			foreach($session->getChallenges() as $challenge){
				if($challenge->isCompleted()){
					$count++;
				}
			}
		}
		return $this->done = $count;
	}

	/**
	 * Should be called when a new island is created,
	 * restarts all challlenges and adds level 1 challenges
	 */
	public function new() : void{
		parent::load(); //Sets loaded so new data can save correctly.

		$this->challenges = [];
		$this->getLevelSession(1)->check(); //Adds all level 1 challenges to array
	}

	public function save() : void{
		if(!$this->isLoaded()) return;

		$nbt = CompoundTag::create();
		foreach($this->levelSessions as $level => $session){
			$nbt->setTag("level_" . $session->getLevel(), $session->getSaveNBT());
		}

		$done = $this->getTotalChallengesCompleted();
		$save = zlib_encode(serialize($nbt), ZLIB_ENCODING_DEFLATE, 1);

		$db = PvP::getInstance()->getDatabase("here");
		$xuid = $this->getXuid();

		$stmt = $db->prepare("INSERT INTO challenge_data(xuid, data) VALUES(?, ?) ON DUPLICATE KEY UPDATE data=VALUES(data)");
		$stmt->bind_param("is", $xuid, $save);
		$stmt->execute();
		$stmt->close();
	}

}