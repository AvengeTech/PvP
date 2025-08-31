<?php namespace pvp\arenas\ui;

use pocketmine\player\Player;

use pvp\arenas\Arena;
use pvp\PvPPlayer;

use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class ArenaSpectateUi extends SimpleForm{

	public array $players = [];

	public function __construct(public Arena $arena){
		parent::__construct("Teleporter", "Tap a player below to teleport to them!");
		foreach($arena->getWorld()->getPlayers() as $player){
			/** @var PvPPlayer $player */
			if($player->isLoaded() && !$player->getGameSession()->getArenas()->isSpectator()){
				$this->players[] = $player;
				$this->addButton(new Button($player->getName()));
			}
		}
	}

	public function handle($response, Player $player){
		$arena = $this->arena;
		$pl = $this->players[$response] ?? null;
		if($pl === null || $player->getPosition()->getWorld() !== $arena->getWorld()){
			$player->sendMessage(TextFormat::RI . "This player is no longer in the arena!");
			return;
		}

		$player->teleport($pl->getPosition());
		$player->sendMessage(TextFormat::GI . "Teleported to " . TextFormat::YELLOW . $pl->getName());
	}

}