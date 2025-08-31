<?php namespace pvp;

use pocketmine\event\Listener;
use pocketmine\event\player\{
	PlayerCreationEvent,
	PlayerJoinEvent,
	PlayerMoveEvent,
	PlayerQuitEvent,
	PlayerItemUseEvent,
	PlayerInteractEvent,
	PlayerDropItemEvent,
	PlayerExhaustEvent
};
use pocketmine\event\block\{
	BlockPlaceEvent,
	BlockBreakEvent,
	BlockUpdateEvent
};
use pocketmine\event\entity\{
	EntityDamageEvent,
	EntityDamageByEntityEvent,
	EntityDamageByChildEntityEvent,
	EntityShootBowEvent,
	ProjectileHitEvent,
	EntityTeleportEvent
};
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\{
	EnderPearl,
};
use pocketmine\network\mcpe\protocol\{
	PlayerActionPacket,
	types\PlayerAction
};
use pocketmine\player\GameMode;
use pocketmine\scheduler\ClosureTask;

use pvp\games\entity\PracticeBot;
use pvp\games\type\Game;

use core\Core;
use core\utils\ItemRegistry;
use core\utils\TextFormat;

use pvp\PvPPlayer as Player;

class MainListener implements Listener{

	public function __construct(public PvP $plugin){}

	/**
	 * @priority HIGHEST
	 */
	public function onCreation(PlayerCreationEvent $e){
		$e->setPlayerClass(PvPPlayer::class);
	}

	public function onJoin(PlayerJoinEvent $e){
		$this->plugin->onPreJoin($e->getPlayer());
	}

	public function onQuit(PlayerQuitEvent $e){
		//$this->plugin->onQuit($e->getPlayer());
	}

	public function onMove(PlayerMoveEvent $e){
		/** @var PvPPlayer $player */
		$player = $e->getPlayer();
		if($player->getPosition()->getY() < 0){
			if($player->inSpawn()){
				if($player->inSpawnPvP()){
					$session = $player->getGameSession()->getCombat();
					$cm = $session->getCombatMode();
					if($cm->inCombat()){
						$hplayer = $cm->getHit();
						if(!($hplayer instanceof PvPPlayer)) return;
						$hsession = $hplayer->getGameSession()->getCombat();
						$hsession->kill($player);
					}else{
						$session->suicide();
					}
				}else{
					$player->gotoSpawn();
				}
			}
		}
		foreach($this->plugin->getLeaderboards()->getLeaderboards() as $lb){
			$lb->doRenderCheck($player);
		}
	}

	/**
	 * @handleCancelled
	 */
	public function onItemUse(PlayerItemUseEvent $e){
		/** @var PvPPlayer $player */
		$player = $e->getPlayer();
		$item = $e->getItem();
		$session = $player->getGameSession()->getHotbar();
		if($player->isSpectator() && $player->hasHotbar()) $e->uncancel();
		if($session->hasHotbar()) {
			$e->cancel();
			$hotbar = $session->getHotbar();
			$hotbar->handle($player, $player->getInventory()->first($item));
		}

		if(($gs = $player->getGameSession()->getGame())->inGame()){
			$game = $gs->getGame();
			if(
				!$gs->getGame()->isStarted() ||
				$gs->getGame()->getStatus() === Game::GAME_DEATHMATCH_COUNTDOWN
			){
				$e->cancel();
				return;
			}
			if(
				$game->getSettings()->getPearlCooldown() > 0 &&
				$item instanceof EnderPearl
			){
				if($game->onPearlCooldown($player)){
					$cooldown = $game->getPearlCooldown($player);
					$player->sendMessage(TextFormat::RI . "You can use this again in " . TextFormat::YELLOW . $cooldown . TextFormat::GRAY . " second" . ($cooldown !== 1 ? "s" : "") . "!");
					$e->cancel();
					return;
				}
				$game->setPearlCooldown($player);
			}
		}

		if(($as = $player->getGameSession()->getArenas())->inArena()){
			if(
				($arena = $as->getArena())->getSettings()->getPearlCooldown() > 0 &&
				$item instanceof EnderPearl
			){
				if($arena->onPearlCooldown($player)){
					$cooldown = $arena->getPearlCooldown($player);
					$player->sendMessage(TextFormat::RI . "You can use this again in " . TextFormat::YELLOW . $cooldown . TextFormat::GRAY . " second" . ($cooldown !== 1 ? "s" : "") . "!");
					$e->cancel();
					return;
				}
				$arena->setPearlCooldown($player);
			}
		}
	}

	public function onInteract(PlayerInteractEvent $e){
		/** @var PvPPlayer $player */
		$player = $e->getPlayer();
		$item = $e->getItem();
		$action = $e->getAction();
		if(!$player->isLoaded()){
			$e->cancel();
			return;
		}
		if(
			($gs = $player->getGameSession()->getGame())->inGame() &&
			(
				!($game = $gs->getGame())->isStarted() ||
				$game->getStatus() === Game::GAME_DEATHMATCH_COUNTDOWN
			)
		){
			$e->cancel();
		}
	}

	public function onDrop(PlayerDropItemEvent $e){
		/** @var PvPPlayer $player */
		$player = $e->getPlayer();
		if(!$player->isLoaded()){
			$e->cancel();
			return;
		}
		if($player->hasHotbar()){
			$e->cancel();
			return;
		}
		if(($gs = $player->getGameSession()->getGame())->inGame()){
			$game = $gs->getGame();
			if(!$game->isStarted() || !$game->getSettings()->canDropItems()){
				$e->cancel();
			}
		}
	}

	public function onExhaust(PlayerExhaustEvent $e){
		$e->cancel();
	}

	public function onPlace(BlockPlaceEvent $e){
		/** @var PvPPlayer $player */
		$player = $e->getPlayer();
		if($player->inSpawn()){
			$e->cancel();
			return;
		}
		if(
			($gs = $player->getGameSession()->getGame())->inGame() &&
			($game = $gs->getGame())->isStarted() &&
			!$game->getSettings()->canBreakArena()
		){
			foreach($e->getTransaction()->getBlocks() as [$x, $y, $z, $block]){
				$game->addPlacedBlock($block);
			}
		}
		if(($as = $player->getGameSession()->getArenas())->inArena()){
			if(($arena = $as->getArena())->getSettings()->canBuild()){
				foreach($e->getTransaction()->getBlocks() as [$x, $y, $z, $block]){
					$arena->addPlacedBlock($block);
				}
			}else{
				$e->cancel();
			}
		}
	}

	public function onBreak(BlockBreakEvent $e){
		/** @var PvPPlayer $player */
		$player = $e->getPlayer();
		$block = $e->getBlock();
		if($player->inSpawn()){
			$e->cancel();
			return;
		}
		if(($gs = $player->getGameSession()->getGame())->inGame()){
			$game = $gs->getGame();
			if(!$game->getSettings()->canBreakArena()){
				if($game->isStarted()){
					if(!$game->wasBlockPlaced($block)){
						$player->sendMessage(TextFormat::RI . "You cannot modify the arena!");
						$e->cancel();
					}else{
						$game->removePlacedBlock($block);
						foreach($e->getDrops() as $drop){
							$player->getInventory()->addItem($drop);
						}
						$e->setDrops([]);
					}
				}else{
					$e->cancel();
				}
				$game->addPlacedBlock($block);
			}else{
				if(!$game->isStarted()){
					$e->cancel();
				}
			}
			return;
		}
		if(($as = $player->getGameSession()->getArenas())->inArena()){
			if(($arena = $as->getArena())->getSettings()->canBuild()){
				if(!$arena->isPlacedBlock($block)){
					$player->sendMessage(TextFormat::RI . "You cannot modify the arena!");
					$e->cancel();
				}
				$arena->removePlacedBlock($block);
			}else{
				$player->sendMessage(TextFormat::RI . "You cannot modify the arena!");
				$e->cancel();
			}
		}

	}

	public function onBlockUpdate(BlockUpdateEvent $e){
		//$e->cancel();
	}

	public function onEntityDamage(EntityDamageEvent $e){
		if($e->isCancelled()) return;

		$player = $e->getEntity();
		if($player instanceof Player && $player->isLoaded()){
			$gs = $player->getGameSession()->getGame();
			if($gs->inGame()){
				$game = $gs->getGame();
				if($e->getCause() === EntityDamageEvent::CAUSE_VOID){
					switch($game->getStatus()){
						case Game::GAME_WAITING:
						case Game::GAME_LOBBY_COUNTDOWN:
							$e->cancel();
							$player->teleport($game->getGameLobby()->getSpawnpoint());
							break;
						case Game::GAME_END:
							$e->cancel();
							$player->teleport($game->getArena()->getRandomSpawn());
							break;
					}
				}elseif(!$game->pastCountdown()){
					$e->cancel();
				}
			}
			$session = $player->getGameSession()->getCombat();
			if($e instanceof EntityDamageByEntityEvent){
				/** @var PvPPlayer $damager */
				$damager = $e->getDamager();
				if(
					$player->inSpawn() &&
					!$player->inSpawnPvP() &&
					$damager instanceof Player &&
					$damager->inSpawn() &&
					!$damager->inSpawnPvP()
				){
					$e->cancel();
					if($damager->isStaff()){
						$ds = $damager->getSession()->getStaff();
						if($ds->canPunchBack($player)){
							$ds->punchBack($player);
						}elseif($player->isStaff()){
							$player->getSession()->getStaff()->punch($damager);
						}
					}else{
						$player->getSession()->getStaff()->punch($damager);
					}
				}

				if($e->isCancelled()) return;

				if(!$session->canCombat($damager)){
					$e->cancel();
					return;
				}

				//if(!$e instanceof EntityDamageByChildEntityEvent){
				//	$this->plugin->getEnchantments()->process($e);
				//}

				if($dp = $damager instanceof Player){
					$dsession = $damager->getGameSession()->getCombat();
					if(!$dsession->canCombat($player)){
						if($dsession->lastReason === "ai"){
							$damager->sendMessage(TextFormat::RI . "You cannot interrupt fights in this arena!");
						}
						$e->cancel();
						return;
					}
					if($damager === $player){
						$e->cancel();
						return;
					}
				}

				if(!$e instanceof EntityDamageByChildEntityEvent){
					$this->plugin->getEnchantments()->process($e);
				}

				if($dp){
					if(($as = $player->getGameSession()->getArenas())->inArena() || $player->inSpawnPvP() || $player->getGameSession()->getGame()->inGame()){ //todo: check if in normal arena, cuz player won't need this for timer games
						$ecm = $session->getCombatMode();
						$dcm = $dsession->getCombatMode();

						$msg = TextFormat::RI . "You are now in combat mode!";
						if(!$ecm->inCombat() && !$player->getGameSession()->getGame()->inGame()){
							$player->sendMessage($msg);
						}
						if(!$dcm->inCombat() && !$damager->getGameSession()->getGame()->inGame()){
							$damager->sendMessage($msg);
						}
						$ecm->setCombat($damager, !$player->getGameSession()->getGame()->inGame());
						$dcm->setCombat($player, !$damager->getGameSession()->getGame()->inGame());

						if($as->inArena()){
							$arena = $as->getArena();
							if($arena->getSettings()->noDamage()){
								//$e->setBaseDamage(0);
								//$e->setAttackCooldown(10);
								Core::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use($player) : void{
									if($player->isConnected()) $player->setHealth($player->getMaxHealth());
								}), 1);
							}
							$as->resetCurrentCombo();
							($das = $damager->getGameSession()->getArenas())->addCurrentCombo();
							for($i = 0; $i <= 2; $i++){
								if($das->getCurrentCombo() > $das->getCombo($arena, $i)){
									$das->setCombo($arena, $das->getCurrentCombo(), $i);
								}
							}
						}
						if(($gs = $player->getGameSession()->getGame())->inGame()){
							$game = $gs->getGame();
							$game->getPlayer($player)->setScore("combo", 0);

							$dp = $game->getPlayer($damager);
							if($dp !== null){
								$dp->addScore("hits");
								$dp->addScore("combo");
								if(($combo = $dp->getScore("combo")) > $dp->getScore("highest_combo")){
									$dp->setScore("highest_combo", $combo);
								}
							}
						}
					}

					if($player->getHealth() - $e->getFinalDamage() <= 0){
						$e->cancel();
						$dsession->kill($player);
						return;
					}
				}elseif($damager instanceof PracticeBot){
					if(($gs = $player->getGameSession()->getGame())->inGame()){
						$game = $gs->getGame();
						$game->getPlayer($player)->setScore("combo", 0);
					}
				}

				if($player->getHealth() - $e->getFinalDamage() <= 0){
					$e->cancel();
					$cm = $session->getCombatMode();
					if($cm->inCombat()){
						$hsession = $cm->getHit()->getGameSession()->getCombat();
						$hsession->kill($player);
					}else{
						$session->suicide();
					}
				}
				return;
			}

			if($e->getCause() == EntityDamageEvent::CAUSE_FALL){
				$e->cancel();
				return;
				/**if($player->inSpawn()){
					$e->cancel();
					return;
				}
				if(($gs = $player->getGameSession()->getGame())->inGame()){
					$game = $gs->getGame();
					if(!$game->isStarted() || $game->isEnded()){
						$e->cancel();
						return;
					}
				}*/
			}
			if($e->getCause() == EntityDamageEvent::CAUSE_ENTITY_EXPLOSION){
				$e->cancel();
				return;
			}
			if($e->getCause() == EntityDamageEvent::CAUSE_BLOCK_EXPLOSION){
				$e->cancel();
				return;
			}

			if($player->getHealth() - $e->getFinalDamage() <= 0){
				$e->cancel();
				$cm = $session->getCombatMode();
				if($cm->inCombat()){
					$hplayer = $cm->getHit();
					if(!($hplayer instanceof Player)) return;
					$hsession = $hplayer->getGameSession()->getCombat();
					$hsession->kill($player);
				}else{
					$session->suicide();
				}
			}
		}elseif($player instanceof PracticeBot){
			if($e instanceof EntityDamageByEntityEvent){
				$damager = $e->getDamager();
				if($damager instanceof Player){
					if(($gs = $damager->getGameSession()->getGame())->inGame()){
						$game = $gs->getGame();
						$game->getPlayer($damager)->addScore("hits");
						$game->getPlayer($damager)->addScore("combo");
						if(($combo = $game->getPlayer($damager)->getScore("combo")) > $game->getPlayer($damager)->getScore("highest_combo")){
							$game->getPlayer($damager)->setScore("highest_combo", $combo);
						}
					}
				}
			}

		}
	}

	public function onShoot(EntityShootBowEvent $e){
		$player = $e->getEntity();
		if($player instanceof Player && !$e->isCancelled()){
			$ench = $this->plugin->getEnchantments();
			$ench->process($e);
		}
	}

	public function onProjHit(ProjectileHitEvent $e){
		/**$proj = $e->getEntity();
		if($e instanceof ProjectileHitEntityEvent){
			if($proj instanceof InstaShotArrow && $e->getEntityHit() instanceof Player){
				$proj->getOwningEntity()->getInventory()->addItem(VanillaItems::ARROW());
			}
		}*/
	}

	public function onChangeLevel(EntityTeleportEvent $e){
		$entity = $e->getEntity();
		$target = $e->getTo()->getWorld();
		if($entity instanceof Player){
			if($e->getFrom()->getWorld() !== $target){
				$this->plugin->getLeaderboards()->changeLevel($entity, $target->getDisplayName());
			}
			if(
				$entity->inSpawn() &&
				$target->getDisplayName() !== PvP::SPAWN_WORLD &&
				$entity->getGamemode() == GameMode::CREATIVE()
			){
				$entity->setHotbar();
			}
		}
	}

	public function onTransaction(InventoryTransactionEvent $e){
		$t = $e->getTransaction();
		/** @var PvPPlayer $player */
		$player = $t->getSource();
		$session = $player->getGameSession()->getHotbar();
		if($session->hasHotbar()){
			$e->cancel();
			return;
		}
	}

	public function onDp(DataPacketReceiveEvent $e){
		$packet = $e->getPacket();
		/** @var PvPPlayer $player */
		$player = $e->getOrigin()->getPlayer();

		if($packet instanceof PlayerActionPacket){
			if($packet->action === PlayerAction::START_GLIDE){
				$chest = $player->getArmorInventory()->getItem(1);
				if(!($chest->equals(ItemRegistry::ELYTRA()))){
					$e->cancel();
					$player->getNetworkSession()->getInvManager()->syncContents($player->getArmorInventory());
					return;
				}
				$player->setGliding(true);
				$player->networkPropertiesDirty = true;
				return;
			}
			if($packet->action === PlayerAction::STOP_GLIDE){
				$player->setGliding(false);
				$player->networkPropertiesDirty = true;
			}
			return;
		}
	}

}