<?php namespace pvp\games\arena\loot;

use pocketmine\block\tile\Chest;
use pocketmine\item\Item;

class LootPool{
	
	public function __construct(
		public string $name,
		public array $tier1Items = [],
		public array $tier2Items = [],
		public array $tier3Items = []
	){}
	
	public function getName() : string{
		return $this->name;
	}
	
	public function getItems(int $tier) : array{
		return match($tier){
			1 => $this->tier1Items,
			2 => $this->tier2Items,
			3 => $this->tier3Items,
			default => []
		};
	}
	
	public function getRandomItem(int $tier = -1) : Item{
		if($tier === -1) return $this->getRandomItem(mt_rand(1, 3));
		$items = $this->getItems($tier);
		return $items[mt_rand(0, count($items) - 1)];
	}
	
	/**
	 * Gets items using loot chest tier; separate from item tiers
	 */
	public function getItemsFor(int $tier) : array{
		$tier1items = $tier2items = $tier3items = 0;
		$items = [];
		
		switch($tier){
			case 1:
				$total = mt_rand(3, 7);
				for($i = 0; $i < $total; $i++){
					$slot = mt_rand(0, 26);
					while(isset($items[$slot]))
						$slot = mt_rand(0, 26);

					$items[$slot] = $this->getRandomItem(1);
				}
				if(mt_rand(1, 3) == 1){
					$total = mt_rand(1, 3);
					for($i = 0; $i < $total; $i++){
						$slot = mt_rand(0, 26);
						while(isset($items[$slot]))
							$slot = mt_rand(0, 26);

						$items[$slot] = $this->getRandomItem(mt_rand(1, 10) === 1 ? 3 : 2);
					}
				}
				break;
			case 2:
				$total = mt_rand(3, 5);
				for($i = 0; $i < $total; $i++){
					$slot = mt_rand(0, 26);
					while(isset($items[$slot]))
						$slot = mt_rand(0, 26);

					$items[$slot] = $this->getRandomItem(1);
				}
				$total = mt_rand(2, 5);
				for($i = 0; $i < $total; $i++){
					$slot = mt_rand(0, 26);
					while(isset($items[$slot]))
						$slot = mt_rand(0, 26);

					$items[$slot] = $this->getRandomItem(2);
				}
				$total = mt_rand(0, 2);
				if($total === 0) break;
				for($i = 0; $i < $total; $i++){
					$slot = mt_rand(0, 26);
					while(isset($items[$slot]))
						$slot = mt_rand(0, 26);

					$items[$slot] = $this->getRandomItem(mt_rand(1, 10) === 1 ? 3 : 2);
				}
				break;
			case 3:
				//todo: when i give a shit!!!
				break;
		}
		return $items;
	}
	
	public function fillChest(Chest $chest, int $tier) : void{
		if(!$chest->isClosed()){
			$chest->getInventory()->clearAll();
			$items = $this->getItemsFor($tier);
			foreach($items as $slot => $item){
				$chest->getInventory()->setItem($slot, $item);
			}
		}
	}
	
}