<?php namespace pvp\games\ui;

use pocketmine\player\Player;

use pvp\games\type\Game;
use pvp\kits\KitVoteLibrary;

use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class KitVoteUi extends SimpleForm{

	public array $kits = [];

	public function __construct(Player $player, public Game $game){
		parent::__construct("Kit Vote", "Select the kit you'd like to use for this game!");
		foreach(($kl = $game->getSettings()->getKitLibrary())->getKits() as $kit){
			/** @var KitVoteLibrary $kl */
			$this->kits[] = $kit;
			$this->addButton(new Button($kit->getName() . PHP_EOL . $kl->getVoteTotal($kit) . " votes"));
		}
	}

	public function handle($response, Player $player){
		$game = $this->game->get();
		if($game === null){
			$player->sendMessage(TextFormat::RI . "This game has ended!");
			return;
		}
		$kit = $this->kits[$response] ?? null;
		if($kit === null) return;
		if($game->isStarted()){
			$player->sendMessage(TextFormat::RI . "Kit vote has already ended!");
			return;
		}
		$game->getSettings()->getKitLibrary()->vote($player, $kit);
		$player->sendMessage(TextFormat::GI . "You voted for the " . TextFormat::YELLOW . $kit->getName() . TextFormat::GRAY . " kit!");
	}

}