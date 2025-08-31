<?php namespace pvp\combat;

use pocketmine\Server;
use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\{
	AddActorPacket,
	types\LevelSoundEvent
};
use pocketmine\world\Position;

use pvp\PvP;
use pvp\PvPPlayer;

use core\utils\{
	GenericSound
};

class Combat{

	public function __construct(public PvP $plugin){}

	public function removeLogs() : void{
		foreach(Server::getInstance()->getOnlinePlayers() as $player){
			/** @var PvPPlayer $player */
			$player->getGameSession()?->getCombat()->getCombatMode()->reset(false);
		}
	}

	public function close() : void{
		$this->removeLogs();
	}

	public function strikeLightning(Position $pos, ?Entity $entity = null){
		$pk = new AddActorPacket();
		$pk->type = "minecraft:lightning_bolt";
		$pk->entityRuntimeId = Entity::nextRuntimeId();
		$pk->position = $pos->asVector3();
		$pk->yaw = $pk->pitch = 0;
		if($entity instanceof Entity){
			$players = $entity->getViewers();
		}else{
			$players = $pos->getWorld()->getPlayers();
		}
		foreach($players as $p){
			$p->getNetworkSession()->sendDataPacket($pk);
		}
		$pos->getWorld()->addSound($pos, new GenericSound($pos, LevelSoundEvent::THUNDER));
	}

}