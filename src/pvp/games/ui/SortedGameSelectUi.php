<?php namespace pvp\games\ui;

use pocketmine\player\Player;

use pvp\PvP;
use pvp\PvPPlayer;
use pvp\games\type\PracticeGame;

use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class SortedGameSelectUi extends SimpleForm{

	public array $handlers = [];

	public function __construct(Player $player, public bool $practice = false){
		parent::__construct("Select " . ($practice ? "Practice " : "") . "Game", "Tap a game below to enter it's queue!");
		$this->addButton(new Button(TextFormat::GOLD . "Active Games"));
		foreach(PvP::getInstance()->getGames()->getGameManager()->getGameHandlers() as $handler){
			if($practice === $handler->getBaseGame() instanceof PracticeGame){
				$this->handlers[] = $handler;
				$g = $handler->getBaseGame();
				$this->addButton(new Button(TextFormat::AQUA . ($g->getSettings()->hasDisplayName() ? $g->getSettings()->getDisplayName() : $g->getName()) . PHP_EOL . TextFormat::YELLOW . $handler->getTotalPlayers() . " playing"));
			}
		}
	}
	
	public function handle($response, Player $player){
		/** @var PvPPlayer $player */
		if($response == 0){
			$player->showModal(new ActiveGamesUi($this->practice));
			return;
		}
		if($player->getGameSession()->getGame()->inGame()){
			$player->sendMessage(TextFormat::RI . "You are already in a game!");
			return;
		}
		$handler = $this->handlers[$response - 1] ?? null;
		if($handler !== null){
			$handler = PvP::getInstance()->getGames()->getGameManager()->getHandler($handler);
			$handler->getAvailableGame()->addPlayer($player);
		}
	}
	
}