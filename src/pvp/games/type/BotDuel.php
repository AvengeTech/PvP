<?php

namespace pvp\games\type;

use pocketmine\entity\{
	Location,
	Skin
};
use pocketmine\player\Player;

use pvp\games\entity\PracticeBot;
use pvp\games\entity\NodebuffBot;

use core\utils\TextFormat;
use pvp\games\entity\Bot;
use pvp\games\entity\type\BotSettings;

class BotDuel extends Duel implements PracticeGame {

	public ?NodebuffBot $bot = null;

	public function getName(): string {
		return "botduel";
	}

	public function getInstructions(): string {
		return "Kill dat ugly ahhhh bot, he stole your lunch money";
	}

	public function tick(): void {
		parent::tick();
		if ($this->isStarted() && !$this->isEnded()) {
			if ($this->getBot()->isClosed()) {
				$this->endPhase();
			}
		}
		if ($this->isEnded() && !($this->getBot()->isClosed() || $this->getBot()->isFlaggedForDespawn())) {
			$this->getBot()->flagForDespawn();
		}
	}

	public function processSuicide(Player $dead): void {
		$dscore = $this->getPlayer($dead);
		$dscore->addScore("deaths");

		$this->eliminate($dead);
	}

	public function startCountdown(bool $newRound = false): void {
		parent::startCountdown();

		$pa = array_values($this->getPlayers());
		$player = array_shift($pa);
		$player = $player->getPlayer();

		/*$bot = new PracticeBot(
			Location::fromObject($this->getArena()->getSpawnpoint(1), $this->getArena()->getWorld()),
			new Skin("Standard_Custom", file_get_contents("/[REDACTED]/skins/noob.dat"), "", "geometry.humanoid.custom")
		);
		$bot->setTargetEntity($player);
		$bot->setGame($this);*/

		$bot = new NodebuffBot(Location::fromObject($this->getArena()->getSpawnpoint(1), $this->getArena()->getWorld()), $player, BotSettings::create(BotSettings::DEFAULT_REACH, BotSettings::DEFAULT_STRAFE, TextFormat::AQUA . "Jefflin Jr", $this));
		$bot->setSkin(new Skin("Standard_Custom", file_get_contents("/[REDACTED]/skins/noob.dat"), "", "geometry.humanoid.custom"));
		$bot->setNoClientPredictions(true);
		$bot->setNameTagAlwaysVisible(true);
		$bot->spawnToAll();

		$this->setBot($bot);
	}

	public function start(): void {
		parent::start();
		//$this->getBot()->started = true;
		$this->getBot()->setNoClientPredictions(false);
	}

	public function draw(array $scores): void {
		foreach ($this->getViewers() as $viewer) {
			$viewer->sendMessage(TextFormat::RI . "The bot duel was lost!");
		}
	}

	public function getBot(): ?Bot {
		return $this->bot;
	}

	public function setBot(Bot $bot): void {
		$this->bot = $bot;
	}
}
