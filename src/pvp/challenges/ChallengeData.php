<?php namespace pvp\challenges;

class ChallengeData{

	const DIFFICULTY_EASY = 1;
	const DIFFICULTY_NORMAL = 2;
	const DIFFICULTY_HARD = 3;

	/** Nice to look at in array ;-; */
	const LEVEL_1 = 1;
	const LEVEL_2 = 2;
	const LEVEL_3 = 3;
	const LEVEL_4 = 4;
	const LEVEL_5 = 5;
	const LEVEL_6 = 6;
	const LEVEL_7 = 7;
	const LEVEL_8 = 8;
	const LEVEL_9 = 9;
	const LEVEL_10 = 10;
	const LEVEL_11 = 11;
	const LEVEL_12 = 12;
	const LEVEL_13 = 13;
	const LEVEL_14 = 14;
	const LEVEL_15 = 15;

	const CHALLENGES = [
		self::LEVEL_1 => [
			self::ISLAND_EXPAND => [
				"name" => "Upgrade Island",
				"description" => "Upgrade your island",
				"techits" => 1000,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "IslandExpandChallenge",
				"progress" => [],
			],
			self::FURNACE_CRAFT => [
				"name" => "Hot!",
				"description" => "Craft a furnace",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "FurnaceCraftChallenge",
				"progress" => [],
			],
			self::BED_CRAFT => [
				"name" => "Bedtime",
				"description" => "Craft a bed",
				"techits" => 75,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "BedCraftChallenge",
				"progress" => [],
			],
			self::SUGARCANE_PLANT => [
				"name" => "Sugarcane farm",
				"description" => "Plant 5 Sugarcane",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "SugarcanePlantChallenge",
				"progress" => [
					"planted" => ["progress" => 0, "needed" => 5]
				],
			],
			self::CACTUS_PLANT => [
				"name" => "Prickly Prickles",
				"description" => "Plant 3 cacti",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CactusPlantChallenge",
				"progress" => [
					"planted" => ["progress" => 0, "needed" => 3]
				],
			],
			self::TRAP_CRAFT => [
				"name" => "Trap",
				"description" => "Craft 10 trapdoors",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "TrapCraftChallenge",
				"progress" => [
					"trapdoors" => ["progress" => 0, "needed" => 10],
				],
			],
		],

		self::LEVEL_2 => [
			self::BREAK_WOOD_1 => [
				"name" => "Can I AXE you a question",
				"description" => "Break 50 Oak Logs",
				"techits" => 150,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "BreakWood1Challenge",
				"progress" => [
					"logs" => ["progress" => 0, "needed" => 50],
				],
			],
			self::PLANT_MELON => [
				"name" => "Watermalone!",
				"description" => "Plant 3 watermelon seeds",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "PlantMelonChallenge",
				"progress" => [
					"planted" => ["progress" => 0, "needed" => 3],
				],
			],
			self::COBBLESTONE_OAK_STAIR => [
				"name" => "McFallen!",
				"description" => "Craft 4 cobblestone stairs and 4 oak wood stairs",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CobblestoneOakStairChallenge",
				"progress" => [
					"cobblestone" => ["progress" => 0, "needed" => 4],
					"wood" => ["progress" => 0, "needed" => 4],
				],
			],
			self::BUTTON_CRAFT => [
				"name" => "Bootons",
				"description" => "Craft 10 stone buttons",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "ButtonCraftChallenge",
				"progress" => [
					"buttons" => ["progress" => 0, "needed" => 10],
				]
			],
			self::COBBLESTONE_SLAB_CRAFT => [
				"name" => "Cobblestone Construction",
				"description" => "Craft 10 Cobblestone Slabs",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CobblestoneSlabCraftChallenge",
				"progress" => [
					"slabs" => ["progress" => 0, "needed" => 10],
				],
			],
			self::CRAFT_BRICKS => [
				"name" => "Three Little Pigs",
				"description" => "Craft a stack of Brick Blocks",
				"techits" => 150,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CraftBricksChallenge",
				"progress" => [
					"bricks" => ["progress" => 0, "needed" => 64],
				],
			],
			self::CRAFT_PANES => [
				"name" => "Windows",
				"description" => "Craft 16 glass panes",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftPanesChallenge",
				"progress" => [
					"panes" => ["progress" => 0, "needed" => 16],
				]
			],
			self::SELL_CARROTS => [
				"name" => "Feed the rabbits",
				"description" => "Sell 16 carrots",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "SellCarrotsChallenge",
				"progress" => [
					"carrots" => ["progress" => 0, "needed" => 16],
				]
			],
		],

		self::LEVEL_3 => [
			self::CRAFT_CHEST => [
				"name" => "Storage",
				"description" => "Craft 16 chests",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftChestChallenge",
				"progress" => [
					"chests" => ["progress" => 0, "needed" => 16],
				]
			],
			self::COLLECT_CACTUS => [
				"name" => "Prickling Pro",
				"description" => "Collect 64 cacti",
				"techits" => 200,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CollectCactusChallenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 64],
				],
			],
			self::PLANT_WHEAT => [
				"name" => "Novice Farmer",
				"description" => "Plant 10 Wheat Seeds",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "PlantWheatChallenge",
				"progress" => [
					"planted" => ["progress" => 0, "needed" => 10],
				],
			],
			self::CRAFT_FENCE => [
				"name" => "Fence Protection",
				"description" => "Craft 20 fences",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftFenceChallenge",
				"progress" => [
					"fences" => ["progress" => 0, "needed" => 20],
				]
			],
			self::CRAFT_GATE => [
				"name" => "Gate Protection",
				"description" => "Craft 4 fence gates",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftGateChallenge",
				"progress" => [
					"gates" => ["progress" => 0, "needed" => 4],
				]
			],
			self::PLANT_OAK_SAPLING => [
				"name" => "Tree Saver",
				"description" => "Plant 10 Oak Saplings",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "PlantOakSaplingChallenge",
				"progress" => [
					"planted" => ["progress" => 0, "needed" => 10],
				],
			],
			self::BREAK_WOOD_2 => [
				"name" => "Lumberjack Pro",
				"description" => "Break 200 Oak Logs",
				"techits" => 500,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "BreakWood2Challenge",
				"progress" => [
					"logs" => ["progress" => 0, "needed" => 200],
				],
			],
			self::CRAFT_TORCH => [
				"name" => "Lights on!",
				"description" => "Craft 32 Torches",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftTorchChallenge",
				"progress" => [
					"torches" => ["progress" => 0, "needed" => 32],
				]
			],
			self::CRAFT_SIGN => [
				"name" => "Organizing",
				"description" => "Craft 10 Signs",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftSignChallenge",
				"progress" => [
					"signs" => ["progress" => 0, "needed" => 10],
				]
			],
			self::CRAFT_GREEN_WOOL => [
				"name" => "Greens",
				"description" => "Craft 10 Green Wool",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftGreenWoolChallenge",
				"progress" => [
					"wool" => ["progress" => 0, "needed" => 10],
				]
			],
		],

		self::LEVEL_4 => [
			self::COLLECT_SPRUCE_1 => [
				"name" => "Spruce 50",
				"description" => "Collect 50 spruce logs",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CollectSpruceChallenge1",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 50],
				]
			],
			self::CRAFT_BREAD => [
				"name" => "Baguette!",
				"description" => "Craft 10 bread",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftBreadChallenge",
				"progress" => [
					"bread" => ["progress" => 0, "needed" => 10],
				]
			],
			self::CRAFT_BEETROOT_SOUP => [
				"name" => "Soup",
				"description" => "Craft 10 beetroot soup",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftBeetrootSoupChallenge",
				"progress" => [
					"soup" => ["progress" => 0, "needed" => 10],
				]
			],
			self::CRAFT_STONE_BRICKS => [
				"name" => "Stone Bricks",
				"description" => "Craft 64 stone bricks",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftStoneBricksChallenge",
				"progress" => [
					"bricks" => ["progress" => 0, "needed" => 64],
				]
			],
			self::CRAFT_WHITE_WOOL => [
				"name" => "Clouds",
				"description" => "Craft 10 white wool",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftWhiteWoolChallenge",
				"progress" => [
					"wool" => ["progress" => 0, "needed" => 10],
				]
			],
			self::CRAFT_PAINTINGS => [
				"name" => "Visual",
				"description" => "Craft 5 paintings",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftPaintingsChallenge",
				"progress" => [
					"paintings" => ["progress" => 0, "needed" => 5],
				]
			],
			self::CRAFT_LADDERS => [
				"name" => "Going Up",
				"description" => "Craft 9 ladders",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftLaddersChallenge",
				"progress" => [
					"ladders" => ["progress" => 0, "needed" => 9],
				]
			],
			self::CRAFT_BOW => [
				"name" => "Bowing",
				"description" => "Craft a bow",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftBowChallenge",
				"progress" => []
			],
			self::BONEMEAL_SAPLINGS => [
				"name" => "Ez Growth",
				"description" => "Bonemeal 10 Saplings",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "BonemealSaplingsChallenge",
				"progress" => [
					"saplings" => ["progress" => 0, "needed" => 10],
				]
			],
		],
		self::LEVEL_5 => [
			self::COLLECT_SPRUCE_2 => [
				"name" => "Spruce 200",
				"description" => "Collect 200 spruce logs",
				"techits" => 500,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CollectSpruceChallenge2",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 200],
				]
			],
			self::PLANT_PUMPKINS => [
				"name" => "Halloween!",
				"description" => "Plant 5 pumpkin seeds",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "PlantPumpkinsChallenge",
				"progress" => [
					"planted" => ["progress" => 0, "needed" => 5],
				]
			],
			self::COLLECT_PUMPKINS => [
				"name" => "Pumpkin Man",
				"description" => "Collect 5 Pumpkins",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CollectPumpkinsChallenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 5],
				]
			],
			self::CRAFT_POLISHED_GRANITE => [
				"name" => "Polished Granite",
				"description" => "Craft 64 blocks of Polished Granite",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftPolishedGraniteChallenge",
				"progress" => [
					"granite" => ["progress" => 0, "needed" => 64],
				]
			],
			self::UPGRADE_MOB_SPAWNER => [
				"name" => "Hostile Upgrade",
				"description" => "Upgrade your mob spawner",
				"techits" => 1000,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "UpgradeMobSpawnerChallenge",
				"progress" => [],
			],
			self::KILL_PIGS => [
				"name" => "Oink-Oink!",
				"description" => "Kill 10 Pigs",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "KillPigsChallenge",
				"progress" => [
					"pigs" => ["progress" => 0, "needed" => 10],
				]
			],
			self::GROW_BIRCH_SAPLINGS => [
				"name" => "Birch Field",
				"description" => "Grow 10 Birch Saplings with bonemeal",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "GrowBirchSaplingsChallenge",
				"progress" => [
					"grown" => ["progress" => 0, "needed" => 10],
				]
			],
			self::COLLECT_COAL => [
				"name" => "MineMan",
				"description" => "Collect 64 coal",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CollectCoalChallenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 64],
				]
			],
			self::COLLECT_JUNGLE_1 => [
				"name" => "Jungle 50",
				"description" => "Collect 50 Jungle Logs",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CollectJungleChallenge1",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 50],
				]
			],
			self::BUY_IRON_ORE_GEN => [
				"name" => "Iron Man",
				"description" => "Purchase an Iron Ore Generator",
				"techits" => 500,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "BuyIronOreGenChallenge",
				"progress" => []
			],
		],
		self::LEVEL_6 => [
			self::COLLECT_JUNGLE_2 => [
				"name" => "Jungle 200",
				"description" => "Collect 200 Jungle Logs",
				"techits" => 500,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CollectJungleChallenge2",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 200],
				]
			],
			self::COLLECT_ACACIA_1 => [
				"name" => "Acacia 50",
				"description" => "Collect 50 Acacia Logs",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CollectAcaciaChallenge1",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 50],
				]
			],
			self::COLLECT_POTATOES => [
				"name" => "Yam Yam!",
				"description" => "Collect 32 potatoes",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CollectPotatoesChallenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 32],
				]
			],

			self::CRAFT_POLISHED_DIORITE => [
				"name" => "Dio's Rite",
				"description" => "Craft 64 blocks of Polished Diorite",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftPolishedDioriteChallenge",
				"progress" => [
					"diorite" => ["progress" => 0, "needed" => 64],
				]
			],
			self::PLANT_JUNGLE_SAPLINGS => [
				"name" => "Jumanji",
				"description" => "Plant 10 Jungle Saplings",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "PlantJungleSaplingsChallenge",
				"progress" => [
					"planted" => ["progress" => 0, "needed" => 10],
				],
			],
			self::PLACE_VINES => [
				"name" => "Do It For The Vine",
				"description" => "Plant 8 Vines on your island",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "PlaceVinesChallenge",
				"progress" => [
					"placed" => ["progress" => 0, "needed" => 8],
				],
			],
			self::CRAFT_CARPET => [
				"name" => "It's So Fluffy!",
				"description" => "Craft 10 carpet",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftCarpetChallenge",
				"progress" => [
					"carpet" => ["progress" => 0, "needed" => 10],
				]
			],

			self::BUY_REDSTONE_ORE_GEN => [
				"name" => "Power up!",
				"description" => "Purchase a Redstone Ore Generator",
				"techits" => 1000,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "BuyRedstoneOreGenChallenge",
				"progress" => []
			],
		],

		self::LEVEL_7 => [
			self::COLLECT_ACACIA_2 => [
				"name" => "Acacia 200",
				"description" => "Collect 200 Acacia Logs",
				"techits" => 500,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CollectAcaciaChallenge2",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 200],
				]
			],
			self::BUY_QUARTZ_BLOCK => [
				"name" => "Clout Gang",
				"description" => "Buy 64 Quartz Blocks",
				"techits" => 150,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "BuyQuartzBlockChallenge",
				"progress" => [
					"blocks" => ["progress" => 0, "needed" => 64],
				]
			],
			self::WIN_GLOWSTONE => [
				"name" => "Glow",
				"description" => "Win 16 Glowstone in crates",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "WinGlowstoneChallenge",
				"progress" => [
					"won" => ["progress" => 0, "needed" => 16],
				]
			],
			self::BUY_NETHERBRICK_BLOCK => [
				"name" => "Nether",
				"description" => "Buy 32 Nether Brick Blocks",
				"techits" => 150,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "BuyNetherBrickBlockChallenge",
				"progress" => [
					"blocks" => ["progress" => 0, "needed" => 32],
				]
			],
			self::CRAFT_IRON_BLOCKS => [
				"name" => "Iron",
				"description" => "Craft 16 Iron Blocks",
				"techits" => 200,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CraftIronBlocksChallenge",
				"progress" => [
					"blocks" => ["progress" => 0, "needed" => 16],
				]
			],
			self::CRAFT_IRON_NUGGETS => [
				"name" => "Iron Nuggets",
				"description" => "Craft 18 Iron Nuggets",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftIronNuggetsChallenge",
				"progress" => [
					"nuggets" => ["progress" => 0, "needed" => 18],
				]
			],
			self::PLANT_ACACIA_SAPLINGS => [
				"name" => "Acacia Farm",
				"description" => "Plant 5 Acacia Saplings",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "PlantAcaciaSaplingsChallenge",
				"progress" => [
					"planted" => ["progress" => 0, "needed" => 5],
				],
			],
			self::COLLECT_DARK_OAK_1 => [
				"name" => "Dark Oak 50",
				"description" => "Collect 50 Dark Oak Logs",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CollectDarkOakChallenge1",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 50],
				]
			],
			self::SELL_LAPIS_BLOCKS => [
				"name" => "Lazuli",
				"description" => "Sell 16 Lapis Lazuli Blocks",
				"techits" => 300,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "SellLapisBlocksChallenge",
				"progress" => [
					"blocks" => ["progress" => 0, "needed" => 16],
				],
			],
			self::COLLECT_LEAVES => [
				"name" => "Shear It",
				"description" => "Collect 64 Leaves using Shears",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CollectLeavesChallenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 64],
				],
			],
			self::BUY_LAPIS_ORE_GEN => [
				"name" => "Fake Diamonds",
				"description" => "Purchase a Lapis Ore Generator",
				"techits" => 1000,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "BuyLapisOreGenChallenge",
				"progress" => []
			],
		],
		self::LEVEL_8 => [
			self::COLLECT_DARK_OAK_2 => [
				"name" => "Dark Oak 200",
				"description" => "Collect 200 Dark Oak Logs",
				"techits" => 500,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CollectDarkOakChallenge2",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 200],
				]
			],
			self::CRAFT_SNOW_BLOCKS => [
				"name" => "Snow",
				"description" => "Craft 16 snow blocks",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftSnowBlocksChallenge",
				"progress" => [
					"blocks" => ["progress" => 0, "needed" => 16],
				]
			],
			self::BUY_BOOKSHELVES => [
				"name" => "Learning Time",
				"description" => "Purchase 16 bookshelves",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "BuyBookshelvesChallenge",
				"progress" => [
					"bookshelves" => ["progress" => 0, "needed" => 16],
				]
			],
			self::SELL_GOLD_BLOCKS => [
				"name" => "Gold",
				"description" => "Sell 16 Gold Blocks",
				"techits" => 350,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "SellGoldBlocksChallenge",
				"progress" => [
					"blocks" => ["progress" => 0, "needed" => 16],
				],
			],
			self::CRAFT_GOLD_NUGGETS => [
				"name" => "Gold Nugs",
				"description" => "Craft 18 Gold Nuggets",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftGoldNuggetsChallenge",
				"progress" => [
					"nuggets" => ["progress" => 0, "needed" => 18],
				]
			],
			self::KILL_SPIDERS => [
				"name" => "Creepy Crawly",
				"description" => "Kill 20 Spiders",
				"techits" => 300,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "KillSpidersChallenge",
				"progress" => [
					"spiders" => ["progress" => 0, "needed" => 20],
				]
			],
			self::WIN_BLUE_WOOL => [
				"name" => "Blues",
				"description" => "Win 16 Blue Wool",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "WinBlueWoolChallenge",
				"progress" => [
					"won" => ["progress" => 0, "needed" => 16],
				]
			],
			self::WIN_GRAY_WOOL => [
				"name" => "Cloudy",
				"description" => "Win 32 Gray Wool",
				"techits" => 150,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "WinGrayWoolChallenge",
				"progress" => [
					"won" => ["progress" => 0, "needed" => 32],
				]
			],
			self::WIN_LIME_CONCRETE => [
				"name" => "Sourness",
				"description" => "Win 32 Lime Concrete",
				"techits" => 150,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "WinLimeConcreteChallenge",
				"progress" => [
					"won" => ["progress" => 0, "needed" => 32],
				]
			],
			self::BUY_GOLD_ORE_GEN => [
				"name" => "Gold Digger",
				"description" => "Purchase a Gold Ore Generator",
				"techits" => 1000,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "BuyGoldOreGenChallenge",
				"progress" => []
			],
		],

		self::LEVEL_9 => [
			self::PLANT_DARK_OAK_SAPLINGS => [
				"name" => "Dark Oak Farm",
				"description" => "Plant 5 Dark Oak Saplings",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "PlantDarkOakSaplingsChallenge",
				"progress" => [
					"planted" => ["progress" => 0, "needed" => 5],
				],
			],
			self::KILL_SKELETONS => [
				"name" => "Boney",
				"description" => "Kill 15 Skeletons",
				"techits" => 200,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "KillSkeletonsChallenge",
				"progress" => [
					"skeletons" => ["progress" => 0, "needed" => 15],
				]
			],
			self::WIN_INK_SACS => [
				"name" => "Ink",
				"description" => "Win 32 Ink Sacs",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "WinInkSacsChallenge",
				"progress" => [
					"won" => ["progress" => 0, "needed" => 32],
				]
			],
			self::CRAFT_REDSTONE_BLOCKS => [
				"name" => "Redstone",
				"description" => "Craft 16 Redstone Blocks",
				"techits" => 300,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CraftRedstoneBlocksChallenge",
				"progress" => [
					"blocks" => ["progress" => 0, "needed" => 16],
				],
			],
			self::WIN_LEATHER_BOOTS => [
				"name" => "Boots",
				"description" => "Win 5 Leather Boots",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "WinLeatherBootsChallenge",
				"progress" => [
					"won" => ["progress" => 0, "needed" => 5],
				]
			],
			self::WIN_YELLOW_DYE => [
				"name" => "Sunny",
				"description" => "Win 32 Yellow Dye",
				"techits" => 1000,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "WinYellowDyeChallenge",
				"progress" => [
					"won" => ["progress" => 0, "needed" => 32],
				]
			],
			self::KILL_ZOMBIES => [
				"name" => "Apocalypse",
				"description" => "Kill 20 Zombies",
				"techits" => 300,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "KillZombiesChallenge",
				"progress" => [
					"zombies" => ["progress" => 0, "needed" => 20],
				]
			],
		],
		self::LEVEL_10 => [
			self::SELL_DIAMONDS => [
				"name" => "Shine Bright",
				"description" => "Sell 16 Diamonds",
				"techits" => 300,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "SellDiamondsChallenge",
				"progress" => [
					"diamonds" => ["progress" => 0, "needed" => 16],
				],
			],
			self::COLLECT_GOLD_KEYS => [
				"name" => "Key Dealer",
				"description" => "Collect 10 Gold Keys",
				"techits" => 200,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CollectGoldKeysChallenge",
				"progress" => [
					"keys" => ["progress" => 0, "needed" => 10],
				],
			],
			self::MINE_OBSIDIAN => [
				"name" => "10 Minutes of Mining",
				"description" => "Mine 16 Obsidian",
				"techits" => 200,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "MineObsidianChallenge",
				"progress" => [
					"obsidian" => ["progress" => 0, "needed" => 16],
				],
			],
			self::BUY_END_STONE => [
				"name" => "The End",
				"description" => "Purchase 32 End Stone",
				"techits" => 300,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "BuyEndStoneChallenge",
				"progress" => [
					"blocks" => ["progress" => 0, "needed" => 32],
				],
			],
			self::COLLECT_FISH => [
				"name" => "Fishies",
				"description" => "Collect 16 of any kind of fish from Fishing",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CollectFishChallenge",
				"progress" => [
					"fish" => ["progress" => 0, "needed" => 16],
				],
			],
			self::BUY_AUTOMINER => [
				"name" => "Autominer",
				"description" => "Purchase an Autominer",
				"techits" => 1000,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "BuyAutominerChallenge",
				"progress" => [],
			],
			self::CRAFT_DIAMOND_BLOCKS => [
				"name" => "57",
				"description" => "Craft 16 Diamond Blocks",
				"techits" => 500,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CraftDiamondBlocksChallenge",
				"progress" => [
					"blocks" => ["progress" => 0, "needed" => 16],
				],
			],
			self::KILL_HUSKS => [
				"name" => "Husks",
				"description" => "Kill 20 Husks",
				"techits" => 300,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "KillHusksChallenge",
				"progress" => [
					"husks" => ["progress" => 0, "needed" => 20],
				]
			],
			self::BUY_DIAMOND_ORE_GEN => [
				"name" => "Infinite Diamonds?!",
				"description" => "Purchase a Diamond Ore Generator",
				"techits" => 1000,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "BuyDiamondOreGenChallenge",
				"progress" => []
			],
		],

		self::LEVEL_11 => [
			self::BUY_WHITE_STAINED_GLASS => [
				"name" => "Stained Mirrors",
				"description" => "Buy 16 white stained glass",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "BuyWhiteStainedGlassChallenge",
				"progress" => [
					"bought" => ["progress" => 0, "needed" => 16],
				]
			],
			self::KILL_BLAZES => [
				"name" => "Blazing Powder",
				"description" => "Kill 15 Blazes",
				"techits" => 500,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "KillBlazesChallenge",
				"progress" => [
					"blazes" => ["progress" => 0, "needed" => 15],
				]
			],
			self::COLLECT_ROTTEN_FLESH => [
				"name" => "Fresh Meat",
				"description" => "Collect 20 Rotten Flesh",
				"techits" => 200,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CollectRottenFleshChallenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 20],
				],
			],
			self::LEVEL_UP => [
				"name" => "Fresh Meat",
				"description" => "Reach 20 levels of experience",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "LevelUpChallenge",
				"progress" => [
					"level" => ["progress" => 0, "needed" => 20],
				],
			],
			self::COLLECT_EMERALD => [
				"name" => "Emeralds",
				"description" => "Collect 16 emeralds",
				"techits" => 500,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CollectEmeraldChallenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 16],
				]
			],
			self::REPAIR_ITEM => [
				"name" => "Waste XP",
				"description" => "Use your XP Levels to repair an item",
				"techits" => 200,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "RepairItemChallenge",
				"progress" => []
			],
			self::COLLECT_PRISMARINE_SHARDS => [
				"name" => "Light of the Sea",
				"description" => "Collect 32 Prismarine Shards",
				"techits" => 300,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CollectPrismarineShardsChallenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 32]
				],
			],
		],

		self::LEVEL_12 => [
			self::BUY_PURPUR_BLOCKS => [
				"name" => "PurrPurple",
				"description" => "Buy 20 purpur blocks",
				"techits" => 300,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "BuyPurpurBlocksChallenge",
				"progress" => [
					"bought" => ["progress" => 0, "needed" => 20],
				]
			],
			self::CRAFT_EMERALD_BLOCKS => [
				"name" => "Jade",
				"description" => "Craft 16 emerald blocks",
				"techits" => 500,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CraftEmeraldBlocksChallenge",
				"progress" => [
					"blocks" => ["progress" => 0, "needed" => 16],
				]
			],
			self::WIN_KOTH => [
				"name" => "King of the Hill",
				"description" => "Win one KOTH game",
				"techits" => 500,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "WinKothChallenge",
				"progress" => []
			],
			self::KILL_WITHER_SKELETONS => [
				"name" => "Withering",
				"description" => "Kill 10 Wither Skeletons",
				"techits" => 500,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "KillWitherSkeletonsChallenge",
				"progress" => [
					"skeletons" => ["progress" => 0, "needed" => 10],
				]
			],
			self::BUY_EMERALD_ORE_GEN => [
				"name" => "A Shiny Green Upgrade",
				"description" => "Purchase an Emerald Ore Generator",
				"techits" => 1000,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "BuyEmeraldOreGenChallenge",
				"progress" => []
			],
		],

		self::LEVEL_13 => [
			self::CRAFT_CLOCK => [
				"name" => "Out of Time",
				"description" => "Craft a clock",
				"techits" => 300,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftClockChallenge",
				"progress" => []
			],
			self::BUY_PURPUR_QUARTZ_STONE_BRICK => [
				"name" => "Aesthetics Design",
				"description" => "Purchase 20 Purpur blocks, 20 quartz blocks, and 20 stone bricks",
				"techits" => 500,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "BuyPurpurQuartzStoneBrickChallenge",
				"progress" => [
					"purpur" => ["progress" => 0, "needed" => 20],
					"quartz" => ["progress" => 0, "needed" => 20],
					"stone" => ["progress" => 0, "needed" => 20],
				]
			],
			self::BUY_BLACK_WOOL_CONCRETE => [
				"name" => "Black Hole",
				"description" => "Purchase 16 black wool and 16 black concrete",
				"techits" => 200,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "BuyBlackWoolConcreteChallenge",
				"progress" => [
					"wool" => ["progress" => 0, "needed" => 16],
					"concrete" => ["progress" => 0, "needed" => 16],
				]
			],
		],

		self::LEVEL_14 => [
			self::BUY_MAGMA => [
				"name" => "The floor is magma",
				"description" => "Buy a magma block",
				"techits" => 1000,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "BuyMagmaChallenge",
				"progress" => []
			],
			self::WIN_BOTTLE_O_ENCHANTING => [
				"name" => "Level up!",
				"description" => "Win 16 Bottle O' Enchanting in the crates",
				"techits" => 300,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "WinBottleOEnchantingChallenge",
				"progress" => [
					"won" => ["progress" => 0, "needed" => 16],
				]
			],
		],

		self::LEVEL_15 => [
			self::BUY_DIMENSIONAL => [
				"name" => "A portal to a new dimension?!",
				"description" => "Buy a dimensional",
				"techits" => 1000,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "BuyDimensionalChallenge",
				"progress" => []
			],
			self::BUY_ELYTRA => [
				"name" => "I believe I can fly",
				"description" => "Purchase an elytra",
				"techits" => 500,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "BuyElytraChallenge",
				"progress" => []
			],
			self::BUY_ARMOR_STAND => [
				"name" => "Lookin' Fancy",
				"description" => "Purchase an Armor Stand",
				"techits" => 200,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "BuyArmorStandChallenge",
				"progress" => []
			],
			self::COLLECT_WITHER_SKULL => [
				"name" => "Withering Mask",
				"description" => "Obtain one wither skeleton head",
				"techits" => 1000,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "CollectWitherSkullChallenge",
				"progress" => []
			],
		],
	];

	//1 - 1-30
	const ISLAND_EXPAND = 1;
	const FURNACE_CRAFT = 2;
	const BED_CRAFT = 3;
	const SUGARCANE_PLANT = 4;
	const CACTUS_PLANT = 5;
	const TRAP_CRAFT = 6;

	//2 - 31-60
	const BREAK_WOOD_1 = 31;
	const PLANT_MELON = 32;
	const COBBLESTONE_OAK_STAIR = 33;
	const BUTTON_CRAFT = 34;
	const COBBLESTONE_SLAB_CRAFT = 35;
	const CRAFT_BRICKS = 36;
	const CRAFT_PANES = 37;
	const SELL_CARROTS = 38;

	//3 - 61-90
	const CRAFT_CHEST = 61;
	const COLLECT_CACTUS = 62;
	const PLANT_WHEAT = 63;
	const CRAFT_FENCE = 64;
	const CRAFT_GATE = 65;
	const PLANT_OAK_SAPLING = 66;
	const BREAK_WOOD_2 = 67;
	const CRAFT_TORCH = 68;
	const CRAFT_SIGN = 69;
	const CRAFT_GREEN_WOOL = 70;

	//4 - 91-120
	const COLLECT_SPRUCE_1 = 91;
	const CRAFT_BREAD = 92;
	const CRAFT_BEETROOT_SOUP = 93;
	const CRAFT_STONE_BRICKS = 94;
	const CRAFT_WHITE_WOOL = 95;
	const CRAFT_PAINTINGS = 96;
	const CRAFT_LADDERS = 97;
	const CRAFT_BOW = 98;
	const BONEMEAL_SAPLINGS = 99;

	//5 - 121-150
	const COLLECT_SPRUCE_2 = 121;
	const PLANT_PUMPKINS = 122;
	const COLLECT_PUMPKINS = 123;
	const CRAFT_POLISHED_GRANITE = 124;
	const UPGRADE_MOB_SPAWNER = 125;
	const KILL_PIGS = 126;
	const GROW_BIRCH_SAPLINGS = 127;
	const COLLECT_COAL = 128;
	const COLLECT_JUNGLE_1 = 129;
	const BUY_IRON_ORE_GEN = 130;

	//6 - 151-180
	const COLLECT_JUNGLE_2 = 151;
	const COLLECT_ACACIA_1 = 152;
	const COLLECT_POTATOES = 153;
	//const ABSORB_SPONGE = 154;
	//const COOK_RAW_BEEF = 155;
	const CRAFT_POLISHED_DIORITE = 156;
	const PLANT_JUNGLE_SAPLINGS = 157;
	const PLACE_VINES = 158;
	const CRAFT_CARPET = 159;
	//const SPEND_TECHITS = 160;
	const BUY_REDSTONE_ORE_GEN = 161;

	//7 - 181-210
	const COLLECT_ACACIA_2 = 181;
	const BUY_QUARTZ_BLOCK = 182;
	const WIN_GLOWSTONE = 183;
	const BUY_NETHERBRICK_BLOCK = 184;
	const CRAFT_IRON_BLOCKS = 185;
	const CRAFT_IRON_NUGGETS = 186;
	const PLANT_ACACIA_SAPLINGS = 187;
	const COLLECT_DARK_OAK_1 = 188;
	const SELL_LAPIS_BLOCKS = 189;
	const COLLECT_LEAVES = 190;
	const BUY_LAPIS_ORE_GEN = 191;

	//8 - 211-240
	const COLLECT_DARK_OAK_2 = 211;
	const CRAFT_SNOW_BLOCKS = 212;
	const BUY_BOOKSHELVES = 213;
	const SELL_GOLD_BLOCKS = 214;
	const CRAFT_GOLD_NUGGETS = 215;
	const KILL_SPIDERS = 216;
	const WIN_BLUE_WOOL = 217;
	const WIN_GRAY_WOOL = 218;
	const WIN_LIME_CONCRETE = 219;
	const BUY_GOLD_ORE_GEN = 220;

	//9 - 241-270
	const PLANT_DARK_OAK_SAPLINGS = 241;
	const KILL_SKELETONS = 242;
	const WIN_INK_SACS = 243;
	const CRAFT_REDSTONE_BLOCKS = 244;
	const WIN_LEATHER_BOOTS = 245;
	const WIN_YELLOW_DYE = 246;
	const KILL_ZOMBIES = 247;

	//10 - 271-300
	const SELL_DIAMONDS = 271;
	const COLLECT_GOLD_KEYS = 272;
	const MINE_OBSIDIAN = 273;
	const BUY_END_STONE = 274;
	const COLLECT_FISH = 275;
	const BUY_AUTOMINER = 276;
	const CRAFT_DIAMOND_BLOCKS = 277;
	const KILL_HUSKS = 278;
	const BUY_DIAMOND_ORE_GEN = 279;

	//11 - 301-330
	const BUY_WHITE_STAINED_GLASS = 301;
	const KILL_BLAZES = 302;
	const COLLECT_ROTTEN_FLESH = 303;
	const LEVEL_UP = 304;
	const COLLECT_EMERALD = 305;
	const REPAIR_ITEM = 306;
	const COLLECT_PRISMARINE_SHARDS = 307;

	//12 - 331-360
	const BUY_PURPUR_BLOCKS = 331;
	const CRAFT_EMERALD_BLOCKS = 332;
	const WIN_KOTH = 333;
	const KILL_WITHER_SKELETONS = 334;
	const BUY_EMERALD_ORE_GEN = 335;

	//13 - 361-390
	const CRAFT_CLOCK = 361;
	const BUY_PURPUR_QUARTZ_STONE_BRICK = 362;
	const BUY_BLACK_WOOL_CONCRETE = 363;

	//14 - 391-420
	const BUY_MAGMA = 391;
	const WIN_BOTTLE_O_ENCHANTING = 392;

	//15 - 421-450
	const BUY_DIMENSIONAL = 421;
	const BUY_ELYTRA = 422;
	const BUY_ARMOR_STAND = 423;
	const COLLECT_WITHER_SKULL = 424;

}