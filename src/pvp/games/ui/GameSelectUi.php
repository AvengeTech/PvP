<?php namespace pvp\games\ui;

use pocketmine\player\Player;

use pvp\PvP;
use pvp\PvPPlayer;
use pvp\games\type\PracticeGame;

use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class GameSelectUi extends SimpleForm{

	public bool $h;
	public array $handlers = [];

	public function __construct(Player $player, public bool $practice = false, array $handlers = []){
		parent::__construct("Select " . ($practice ? "Practice " : "") . "Game", "Select which game you'd like to join!");
		$h = $this->h = count($handlers) === 0;
		if($h) $this->addButton(new Button(TextFormat::DARK_RED . "Active Games"));
		$handlers = $h ? PvP::getInstance()->getGames()->getGameManager()->getGameHandlers() : $handlers;
		foreach($handlers as $handler){
			if($practice === $handler->getBaseGame() instanceof PracticeGame){
				$this->handlers[] = $handler;
				$g = $handler->getBaseGame();
				$this->addButton(new Button(TextFormat::RED . ($g->getSettings()->hasDisplayName() ? $g->getSettings()->getDisplayName() : $g->getName()) . PHP_EOL . TextFormat::DARK_GRAY . $handler->getTotalPlayers() . " playing"));
			}
		}

	}
	
	public function handle($response, Player $player){
		/** @var PvPPlayer $player */
		if($this->h && $response == 0){
			$player->showModal(new ActiveGamesUi($this->practice));
			return;
		}
		if($player->getGameSession()->getGame()->inGame()){
			$player->sendMessage(TextFormat::RI . "You are already in a game!");
			return;
		}
		$handler = $this->handlers[$response - ($this->h ? 1 : 0)] ?? null;
		if($handler !== null){
			$handler = PvP::getInstance()->getGames()->getGameManager()->getHandler($handler);
			$handler->getAvailableGame()->addPlayer($player);
		}
	}
	
}