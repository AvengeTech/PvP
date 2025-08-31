<?php namespace pvp\challenges;

use pocketmine\player\Player;
use pocketmine\item\Item;
use pocketmine\block\Block;

use pocketmine\event\Listener;
use pocketmine\event\player\{
	PlayerInteractEvent,
	PlayerExperienceChangeEvent
};
use pocketmine\event\block\{
	BlockPlaceEvent,
	BlockBreakEvent
};
use pocketmine\event\inventory\{
	InventoryPickupItemEvent,
	CraftItemEvent
};

use skyblock\SkyBlock;
use skyblock\challenges\ChallengeData as CD;

use skyblock\fishing\event\FishingCatchEvent;
use skyblock\islands\event\IslandUpgradeEvent;
use skyblock\shop\event\{
	ShopBuyEvent,
	ShopSellEvent
};
use skyblock\spawners\event\{
	SpawnerKillEvent,
	SpawnerUpgradeEvent
};
use skyblock\event\AutoInventoryCollectEvent;
use skyblock\koth\event\KothWinEvent;
use skyblock\crates\event\{
	CrateWinEvent,
	KeyGiveEvent
};
use skyblock\enchantments\event\RepairItemEvent;

class ChallengeListener implements Listener{

	public $plugin;
	public $challenges;

	public $tapCooldown = [];

	public function __construct(SkyBlock $plugin, Challenges $challenges){
		$this->plugin = $plugin;
		$this->challenges = $challenges;
	}

	public function setTapCooldown(Player $player) : void{
		$this->tapCooldown[$player->getXuid()] = time();
	}

	public function hasTapCooldown(Player $player) : bool{
		return isset($this->tapCooldown[$player->getXuid()]) && $this->tapCooldown[$player->getXuid()] == time();
	}

	public function onInteract(PlayerInteractEvent $e){
		if($e->isCancelled()) return;
		$player = $e->getPlayer();
		$block = $e->getBlock();

		if($e->getAction() == PlayerInteractEvent::RIGHT_CLICK_BLOCK && !$this->hasTapCooldown($player)){
			$csession = $this->challenges->getSessionManager()->getSession($player);
			if($csession->hasLevelUnlocked(4)){
				$lvl = $csession->getLevelSession(4);

				$lvl->getChallengeById(CD::BONEMEAL_SAPLINGS)->onEvent($e, $player);
			}
			if($csession->hasLevelUnlocked(5)){
				$lvl = $csession->getLevelSession(5);

				$lvl->getChallengeById(CD::GROW_BIRCH_SAPLINGS)->onEvent($e, $player);
			}
			$this->setTapCooldown($player);
		}
	}

	public function onExpChange(PlayerExperienceChangeEvent $e){
		if($e->isCancelled()) return;
		$player = $e->getEntity();
		if($player instanceof Player){
			$csession = $this->challenges->getSessionManager()->getSession($player);
			if($csession->hasLevelUnlocked(11)){
				$lvl = $csession->getLevelSession(11);
				$lvl->getChallengeById(CD::LEVEL_UP)->onEvent($e, $player);
			}
		}
	}

	public function onPlace(BlockPlaceEvent $e){
		if($e->isCancelled()) return;
		$player = $e->getPlayer();
		$block = $e->getBlock();

		$csession = $this->challenges->getSessionManager()->getSession($player);
		if($csession->hasLevelUnlocked(1)){
			$lvl = $csession->getLevelSession(1);
			$lvl->getChallengeById(CD::SUGARCANE_PLANT)->onEvent($e, $player);
			$lvl->getChallengeById(CD::CACTUS_PLANT)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(2)){
			$lvl = $csession->getLevelSession(2);

			$lvl->getChallengeById(CD::PLANT_MELON)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(3)){
			$lvl = $csession->getLevelSession(3);

			$lvl->getChallengeById(CD::PLANT_WHEAT)->onEvent($e, $player);
			$lvl->getChallengeById(CD::PLANT_OAK_SAPLING)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(5)){
			$lvl = $csession->getLevelSession(5);

			$lvl->getChallengeById(CD::PLANT_PUMPKINS)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(6)){
			$lvl = $csession->getLevelSession(6);

			$lvl->getChallengeById(CD::PLANT_JUNGLE_SAPLINGS)->onEvent($e, $player);
			$lvl->getChallengeById(CD::PLACE_VINES)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(7)){
			$lvl = $csession->getLevelSession(7);

			$lvl->getChallengeById(CD::PLANT_ACACIA_SAPLINGS)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(9)){
			$lvl = $csession->getLevelSession(9);

			$lvl->getChallengeById(CD::PLANT_DARK_OAK_SAPLINGS)->onEvent($e, $player);
		}
	}

	public function onBreak(BlockBreakEvent $e){
		if($e->isCancelled()) return;
		$player = $e->getPlayer();

		$csession = $this->challenges->getSessionManager()->getSession($player);
		if($csession->hasLevelUnlocked(2)){
			$lvl = $csession->getLevelSession(2);

			$lvl->getChallengeById(CD::BREAK_WOOD_1)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(3)){
			$lvl = $csession->getLevelSession(3);

			$lvl->getChallengeById(CD::BREAK_WOOD_2)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(10)){
			$lvl = $csession->getLevelSession(10);

			$lvl->getChallengeById(CD::MINE_OBSIDIAN)->onEvent($e, $player);
		}
	}

	public function onPickup(InventoryPickupItemEvent $e){
		if($e->isCancelled()) return;
		$inventory = $e->getInventory();
		$player = $inventory->getHolder();
		$item = $e->getItemEntity()->getItem();

		if($player instanceof Player){
			$csession = $this->challenges->getSessionManager()->getSession($player);

			if($csession->hasLevelUnlocked(3)){
				$lvl = $csession->getLevelSession(3);

				$lvl->getChallengeById(CD::COLLECT_CACTUS)->onEvent($e, $player);
			}
			if($csession->hasLevelUnlocked(4)){
				$lvl = $csession->getLevelSession(4);

				$lvl->getChallengeById(CD::COLLECT_SPRUCE_1)->onEvent($e, $player);
			}
			if($csession->hasLevelUnlocked(5)){
				$lvl = $csession->getLevelSession(5);

				$lvl->getChallengeById(CD::COLLECT_SPRUCE_2)->onEvent($e, $player);
				$lvl->getChallengeById(CD::COLLECT_COAL)->onEvent($e, $player);
				$lvl->getChallengeById(CD::COLLECT_JUNGLE_1)->onEvent($e, $player);
			}
			if($csession->hasLevelUnlocked(6)){
				$lvl = $csession->getLevelSession(6);

				$lvl->getChallengeById(CD::COLLECT_JUNGLE_2)->onEvent($e, $player);
				$lvl->getChallengeById(CD::COLLECT_ACACIA_1)->onEvent($e, $player);
				$lvl->getChallengeById(CD::COLLECT_POTATOES)->onEvent($e, $player);
			}
			if($csession->hasLevelUnlocked(7)){
				$lvl = $csession->getLevelSession(7);

				$lvl->getChallengeById(CD::COLLECT_ACACIA_2)->onEvent($e, $player);
				$lvl->getChallengeById(CD::COLLECT_DARK_OAK_1)->onEvent($e, $player);
				$lvl->getChallengeById(CD::COLLECT_LEAVES)->onEvent($e, $player);
			}
			if($csession->hasLevelUnlocked(8)){
				$lvl = $csession->getLevelSession(8);

				$lvl->getChallengeById(CD::COLLECT_DARK_OAK_2)->onEvent($e, $player);
			}
			if($csession->hasLevelUnlocked(11)){
				$lvl = $csession->getLevelSession(11);

				$lvl->getChallengeById(CD::COLLECT_ROTTEN_FLESH)->onEvent($e, $player);
				$lvl->getChallengeById(CD::COLLECT_EMERALD)->onEvent($e, $player);
				$lvl->getChallengeById(CD::COLLECT_PRISMARINE_SHARDS)->onEvent($e, $player);
			}
			if($csession->hasLevelUnlocked(15)){
				$lvl = $csession->getLevelSession(15);

				$lvl->getChallengeById(CD::COLLECT_WITHER_SKULL)->onEvent($e, $player);
			}
		}
	}

	public function onAutoPickup(AutoInventoryCollectEvent $e){
		$player = $e->getPlayer();
		$item = $e->getItem()->getItem();

		if($player instanceof Player){
			$csession = $this->challenges->getSessionManager()->getSession($player);

			if($csession->hasLevelUnlocked(3)){
				$lvl = $csession->getLevelSession(3);

				$lvl->getChallengeById(CD::COLLECT_CACTUS)->onEvent($e, $player);
			}
			if($csession->hasLevelUnlocked(4)){
				$lvl = $csession->getLevelSession(4);

				$lvl->getChallengeById(CD::COLLECT_SPRUCE_1)->onEvent($e, $player);
			}
			if($csession->hasLevelUnlocked(5)){
				$lvl = $csession->getLevelSession(5);

				$lvl->getChallengeById(CD::COLLECT_PUMPKINS)->onEvent($e, $player);
				$lvl->getChallengeById(CD::COLLECT_SPRUCE_2)->onEvent($e, $player);
				$lvl->getChallengeById(CD::COLLECT_COAL)->onEvent($e, $player);
				$lvl->getChallengeById(CD::COLLECT_JUNGLE_1)->onEvent($e, $player);
			}
			if($csession->hasLevelUnlocked(6)){
				$lvl = $csession->getLevelSession(6);

				$lvl->getChallengeById(CD::COLLECT_JUNGLE_2)->onEvent($e, $player);
				$lvl->getChallengeById(CD::COLLECT_ACACIA_1)->onEvent($e, $player);
				$lvl->getChallengeById(CD::COLLECT_POTATOES)->onEvent($e, $player);
			}
			if($csession->hasLevelUnlocked(7)){
				$lvl = $csession->getLevelSession(7);

				$lvl->getChallengeById(CD::COLLECT_ACACIA_2)->onEvent($e, $player);
				$lvl->getChallengeById(CD::COLLECT_DARK_OAK_1)->onEvent($e, $player);
				$lvl->getChallengeById(CD::COLLECT_LEAVES)->onEvent($e, $player);
			}
			if($csession->hasLevelUnlocked(8)){
				$lvl = $csession->getLevelSession(8);

				$lvl->getChallengeById(CD::COLLECT_DARK_OAK_2)->onEvent($e, $player);
			}
			if($csession->hasLevelUnlocked(11)){
				$lvl = $csession->getLevelSession(11);

				$lvl->getChallengeById(CD::COLLECT_ROTTEN_FLESH)->onEvent($e, $player);
				$lvl->getChallengeById(CD::COLLECT_EMERALD)->onEvent($e, $player);
				$lvl->getChallengeById(CD::COLLECT_PRISMARINE_SHARDS)->onEvent($e, $player);
			}
			if($csession->hasLevelUnlocked(15)){
				$lvl = $csession->getLevelSession(15);

				$lvl->getChallengeById(CD::COLLECT_WITHER_SKULL)->onEvent($e, $player);
			}
		}
	}

	public function onCraft(CraftItemEvent $e){
		if($e->isCancelled()) return;

		$player = $e->getPlayer();
		$csession = $this->challenges->getSessionManager()->getSession($player);
		if($csession->hasLevelUnlocked(1)){
			$lvl = $csession->getLevelSession(1);

			$lvl->getChallengeById(CD::FURNACE_CRAFT)->onEvent($e, $player);
			$lvl->getChallengeById(CD::BED_CRAFT)->onEvent($e, $player);
			$lvl->getChallengeById(CD::TRAP_CRAFT)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(2)){
			$lvl = $csession->getLevelSession(2);

			$lvl->getChallengeById(CD::COBBLESTONE_OAK_STAIR)->onEvent($e, $player);
			$lvl->getChallengeById(CD::BUTTON_CRAFT)->onEvent($e, $player);
			$lvl->getChallengeById(CD::COBBLESTONE_SLAB_CRAFT)->onEvent($e, $player);
			$lvl->getChallengeById(CD::CRAFT_BRICKS)->onEvent($e, $player);
			$lvl->getChallengeById(CD::CRAFT_PANES)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(3)){
			$lvl = $csession->getLevelSession(3);

			$lvl->getChallengeById(CD::CRAFT_CHEST)->onEvent($e, $player);
			$lvl->getChallengeById(CD::CRAFT_FENCE)->onEvent($e, $player);
			$lvl->getChallengeById(CD::CRAFT_GATE)->onEvent($e, $player);
			$lvl->getChallengeById(CD::CRAFT_TORCH)->onEvent($e, $player);
			$lvl->getChallengeById(CD::CRAFT_SIGN)->onEvent($e, $player);
			$lvl->getChallengeById(CD::CRAFT_GREEN_WOOL)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(4)){
			$lvl = $csession->getLevelSession(4);

			$lvl->getChallengeById(CD::CRAFT_BREAD)->onEvent($e, $player);
			$lvl->getChallengeById(CD::CRAFT_BEETROOT_SOUP)->onEvent($e, $player);
			$lvl->getChallengeById(CD::CRAFT_STONE_BRICKS)->onEvent($e, $player);
			$lvl->getChallengeById(CD::CRAFT_WHITE_WOOL)->onEvent($e, $player);
			$lvl->getChallengeById(CD::CRAFT_PAINTINGS)->onEvent($e, $player);
			$lvl->getChallengeById(CD::CRAFT_LADDERS)->onEvent($e, $player);
			$lvl->getChallengeById(CD::CRAFT_BOW)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(5)){
			$lvl = $csession->getLevelSession(5);

			$lvl->getChallengeById(CD::CRAFT_POLISHED_GRANITE)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(6)){
			$lvl = $csession->getLevelSession(6);

			$lvl->getChallengeById(CD::CRAFT_POLISHED_DIORITE)->onEvent($e, $player);
			$lvl->getChallengeById(CD::CRAFT_CARPET)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(7)){
			$lvl = $csession->getLevelSession(7);

			$lvl->getChallengeById(CD::CRAFT_IRON_BLOCKS)->onEvent($e, $player);
			$lvl->getChallengeById(CD::CRAFT_IRON_NUGGETS)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(8)){
			$lvl = $csession->getLevelSession(8);

			$lvl->getChallengeById(CD::CRAFT_SNOW_BLOCKS)->onEvent($e, $player);
			$lvl->getChallengeById(CD::CRAFT_GOLD_NUGGETS)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(9)){
			$lvl = $csession->getLevelSession(9);

			$lvl->getChallengeById(CD::CRAFT_REDSTONE_BLOCKS)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(10)){
			$lvl = $csession->getLevelSession(10);

			$lvl->getChallengeById(CD::CRAFT_DIAMOND_BLOCKS)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(12)){
			$lvl = $csession->getLevelSession(12);

			$lvl->getChallengeById(CD::CRAFT_EMERALD_BLOCKS)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(13)){
			$lvl = $csession->getLevelSession(13);

			$lvl->getChallengeById(CD::CRAFT_CLOCK)->onEvent($e, $player);
		}
	}

	public function onCatch(FishingCatchEvent $e){
		$player = $e->getPlayer();
		$find = $e->getFishingFind();

		$csession = $this->challenges->getSessionManager()->getSession($player);
		if($csession->hasLevelUnlocked(10)){
			$lvl = $csession->getLevelSession(10);

			$lvl->getChallengeById(CD::COLLECT_FISH)->onEvent($e, $player);
		}
	}

	public function onBuy(ShopBuyEvent $e){
		$player = $e->getPlayer();
		$shopitem = $e->getShopItem();
		$amount = $e->getCount();

		$csession = $this->challenges->getSessionManager()->getSession($player);
		if($csession->hasLevelUnlocked(5)){
			$lvl = $csession->getLevelSession(5);

			$lvl->getChallengeById(CD::BUY_IRON_ORE_GEN)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(6)){
			$lvl = $csession->getLevelSession(6);

			$lvl->getChallengeById(CD::BUY_REDSTONE_ORE_GEN)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(7)){
			$lvl = $csession->getLevelSession(7);

			$lvl->getChallengeById(CD::BUY_QUARTZ_BLOCK)->onEvent($e, $player);
			$lvl->getChallengeById(CD::BUY_NETHERBRICK_BLOCK)->onEvent($e, $player);
			$lvl->getChallengeById(CD::BUY_LAPIS_ORE_GEN)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(8)){
			$lvl = $csession->getLevelSession(8);

			$lvl->getChallengeById(CD::BUY_BOOKSHELVES)->onEvent($e, $player);
			$lvl->getChallengeById(CD::BUY_GOLD_ORE_GEN)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(10)){
			$lvl = $csession->getLevelSession(10);

			$lvl->getChallengeById(CD::BUY_END_STONE)->onEvent($e, $player);
			$lvl->getChallengeById(CD::BUY_DIAMOND_ORE_GEN)->onEvent($e, $player);
			$lvl->getChallengeById(CD::BUY_AUTOMINER)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(11)){
			$lvl = $csession->getLevelSession(11);

			$lvl->getChallengeById(CD::BUY_WHITE_STAINED_GLASS)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(12)){
			$lvl = $csession->getLevelSession(12);

			$lvl->getChallengeById(CD::BUY_PURPUR_BLOCKS)->onEvent($e, $player);
			$lvl->getChallengeById(CD::BUY_EMERALD_ORE_GEN)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(13)){
			$lvl = $csession->getLevelSession(13);

			$lvl->getChallengeById(CD::BUY_PURPUR_QUARTZ_STONE_BRICK)->onEvent($e, $player);
			$lvl->getChallengeById(CD::BUY_BLACK_WOOL_CONCRETE)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(14)){
			$lvl = $csession->getLevelSession(14);

			$lvl->getChallengeById(CD::BUY_MAGMA)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(15)){
			$lvl = $csession->getLevelSession(15);

			$lvl->getChallengeById(CD::BUY_DIMENSIONAL)->onEvent($e, $player);
			$lvl->getChallengeById(CD::BUY_ELYTRA)->onEvent($e, $player);
			$lvl->getChallengeById(CD::BUY_ARMOR_STAND)->onEvent($e, $player);
		}
	}

	public function onSell(ShopSellEvent $e){
		$player = $e->getPlayer();

		$csession = $this->challenges->getSessionManager()->getSession($player);
		if($csession->hasLevelUnlocked(2)){
			$lvl = $csession->getLevelSession(2);

			$lvl->getChallengeById(CD::SELL_CARROTS)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(7)){
			$lvl = $csession->getLevelSession(7);

			$lvl->getChallengeById(CD::SELL_LAPIS_BLOCKS)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(8)){
			$lvl = $csession->getLevelSession(8);

			$lvl->getChallengeById(CD::SELL_GOLD_BLOCKS)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(10)){
			$lvl = $csession->getLevelSession(10);

			$lvl->getChallengeById(CD::SELL_DIAMONDS)->onEvent($e, $player);
		}
	}

	public function onKillSpawner(SpawnerKillEvent $e){
		$player = $e->getPlayer();
		$mob = $e->getMob();

		$csession = $this->challenges->getSessionManager()->getSession($player);
		if($csession->hasLevelUnlocked(5)){
			$lvl = $csession->getLevelSession(5);

			$lvl->getChallengeById(CD::KILL_PIGS)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(8)){
			$lvl = $csession->getLevelSession(8);

			$lvl->getChallengeById(CD::KILL_SPIDERS)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(9)){
			$lvl = $csession->getLevelSession(9);

			$lvl->getChallengeById(CD::KILL_SKELETONS)->onEvent($e, $player);
			$lvl->getChallengeById(CD::KILL_ZOMBIES)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(10)){
			$lvl = $csession->getLevelSession(10);

			$lvl->getChallengeById(CD::KILL_HUSKS)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(11)){
			$lvl = $csession->getLevelSession(11);

			$lvl->getChallengeById(CD::KILL_BLAZES)->onEvent($e, $player);
		}
		if($csession->hasLevelUnlocked(12)){
			$lvl = $csession->getLevelSession(12);

			$lvl->getChallengeById(CD::KILL_WITHER_SKELETONS)->onEvent($e, $player);
		}
	}

	public function onIslandUpgrade(IslandUpgradeEvent $e){
		$island = $e->getIsland();
		$level = $e->getNewLevel();

		$player = $island->getOwner()->getPlayer();
		if($player instanceof Player){
			$csession = $this->challenges->getSessionManager()->getSession($player);
			$csession->getLevelSession(1)->getChallengeById(CD::ISLAND_EXPAND)->onEvent($e, $player);
		}
	}

	public function onSpawnerUpgrade(SpawnerUpgradeEvent $e){
		$player = $e->getPlayer();

		if($player instanceof Player){
			$csession = $this->challenges->getSessionManager()->getSession($player);

			if($csession->hasLevelUnlocked(5)){
				$csession->getLevelSession(5)->getChallengeById(CD::UPGRADE_MOB_SPAWNER)->onEvent($e, $player);
			}
		}
	}

	public function onKothWin(KothWinEvent $e){
		$player = $e->getPlayer();

		if($player instanceof Player){
			$csession = $this->challenges->getSessionManager()->getSession($player);

			if($csession->hasLevelUnlocked(12)){
				$csession->getLevelSession(12)->getChallengeById(CD::WIN_KOTH)->onEvent($e, $player);
			}
		}
	}

	public function onCrateWin(CrateWinEvent $e){
		$player = $e->getPlayer();

		if($player instanceof Player){
			$csession = $this->challenges->getSessionManager()->getSession($player);

			if($csession->hasLevelUnlocked(7)){
				$lvl = $csession->getLevelSession(7);

				$lvl->getChallengeById(CD::WIN_GLOWSTONE)->onEvent($e, $player);
			}

			if($csession->hasLevelUnlocked(8)){
				$lvl = $csession->getLevelSession(8);

				$lvl->getChallengeById(CD::WIN_BLUE_WOOL)->onEvent($e, $player);
				$lvl->getChallengeById(CD::WIN_GRAY_WOOL)->onEvent($e, $player);
				$lvl->getChallengeById(CD::WIN_LIME_CONCRETE)->onEvent($e, $player);
			}

			if($csession->hasLevelUnlocked(9)){
				$lvl = $csession->getLevelSession(9);

				$lvl->getChallengeById(CD::WIN_INK_SACS)->onEvent($e, $player);
				$lvl->getChallengeById(CD::WIN_LEATHER_BOOTS)->onEvent($e, $player);
				$lvl->getChallengeById(CD::WIN_YELLOW_DYE)->onEvent($e, $player);
			}

			if($csession->hasLevelUnlocked(14)){
				$lvl = $csession->getLevelSession(14);

				$lvl->getChallengeById(CD::WIN_BOTTLE_O_ENCHANTING)->onEvent($e, $player);
			}
		}
	}

	public function onKeyGive(KeyGiveEvent $e){
		$player = $e->getPlayer();
		if($player instanceof Player){
			$csession = $this->challenges->getSessionManager()->getSession($player);
			if($csession->hasLevelUnlocked(10)){
				$lvl = $csession->getLevelSession(10);

				$lvl->getChallengeById(CD::COLLECT_GOLD_KEYS)->onEvent($e, $player);
			}

		}
	}

	public function onRepairItem(RepairItemEvent $e){
		$player = $e->getPlayer();

		$csession = $this->challenges->getSessionManager()->getSession($player);

		if($csession->hasLevelUnlocked(11)){
			$lvl = $csession->getLevelSession(11);

			$lvl->getChallengeById(CD::REPAIR_ITEM)->onEvent($e, $player);
		}
	}

}