<?php namespace pvp\techits;

/**
 * Area used for server-specific techit saving.
 * Probably looks bad but hey it works..
 */

use pvp\PvP;

use core\Core;
use core\discord\objects\{
	Post,
	Webhook
};
use core\utils\{
	NewSaveableSession,
	InstantLoad
};

// Whole lotta nothin here shane

class Session extends NewSaveableSession implements InstantLoad{

	public $techits = 0;

	public function load() : void{
		parent::load();
		$db = PvP::getInstance()->getDatabase("here");
		$xuid = $this->getXuid();

		$stmt = $db->prepare("SELECT techits FROM techits WHERE xuid=?");
		$stmt->bind_param("i", $xuid);
		$stmt->bind_result($techits);
		if($stmt->execute()){
			$stmt->fetch();
		}
		$stmt->close();

		if($techits == null) return;

		$this->techits = $techits;
	}

	public function getTechits() : int{
		return $this->techits;
	}

	public function setTechits(int $value) : void{
		$this->techits = $value;
		$this->setChanged();
	}

	public function addTechits(int $value = 1) : void{
		$this->techits += $value;
		$this->setChanged();
	}

	public function takeTechits(int $value = 1) : void{
		$this->techits = max(0, $this->techits - $value);
		$this->setChanged();
	}

	public function save() : void{
		if(!$this->isChanged()) return;

		$db = PvP::getInstance()->getDatabase("here");
		$xuid = $this->getXuid();
		$techits = $this->getTechits();

		$stmt = $db->prepare("INSERT INTO techits(xuid, techits) VALUES(?, ?) ON DUPLICATE KEY UPDATE techits=VALUES(techits)");
		$stmt->bind_param("ii", $xuid, $techits);
		$stmt->execute();
		$stmt->close();

		$this->setChanged(false);
	}

}