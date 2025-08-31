<?php namespace pvp\challenges;

use pocketmine\{
	player\Player,
	Server
};
use pocketmine\nbt\tag\{
	CompoundTag,
	IntTag,
	StringTag
};
use pocketmine\event\Event;

use pvp\PvP;

use core\utils\TextFormat;
use pvp\PvPPlayer;

class Challenge{

	const DIFFICULTY_STRINGS = [
		1 => "Easy",
		2 => "Normal",
		3 => "Hard",
	];

	public $id;

	public $name;
	public $class;

	public $description;
	public $unlocklevel = 1;

	public $techits = 0;
	public $difficulty = 1;

	public $progress = [];

	public function __construct(int $id, string $name, string $class, string $description, int $unlocklevel, int $techits, int $difficulty, array $progress = []){
		$this->id = $id;

		$this->name = $name;
		$this->class = $class;

		$this->description = $description;
		$this->unlocklevel = $unlocklevel;

		$this->techits = $techits;
		$this->difficulty = $difficulty;

		$this->progress = $progress;
		$this->progress["completed"] = false;
	}

	public function getId() : int{
		return $this->id;
	}

	public function getName() : string{
		return $this->name;
	}

	public function getClassName() : string{
		return $this->class;
	}

	public function getDescription() : string{
		return $this->description;
	}

	public function getUnlockLevel() : int{
		return $this->unlocklevel;
	}

	public function getTechits() : int{
		return $this->techits;
	}

	public function getDifficulty() : int{
		return $this->difficulty;
	}

	public function getDifficultyString() : string{
		return self::DIFFICULTY_STRINGS[$this->getDifficulty()];
	}

	public function onCompleted(Player $player) : void{
		/** @var PvPPlayer $player */
		Server::getInstance()->broadcastMessage(TextFormat::GI . TextFormat::YELLOW . $player->getName() . TextFormat::GRAY . " has completed the challenge " . TextFormat::AQUA . $this->getName());
		$this->setCompleted();
		$player->addTechits($this->getTechits());

		//Might be able to make this faster?
		//SkyBlock::getInstance()->getLeaderboards()->leaderboards["challenges"]->calculate();
	}

	public function getProgress() : array{
		return $this->progress;
	}

	public function isCompleted() : bool{
		return $this->progress["completed"];
	}

	public function setCompleted(bool $complete = true) : void{
		$this->progress["completed"] = $complete;
	}

	public function setProgressViaNBT(CompoundTag $nbt) : bool{
		$id = $nbt->getInt("id", -1);
		if($id === -1){
			return false;
		}
		$this->progress = unserialize($nbt->getString("progress", serialize(["completed" => false])));
		return true;
	}

	public function getProgressNBT() : CompoundTag{
		return CompoundTag::create()->setInt("id", $this->getId())->setString("progress", serialize($this->getProgress()));
	}

	/**
	 * Returns whether the challenge progress has been affected.
	 */
	public function onEvent(Event $event, Player $player) : bool{
		return false;
	}

}