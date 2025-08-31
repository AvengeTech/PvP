<?php

namespace pvp\hotbar;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

use pvp\PvP;
use pvp\PvPPlayer;
use pvp\arenas\ui\{
	ArenaSelectUi,
	ArenaSpectateUi
};
use pvp\enchantments\ItemData;
use pvp\games\type\Game;
use pvp\games\ui\{
	ArenaVoteUi,
	GameSelectUi,
	GameSpectateUi,
	KitSelectUi,
	KitVoteUi,
};
use pvp\hotbar\utils\HotbarHandler;
use pvp\kits\{
	KitVoteLibrary
};

use core\Core;
use core\rules\uis\RulesUi;
use core\settings\ui\SettingsUi;
use core\utils\TextFormat;

class Hotbar {

	public array $hotbars = [];

	public function __construct(public PvP $plugin) {
		$this->setup();
	}

	public function setup(): void {
		$this->hotbars["spawn"] = new HotbarHandler("spawn", 4, [
			0 => VanillaItems::REDSTONE_DUST()->setCustomName(TextFormat::RESET . TextFormat::RED . "Toggle PvP"),
			1 => VanillaItems::FEATHER()->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Toggle Flight"),

			3 => VanillaItems::TOTEM()->setCustomName(TextFormat::RESET . TextFormat::YELLOW . "Arenas"),
			4 => VanillaItems::COMPASS()->setCustomName(TextFormat::RESET . TextFormat::GREEN . "Games"),
			5 => VanillaItems::SLIMEBALL()->setCustomName(TextFormat::RESET . TextFormat::AQUA . "Practice"),

			7 => VanillaItems::BOOK()->setCustomName(TextFormat::RESET . TextFormat::LIGHT_PURPLE . "Rules"),
			8 => VanillaItems::PAPER()->setCustomName(TextFormat::RESET . TextFormat::DARK_PURPLE . "Settings"),
		], function (Player $player, int $slot) {
			/** @var PvPPlayer $player */
			switch ($slot) {
				default:
					break;
				case 0:
					$player->setKit("spawn_pvp");
					$player->setHotbar("spawn_pvp", false);
					$player->setSpawnPvP();
					$player->setFlightMode(false);
					$player->sendMessage(TextFormat::YI . "Enabled spawn PvP");
					break;
				case 1:
					if (!$player->hasRank()) {
						$player->sendMessage(TextFormat::RI . "You must have a rank to fly at spawn! Purchase one at " . TextFormat::YELLOW . "store.avengetech.net");
						return;
					}
					$player->setFlightMode(!($fm = $player->inFlightMode()));
					$player->sendMessage(TextFormat::GI . "You are " . ($fm ? "no longer" : "now") . " in flight mode");
					break;

				case 3:
					$player->showModal(new ArenaSelectUi($player));
					break;
				case 4:
					$player->showModal(new GameSelectUi($player));
					break;
				case 5:
					$player->showModal(new GameSelectUi($player, true));
					break;

				case 7:
					$player->showModal(new RulesUi(($pl = Core::getInstance()), $pl->getRules()->getRuleManager()));
					break;
				case 8:
					$player->showModal(new SettingsUi());
					break;
			}
		}, function (Player $player, int $runs) {
			//$player->sendTip(TextFormat::AQUA . "Your hotbar is ticking!");
		});

		$ench = $this->plugin->getEnchantments();

		$sword = VanillaItems::DIAMOND_SWORD();
		$data = new ItemData($sword);
		$data->addEnchantment($ench->getEnchantmentByName("oof")->getEnchantment(), 1);
		$data->setEffectId($ench->getEffects()->getEffectByName("R.I.P.")->getId());
		$this->hotbars["spawn_pvp"] = new HotbarHandler("spawn_pvp", 0, [
			0 => $data->getItem(),
			8 => VanillaItems::SUGAR()->setCustomName(TextFormat::RESET . TextFormat::RED . "Disable spawn PvP")
		], function (Player $player, int $slot) {
			/** @var PvPPlayer $player */
			if ($slot == 8) {
				$session = $player->getGameSession()->getCombat();
				if ($session->getCombatMode()->inCombat()) {
					$player->sendMessage(TextFormat::RI . "You cannot disable spawn PvP while in combat!");
					return;
				}
				$player->setKit();
				$player->setHotbar("spawn");
				$player->setSpawnPvP(false);
				$player->sendMessage(TextFormat::YI . "Disabled spawn PvP");
			}
		}, function (Player $player, int $runs) {
		});

		$this->hotbars["arena_spectator"] = new HotbarHandler("arena_spectator", 0, [
			0 => VanillaItems::COMPASS()->setCustomName(TextFormat::RESET . TextFormat::GREEN . "Teleporter"),
			8 => VanillaItems::REDSTONE_DUST()->setCustomName(TextFormat::RESET . TextFormat::RED . "Leave"),
		], function (Player $player, int $slot) {
			/** @var PvPPlayer $player */
			$as = $player->getGameSession()->getArenas();
			if (!$as->inArena()) return;
			$arena = $as->getArena();
			switch ($slot) {
				case 0:
					$player->showModal(new ArenaSpectateUi($arena));
					break;
				case 8:
					$as->setArena();
					$player->gotoSpawn();
					break;
			}
		}, function (Player $player, int $runs) {
		});

		$this->hotbars["game_lobby"] = new HotbarHandler("game_lobby", 4, [
			0 => VanillaItems::PAPER()->setCustomName(TextFormat::RESET . TextFormat::AQUA . "Vote for Map"),
			4 => VanillaItems::BOOK()->setCustomName(TextFormat::RESET . TextFormat::YELLOW . "Select Kit"),
			8 => VanillaItems::REDSTONE_DUST()->setCustomName(TextFormat::RESET . TextFormat::RED . "Leave"),
		], function (Player $player, int $slot) {
			/** @var PvPPlayer $player */
			$gs = $player->getGameSession()->getGame();
			if (!$gs->inGame()) return;
			$game = $gs->getGame();
			switch ($slot) {
				case 0:
					if ($game->getStatus() > Game::GAME_LOBBY_COUNTDOWN) {
						$player->sendMessage(TextFormat::RI . "Map vote has already ended");
						break;
					}
					$player->showModal(new ArenaVoteUi($player, $game));
					break;
				case 4:
					$kitLibrary = $game->getSettings()->getKitLibrary();
					if ($kitLibrary !== null && count($kitLibrary->getKits()) > 1) {
						if ($kitLibrary instanceof KitVoteLibrary) {
							if (($game->getStatus() > Game::GAME_LOBBY_COUNTDOWN && $game->getRound() === 1) || $game->pastCountdown()) {
								$player->sendMessage(TextFormat::RI . "Kit vote has already ended");
								break;
							}
							$player->showModal(new KitVoteUi($player, $game));
						} else {
							if (($game->getStatus() > Game::GAME_LOBBY_COUNTDOWN && $game->getRound() === 1) || $game->pastCountdown()) {
								$player->sendMessage(TextFormat::RI . "You can no longer select a kit");
								break;
							}
							$player->showModal(new KitSelectUi($player, $game));
						}
					}
					break;
				case 8:
					$game->removePlayer($player);
					break;
			}
		}, function (Player $player, int $runs) {
			/** @var PvPPlayer $player */
			if ($runs % 2 === 0) {
				$gs = $player->getGameSession()->getGame();
				if (!$gs->inGame()) return;
				$game = $gs->getGame();
				$kitLibrary = $game->getSettings()->getKitLibrary();
				if (
					$kitLibrary === null || count($kitLibrary->getKits()) <= 1
				) {
					$player->getInventory()->setItem(4, VanillaBlocks::AIR()->asItem());
				}
			}
		});

		$this->hotbars["game_end"] = new HotbarHandler("game_end", 0, [
			0 => VanillaItems::BOOK()->setCustomName(TextFormat::RESET . TextFormat::YELLOW . "Game Stats"),
			8 => VanillaItems::REDSTONE_DUST()->setCustomName(TextFormat::RESET . TextFormat::RED . "Leave"),
		], function (Player $player, int $slot) {
			/** @var PvPPlayer $player */
			$gs = $player->getGameSession()->getGame();
			if (!$gs->inGame()) return;
			$game = $gs->getGame();
			switch ($slot) {
				case 0:
					$player->sendMessage(TextFormat::RED . "Coming soon");
					//match stats!
					break;
				case 7:

					break;
				case 8:
					$game->removePlayer($player);
					break;
			}
		}, function (Player $player, int $runs) {
		});
		$this->hotbars["game_spectator"] = new HotbarHandler("game_spectator", 0, [
			0 => VanillaItems::BOOK()->setCustomName(TextFormat::RESET . TextFormat::YELLOW . "Game Stats"),
			1 => VanillaItems::COMPASS()->setCustomName(TextFormat::RESET . TextFormat::GREEN . "Teleporter"),
			8 => VanillaItems::REDSTONE_DUST()->setCustomName(TextFormat::RESET . TextFormat::RED . "Leave"),
		], function (Player $player, int $slot) {
			/** @var PvPPlayer $player */
			$gs = $player->getGameSession()->getGame();
			if (!$gs->inGame()) return;
			$game = $gs->getGame();
			switch ($slot) {
				case 0:
					$player->sendMessage(TextFormat::RED . "Coming soon");
					//match stats! (if they were eliminated or game ended)
					break;
				case 1:
					$player->showModal(new GameSpectateUi($game));
					break;
				case 8:
					$game->removeSpectator($player);
					break;
			}
		}, function (Player $player, int $runs) {
			/** @var PvPPlayer $player */
			$gs = $player->getGameSession()->getGame();
			if ($gs->inGame() && $gs->getGame()->isEnded()) {
				$player->getInventory()->setItem(1, VanillaBlocks::AIR()->asItem());
			}
		});
	}

	public function getHotbar(string $name): ?HotbarHandler {
		return $this->hotbars[$name] ?? null;
	}
}
