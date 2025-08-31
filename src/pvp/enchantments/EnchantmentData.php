<?php namespace pvp\enchantments;

use pocketmine\inventory\ArmorInventory;
use pocketmine\item\{
    Armor,
    Item,
	Bow,
	Axe,
	Pickaxe,
	Sword,
	Shears,
	Shovel,
	Hoe,
};
use pocketmine\utils\TextFormat;
use pocketmine\item\enchantment\ItemFlags;

use pvp\enchantments\type\Enchantment;
use pvp\item\{
	Elytra,
};

class EnchantmentData{

	const SLOT_NONE = 0;
	const SLOT_ALL = 1;

	const SLOT_ARMOR = 2;
	const SLOT_HEAD = 3;
	const SLOT_TORSO = 4;
	const SLOT_LEGS = 5;
	const SLOT_FEET = 6;

	const SLOT_SWORD = 7;
	const SLOT_BOW = 8;

	const SLOT_TOOL = 9;
	const SLOT_HOE = 10;
	const SLOT_SHEARS = 11;
	const SLOT_FLINT_AND_STEEL = 12;

	const SLOT_DIG = 13;
	const SLOT_AXE = 14;
	const SLOT_PICKAXE = 15;
	const SLOT_SHOVEL = 16;

	const SLOT_FISHING_ROD = 17;
	const SLOT_CARROT_STICK = 18;
	const SLOT_ELYTRA = 19;
	const SLOT_TRIDENT = 20;

	const SLOT_MELEE = 21;


	const RARITY_COMMON = 1;
	const RARITY_UNCOMMON = 2;
	const RARITY_RARE = 3;
	const RARITY_LEGENDARY = 4;
	const RARITY_DIVINE = 5;

	const RARITY_NAMES = [
		self::RARITY_COMMON => "Common",
		self::RARITY_UNCOMMON => "Uncommon",
		self::RARITY_RARE => "Rare",
		self::RARITY_LEGENDARY => "Legendary",
		self::RARITY_DIVINE => "Divine",
	];

	const ENCHANTMENTS = [
		self::PROTECTION => [
			"name" => "Protection",
			"rarity" => self::RARITY_LEGENDARY,
			"type" => self::SLOT_ARMOR,
			"maxLevel" => 4,
			"description" => "Damage from the environment (such as falling) is converted to armor damage",
			"modifier" => 0.75,
			"ev" => null,
		],
		self::FIRE_PROTECTION => [
			"name" => "Fire Protection",
			"rarity" => self::RARITY_RARE,
			"type" => self::SLOT_ARMOR,
			"maxLevel" => 4,
			"description" => "Damage from fire is reduced",
			"modifier" => 1.25,
			"ev" => [5, 6, 7],
		],
		self::FEATHER_FALLING => [
			"name" => "Feather Falling",
			"rarity" => self::RARITY_UNCOMMON,
			"type" => self::SLOT_FEET,
			"maxLevel" => 4,
			"description" => "Damage from falling is reduced",
			"modifier" => 2.5,
			"ev" => [5],
			"obtainable" => false,
		],
		self::BLAST_PROTECTION => [
			"name" => "Blast Protection",
			"rarity" => self::RARITY_RARE,
			"type" => self::SLOT_ARMOR,
			"maxLevel" => 4,
			"description" => "Damage from blasts is reduced",
			"modifier" => 1.5,
			"ev" => [9, 10],
		],
		self::PROJECTILE_PROTECTION => [
			"name" => "Projectile Protection",
			"rarity" => self::RARITY_RARE,
			"type" => self::SLOT_ARMOR,
			"maxLevel" => 4,
			"description" => "Damage from projectiles is reduced",
			"modifier" => 1.5,
			"ev" => [2],
		],
		self::RESPIRATION => [
			"name" => "Respiration",
			"rarity" => self::RARITY_UNCOMMON,
			"type" => self::SLOT_HEAD,
			"maxLevel" => 3,
			"description" => "Increase underwater breathing time",
			"obtainable" => false,
		],
		self::EFFICIENCY => [
			"name" => "Efficiency",
			"rarity" => self::RARITY_RARE,
			"type" => self::SLOT_DIG,
			"maxLevel" => 5,
			"description" => "Faster mining rate",
		],
		self::SILK_TOUCH => [
			"name" => "Silk Touch",
			"rarity" => self::RARITY_LEGENDARY,
			"type" => self::SLOT_DIG,
			"maxLevel" => 1,
			"description" => "Mined blocks will drop themselves instead of their normal drops",
			"obtainable" => false,
		],
		self::UNBREAKING => [
			"name" => "Unbreaking",
			"rarity" => self::RARITY_UNCOMMON,
			"type" => self::SLOT_ALL,
			"maxLevel" => 3,
			"description" => "Tool's durability reduction is slowed",
		],


		self::ZEUS => [
			"name" => "Zeus",
			"rarity" => self::RARITY_RARE,
			"type" => self::SLOT_SWORD,
			"maxLevel" => 3,
			"description" => "Chance to double damage and strike lightning on enemy",
		],
		self::KEY_THEFT => [
			"name" => "Key Theft",
			"rarity" => self::RARITY_LEGENDARY,
			"type" => self::SLOT_SWORD,
			"maxLevel" => 3,
			"description" => "Chance of stealing keys from a player when they die. (1 to 3 keys max depending on level)",
		],
		self::LIFESTEAL => [
			"name" => "Lifesteal",
			"rarity" => self::RARITY_UNCOMMON,
			"type" => self::SLOT_SWORD,
			"maxLevel" => 2,
			"description" => "Chance of damage done being added to your health",
		],
		self::KABOOM => [
			"name" => "Kaboom",
			"rarity" => self::RARITY_LEGENDARY,
			"type" => self::SLOT_SWORD,
			"maxLevel" => 3,
			"description" => "Chance of explosive damage",
		],
		self::HADES => [
			"name" => "Hades",
			"rarity" => self::RARITY_UNCOMMON,
			"type" => self::SLOT_SWORD,
			"maxLevel" => 3,
			"description" => "Chance of extra damage + fire damage + awsum particles",
		],
		self::OOF => [
			"name" => "OOF",
			"rarity" => self::RARITY_COMMON,
			"type" => self::SLOT_SWORD,
			"maxLevel" => 1,
			"description" => "OOF Sounds when doing damage",
		],
		self::FROST => [
			"name" => "Frost",
			"rarity" => self::RARITY_COMMON,
			"type" => self::SLOT_SWORD,
			"maxLevel" => 3,
			"description" => "Chance of dealing temporary slowness",
		],
		self::DAZE => [
			"name" => "Daze",
			"rarity" => self::RARITY_UNCOMMON,
			"type" => self::SLOT_SWORD,
			"maxLevel" => 3,
			"description" => "Chance of dealing temporary nausea",
		],
		self::POISON => [
			"name" => "Poison",
			"rarity" => self::RARITY_UNCOMMON,
			"type" => self::SLOT_SWORD,
			"maxLevel" => 2,
			"description" => "Chance of poisoning the enemy",
		],
		self::UPLIFT => [
			"name" => "Uplift",
			"rarity" => self::RARITY_RARE,
			"type" => self::SLOT_SWORD,
			"maxLevel" => 1,
			"description" => "Launch enemies up high!",
		],
		self::BLEED => [
			"name" => "Bleed",
			"rarity" => self::RARITY_RARE,
			"type" => self::SLOT_SWORD,
			"maxLevel" => 3,
			"description" => "Blood particles and damage to players overtime",
		],


		self::STARVATION => [
			"name" => "Starvation",
			"rarity" => self::RARITY_COMMON,
			"type" => self::SLOT_SWORD,
			"maxLevel" => 2,
			"description" => "Makes your enemy lose hunger as you fight them",
		],
		self::ELECTRIFY => [
			"name" => "Electrify",
			"rarity" => self::RARITY_UNCOMMON,
			"type" => self::SLOT_SWORD,
			"maxLevel" => 2,
			"description" => "Stuns your enemy with slowness",
		],
		self::PIERCE => [
			"name" => "Pierce",
			"rarity" => self::RARITY_RARE,
			"type" => self::SLOT_SWORD,
			"maxLevel" => 3,
			"description" => "Deals more armor damage to your enemies",
		],
		self::DECAY => [
			"name" => "Decay",
			"rarity" => self::RARITY_LEGENDARY,
			"type" => self::SLOT_SWORD,
			"maxLevel" => 2,
			"description" => "Chance of dealing wither damage to enemy",
		],
		self::COMBO => [
			"name" => "Combo",
			"rarity" => self::RARITY_DIVINE,
			"type" => self::SLOT_SWORD,
			"maxLevel" => 1,
			"description" => "Raises your weapon damage the higher your combo is",
		],
		self::TIDES => [
			"name" => "Tides",
			"rarity" => self::RARITY_UNCOMMON,
			"type" => self::SLOT_SWORD,
			"maxLevel" => 2,
			"description" => "Chance of extra knockback and splash damage",
		],


		self::TRIPLE_THREAT => [
			"name" => "Triple Threat",
			"rarity" => self::RARITY_LEGENDARY,
			"type" => self::SLOT_BOW,
			"maxLevel" => 1,
			"description" => "Bow shoots 3 arrows at once",
			"obtainable" => false,
		],
		self::RELOCATE => [
			"name" => "Relocate",
			"rarity" => self::RARITY_RARE,
			"type" => self::SLOT_BOW,
			"maxLevel" => 1,
			"description" => "Teleport to arrow shot location",
			"obtainable" => false,
		],
		self::SNIPER => [
			"name" => "Sniper",
			"rarity" => self::RARITY_RARE,
			"type" => self::SLOT_BOW,
			"maxLevel" => 1,
			"description" => "Arrows will shoot in a straight line",
			"obtainable" => false,
		],
		self::INSTA_SHOT => [
			"name" => "Insta Shot",
			"rarity" => self::RARITY_DIVINE,
			"type" => self::SLOT_BOW,
			"maxLevel" => 1,
			"description" => "Arrows instantly kill, and another arrow is given when you hit your shot",
			//"obtainable" => false,
		],

		self::CROUCH => [
			"name" => "Crouch",
			"rarity" => self::RARITY_RARE,
			"type" => self::SLOT_ARMOR,
			"maxLevel" => 2,
			"description" => "Chance to highly decrease damage while crouching",
			"stackable" => true,
			"stackLevel" => 4,
		],
		self::SCORCH => [
			"name" => "Scorch",
			"rarity" => self::RARITY_COMMON,
			"type" => self::SLOT_ARMOR,
			"maxLevel" => 5,
			"description" => "Chance of dealing fire damage to enemies, while making you resistant to all fire",
		],
		self::THORNS => [
			"name" => "Thorns",
			"rarity" => self::RARITY_COMMON,
			"type" => self::SLOT_ARMOR,
			"maxLevel" => 4,
			"description" => "Chance of enemies being damaged while hitting you",
			"stackable" => true,
			"stackLevel" => 4,
			"obtainable" => false,
		],
		self::SHOCKWAVE => [
			"name" => "Shockwave",
			"rarity" => self::RARITY_UNCOMMON,
			"type" => self::SLOT_ARMOR,
			"maxLevel" => 2,
			"stackable" => true,
			"stackLevel" => 2,
			"description" => "Chance to knockback enemies surrounding you",
		],
		self::ADRENALINE => [
			"name" => "Adrenaline",
			"rarity" => self::RARITY_UNCOMMON,
			"type" => self::SLOT_ARMOR,
			"maxLevel" => 1,
			"description" => "Gives you speed when at low health",
		],
		self::OVERLORD => [
			"name" => "Overlord",
			"rarity" => self::RARITY_LEGENDARY,
			"type" => self::SLOT_ARMOR,
			"maxLevel" => 2,
			"description" => "Give you 1 extra heart with each level",
			"stackable" => true,
			"stackLevel" => 5,
		],

		self::SNARE => [
			"name" => "Snare",
			"rarity" => self::RARITY_UNCOMMON,
			"type" => self::SLOT_ARMOR,
			"maxLevel" => 1,
			"description" => "Hooks your enemies and drags them towards you",
		],
		self::RAGE => [
			"name" => "Rage",
			"rarity" => self::RARITY_RARE,
			"type" => self::SLOT_ARMOR,
			"maxLevel" => 3,
			"description" => "Chance to give you strength and resistance",
		],
		self::SORCERY => [
			"name" => "Sorcery",
			"rarity" => self::RARITY_RARE,
			"type" => self::SLOT_ARMOR,
			"maxLevel" => 2,
			"description" => "Gives you a random positive effect",
		],
		self::BLESSING => [
			"name" => "Blessing",
			"rarity" => self::RARITY_LEGENDARY,
			"type" => self::SLOT_ARMOR,
			"maxLevel" => 3,
			"description" => "Chance to remove your negative effects and give them to the enemy",
		],
		self::DODGE => [
			"name" => "Dodge",
			"rarity" => self::RARITY_DIVINE,
			"type" => self::SLOT_ARMOR,
			"maxLevel" => 2,
			"description" => "Chance to fully dodge hits from the enemy",
		],
		self::GODLY_RETRIBUTION => [
			"name" => "Godly Retribution",
			"rarity" => self::RARITY_DIVINE,
			"type" => self::SLOT_ARMOR,
			"maxLevel" => 1,
			"description" => "Strength and regeneration for a short period when close to dying",
		],

		self::GLOWING => [
			"name" => "Glowing",
			"rarity" => self::RARITY_COMMON,
			"type" => self::SLOT_HEAD,
			"maxLevel" => 1,
			"description" => "Permanent night vision to easily let you see in dark conditions",
		],

		self::GEARS => [
			"name" => "Gears",
			"rarity" => self::RARITY_RARE,
			"type" => self::SLOT_FEET,
			"maxLevel" => 2,
			"description" => "Permanent speed effect to get around easily",
		],
		self::BUNNY => [
			"name" => "Bunny",
			"rarity" => self::RARITY_UNCOMMON,
			"type" => self::SLOT_FEET,
			"maxLevel" => 3,
			"description" => "Permanent jump boost to get to higher ground",
		],

		self::EXCAVATE => [
			"name" => "Excavate",
			"rarity" => self::RARITY_RARE,
			"type" => self::SLOT_SHOVEL,
			"maxLevel" => 3,
			"description" => "Chance to blow up nearby blocks while digging",
		],

	];

	const SLOTS = [
		self::SLOT_NONE => [
			"name" => "None",
			"id" => ItemFlags::NONE,
		],
		self::SLOT_ALL => [
			"name" => "Universal",
			"id" => ItemFlags::ALL,
		],

		self::SLOT_ARMOR => [
			"name" => "Armor",
			"id" => ItemFlags::ARMOR,
		],
		self::SLOT_HEAD => [
			"name" => "Helmet",
			"id" => ItemFlags::HEAD,
		],
		self::SLOT_TORSO => [
			"name" => "Chestplate",
			"id" => ItemFlags::TORSO,
		],
		self::SLOT_LEGS => [
			"name" => "Leggings",
			"id" => ItemFlags::LEGS,
		],
		self::SLOT_FEET => [
			"name" => "Boots",
			"id" => ItemFlags::FEET,
		],

		self::SLOT_SWORD => [
			"name" => "Sword",
			"id" => ItemFlags::SWORD,
		],
		self::SLOT_BOW => [
			"name" => "Bow",
			"id" => ItemFlags::BOW,
		],

		self::SLOT_TOOL => [
			"name" => "Tool",
			"id" => ItemFlags::TOOL,
		],
		self::SLOT_HOE => [
			"name" => "Hoe",
			"id" => ItemFlags::HOE,
		],
		self::SLOT_SHEARS => [
			"name" => "Shears",
			"id" => ItemFlags::SHEARS,
		],
		self::SLOT_FLINT_AND_STEEL => [
			"name" => "Flint and Steel",
			"id" => ItemFlags::FLINT_AND_STEEL,
		],

		self::SLOT_DIG => [
			"name" => "Dig",
			"id" => ItemFlags::DIG,
		],
		self::SLOT_AXE => [
			"name" => "Axe",
			"id" => ItemFlags::AXE,
		],
		self::SLOT_PICKAXE => [
			"name" => "Pickaxe",
			"id" => ItemFlags::PICKAXE,
		],
		self::SLOT_SHOVEL => [
			"name" => "Shovel",
			"id" => ItemFlags::SHOVEL,
		],

		self::SLOT_FISHING_ROD => [
			"name" => "Fishing Rod",
			"id" => ItemFlags::FISHING_ROD,
		],

		self::SLOT_MELEE => [
			"name" => "Melee",
			"id" => (ItemFlags::SWORD | ItemFlags::AXE),
		],
	];

	public static function typeToEtype(int $type){
		return self::SLOTS[$type]["id"] ?? 0x0;
	}

	public static function etypeToType($etype){
		foreach(self::SLOTS as $id => $data){
			if($data["id"] == $etype) return $id;
		}
		return -1;
	}

	public static function getItemType(Item $item) : int{
		switch(true){
			case $item instanceof Armor && $item->getArmorSlot() == ArmorInventory::SLOT_HEAD:
				return self::SLOT_HEAD;
			case $item instanceof Armor && $item->getArmorSlot() == ArmorInventory::SLOT_CHEST:
				return self::SLOT_TORSO;
			case $item instanceof Armor && $item->getArmorSlot() == ArmorInventory::SLOT_LEGS:
				return self::SLOT_LEGS;
			case $item instanceof Armor && $item->getArmorSlot() == ArmorInventory::SLOT_FEET:
				return self::SLOT_FEET;

			case $item instanceof Sword:
				return self::SLOT_SWORD;
			case $item instanceof Bow:
				return self::SLOT_BOW;

			case $item instanceof Hoe:
				return self::SLOT_HOE;
			case $item instanceof Shears:
				return self::SLOT_SHEARS;

			case $item instanceof Axe:
				return self::SLOT_AXE;
			case $item instanceof Pickaxe:
				return self::SLOT_PICKAXE;
			case $item instanceof Shovel:
				return self::SLOT_SHOVEL;

			case $item instanceof Elytra:
				return self::SLOT_ELYTRA;

			default:
				return -1;
		}
	}

	public static function canEnchantWith(Item $item, Enchantment $enchantment) : bool{
		$itype = self::typeToEtype(self::getItemType($item));
		return $enchantment->hasType($itype);
	}

	public static function rarityColor(int $rarity) : string{
		return [
			self::RARITY_COMMON => TextFormat::GREEN,
			self::RARITY_UNCOMMON => TextFormat::DARK_GREEN,
			self::RARITY_RARE => TextFormat::YELLOW,
			self::RARITY_LEGENDARY => TextFormat::GOLD,
			self::RARITY_DIVINE => TextFormat::RED,
		][$rarity] ?? TextFormat::GRAY;
	}

	//Vanilla
	const PROTECTION = 0;
	const FIRE_PROTECTION = 1;
	const FEATHER_FALLING = 2;
	const BLAST_PROTECTION = 3;
	const PROJECTILE_PROTECTION = 4;
	const THORNS_VANILLA = 5;
	const RESPIRATION = 6;
	const DEPTH_STRIDER = 7;
	const AQUA_AFFINITY = 8;
	const SHARPNESS = 9;
	const SMITE = 10;
	const BANE_OF_ARTHROPODS = 11;
	const KNOCKBACK = 12;
	const FIRE_ASPECT = 13;
	const LOOTING = 14;
	const EFFICIENCY = 15;
	const SILK_TOUCH = 16;
	const UNBREAKING = 17;
	const FORTUNE = 18;
	const POWER = 19;
	const PUNCH = 20;
	const FLAME = 21;
	const INFINITY = 22;
	const LUCK_OF_THE_SEA = 23;
	const LURE = 24;
	const FROST_WALKER = 25;
	const MENDING = 26;

	//Sword enchants
	const ZEUS = 100;
	const KEY_THEFT = 101;
	const LIFESTEAL = 102;
	const KABOOM = 103;
	const HADES = 104;
	const OOF = 105;
	const FROST = 106;
	const DAZE = 107;
	const POISON = 108;
	const UPLIFT = 109;
	const BLEED = 110;

	const STARVATION = 111;
	const ELECTRIFY = 112;
	const PIERCE = 113;
	const DECAY = 114;
	const COMBO = 115;
	const TIDES = 116;

	//Bow enchants
	const TRIPLE_THREAT = 130;
	const RELOCATE = 131;
	const SNIPER = 132;
	const INSTA_SHOT = 133;

	//Pickaxe enchants
	/**const EXPLOSIVE = 140;
	const ORE_MAGNET = 141;
	const FEED = 142;
	const TRANSFUSION = 143;
	const KEYPLUS = 144;
	const XP_MAGNET = 145;

	const CHARM = 146;
	const MOMENTUM = 147;
	const AIRSTRIKE = 148;
	const IMPLODE = 149;*/

	//Armor enchants
	const CROUCH = 160;
	const SCORCH = 161;
	const THORNS = 162;
	const SHOCKWAVE = 163;
	const ADRENALINE = 164;
	const OVERLORD = 165;


	const SNARE = 166;
	const RAGE = 167;
	const SORCERY = 168;
	const BLESSING = 169;
	const DODGE = 170;
	const GODLY_RETRIBUTION = 171;



	const GLOWING = 190;

	//const CHEST = 200;

	//const LEGGING = 210;

	const GEARS = 220;
	const BUNNY = 221;

	const EXCAVATE = 300;


}