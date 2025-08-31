<?php namespace pvp\hud;

use pocketmine\{
	player\Player,
	Server
};

use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\utils\TextFormat;

use pvp\PvPPlayer;

class HudObject{

	public $name;

	public $color = 0;

	public function __construct(Player $player){
		$this->name = $player->getName();
	}

	public function getName() : string{
		return $this->name;
	}

	public function getPlayer() : ?Player{
		return Server::getInstance()->getPlayerExact($this->getName());
	}

	public function getPercentage() : float {
		/** @var PvPPlayer $player */
		$player = $this->getPlayer();

		$techits = $player->getTechits();
		$session = $player->getGameSession()->getLevels();
		$xp = $session->getExperience();
		$needed = $session->getExperienceNeeded($session->getLevel());

		//return 1; //todo: check how much XP needed to level up and use that
		return (($total = $xp / $needed) > 1 ? 1 : $total);
	}

	public function getText() : string {
		/** @var PvPPlayer $player */
		$player = $this->getPlayer();

		$session = $player->getGameSession()->getLevels();
		return TextFormat::YELLOW . "Level " . $session->getLevel() . TextFormat::GRAY . " | " .
			TextFormat::AQUA . number_format($player->getTechits());
	}

	public function send() : void{
		$player = $this->getPlayer();
		if($player === null) return;

		$pk = new BossEventPacket();
		$pk->bossActorUniqueId = $player->getId();
		$pk->eventType = BossEventPacket::TYPE_SHOW;
		$pk->healthPercent = $this->getPercentage();
		$pk->title = $this->getText();
		$pk->darkenScreen = false;
		$pk->overlay = 0;
		$pk->color = mt_rand(0, 6);

		$player->getNetworkSession()->sendDataPacket($pk);
	}

	public function update() : void{
		$player = $this->getPlayer();
		if($player === null) return;

		$pk = new BossEventPacket();
		$pk->bossActorUniqueId = $player->getId();
		$pk->eventType = BossEventPacket::TYPE_TITLE;
		$pk->title = $this->getText();
		$player->getNetworkSession()->sendDataPacket($pk);

		$pk = new BossEventPacket();
		$pk->bossActorUniqueId = $player->getId();
		$pk->eventType = BossEventPacket::TYPE_HEALTH_PERCENT;
		$pk->healthPercent = $this->getPercentage();
		$player->getNetworkSession()->sendDataPacket($pk);

		$pk = new BossEventPacket();
		$pk->bossActorUniqueId = $player->getId();
		$pk->eventType = BossEventPacket::TYPE_TEXTURE;
		$pk->darkenScreen = false;
		$pk->overlay = 0;
		$pk->color = ++$this->color > 6 ? $this->color = 0 : $this->color;
		$player->getNetworkSession()->sendDataPacket($pk);
	}

}