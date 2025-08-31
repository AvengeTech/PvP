<?php namespace pvp\arenas;

use pocketmine\block\{
	Block,
	VanillaBlocks
};
use pocketmine\world\particle\BlockBreakParticle;

class BlockState{

	public function __construct(
		public Arena $arena,
		public Block $block,
		public int $time = 30,
	){

	}

	public function getArena() : Arena{
		return $this->arena;
	}

	public function getBlock() : Block{
		return $this->block;
	}

	public function tick() : bool{
		if(--$this->time <= 0){
			$this->destroy();
			return true;
		}
		return false;
	}

	public function destroy() : void{
		$world = $this->getArena()->getWorld();
		$block = $this->getBlock();
		$world->addParticle(($pos = $block->getPosition()), new BlockBreakParticle($block));
		$world->setBlock($pos, VanillaBlocks::AIR());
	}

}