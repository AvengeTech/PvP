<?php namespace pvp\leaderboards\types;

use pocketmine\Server;
use pocketmine\world\{
	Position,
	World
};

class NonErrorPosition extends Position{ //shit hacky butt fukkit

	public function getWorld() : World{
		try{
			return parent::getWorld();
		}catch(\Error $error){
			return Server::getInstance()->getWorldManager()->getDefaultWorld();
		}
	}
}