<?php

namespace pvp\entity;

use pocketmine\block\BlockTypeTags;
use pocketmine\block\VanillaBlocks;
use pocketmine\color\Color;
use pocketmine\data\bedrock\PotionTypeIdMap;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\InstantEffect;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\SplashPotion;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\item\PotionType;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\world\particle\PotionSplashParticle;
use pocketmine\world\sound\PotionSplashSound;
use function count;
use function round;

class HealthPot extends SplashPotion
{

	public const TAG_POTION_ID = "PotionId"; //TAG_Short

	public static function getNetworkTypeId(): string
	{
		return EntityIds::SPLASH_POTION;
	}

	//protected bool $linger = false;
	protected PotionType $potionType;

	public function __construct(Location $location, ?Entity $shootingEntity, PotionType $potionType, ?CompoundTag $nbt = null)
	{
		$this->potionType = $potionType;
		parent::__construct($location, $shootingEntity, $potionType, $nbt);
		$this->setPotionType(PotionType::STRONG_HEALING());
	}

	protected function getInitialGravity(): float
	{
		return 0.05;
	}

	public function saveNBT(): CompoundTag
	{
		$nbt = parent::saveNBT();
		$nbt->setShort(self::TAG_POTION_ID, PotionTypeIdMap::getInstance()->toId($this->getPotionType()));

		return $nbt;
	}

	public function getResultDamage(): int
	{
		return -1; //no damage
	}

	protected function onHit(ProjectileHitEvent $event): void
	{
		$effects = $this->getPotionEffects();
		$hasEffects = true;

		if (count($effects) === 0) {
			$particle = new PotionSplashParticle(PotionSplashParticle::DEFAULT_COLOR());
			$hasEffects = false;
		} else {
			$colors = [];
			foreach ($effects as $effect) {
				$level = $effect->getEffectLevel();
				for ($j = 0; $j < $level; ++$j) {
					$colors[] = $effect->getColor();
				}
			}
			$particle = new PotionSplashParticle(Color::mix(...$colors));
		}

		$this->getWorld()->addParticle($this->location, $particle);
		$this->broadcastSound(new PotionSplashSound());

		if ($hasEffects) {
			if (!$this->willLinger()) {
				foreach ($this->getWorld()->getCollidingEntities($this->boundingBox->expandedCopy(4.125, 2.125, 4.125), $this) as $entity) {
					if ($entity instanceof Living) {
						$distanceSquared = $entity->getEyePos()->distanceSquared($this->location);
						if ($distanceSquared > 3.75 ** 2) {
							continue;
						}

						foreach ($this->getPotionEffects() as $effect) {

							if (!($effect->getType() instanceof InstantEffect)) {
								$newDuration = (int) round($effect->getDuration() * 0.75);
								if ($newDuration < 20) {
									continue;
								}
								$effect->setDuration($newDuration);
								$entity->getEffects()->add($effect);
							} else {
								$effect->getType()->applyEffect($entity, $effect, 1, $this);
							}
						}
					}
				}
			} else {
				//TODO: lingering potions
			}
		} elseif ($event instanceof ProjectileHitBlockEvent && $this->getPotionType()->equals(PotionType::WATER())) {
			$blockIn = $event->getBlockHit()->getSide($event->getRayTraceResult()->getHitFace());

			if ($blockIn->hasTypeTag(BlockTypeTags::FIRE)) {
				$this->getWorld()->setBlock($blockIn->getPosition(), VanillaBlocks::AIR());
			}
			foreach ($blockIn->getHorizontalSides() as $horizontalSide) {
				if ($horizontalSide->hasTypeTag(BlockTypeTags::FIRE)) {
					$this->getWorld()->setBlock($horizontalSide->getPosition(), VanillaBlocks::AIR());
				}
			}
		}
	}

	/**
	 * Returns the meta value of the potion item that this splash potion corresponds to. This decides what effects will be applied to the entity when it collides with its target.
	 */
	public function getPotionType(): PotionType
	{
		return PotionType::STRONG_HEALING();
	}

	/**
	 * Returns whether this splash potion will create an area-effect cloud when it lands.
	 */
	public function willLinger(): bool
	{
		return false;
	}

	/**
	 * @return EffectInstance[]
	 */
	public function getPotionEffects(): array
	{
		return $this->potionType->getEffects();
	}

	protected function syncNetworkData(EntityMetadataCollection $properties): void
	{
		parent::syncNetworkData($properties);

		$properties->setShort(EntityMetadataProperties::POTION_AUX_VALUE, PotionTypeIdMap::getInstance()->toId($this->potionType));
		$properties->setGenericFlag(EntityMetadataFlags::LINGER, $this->linger);
	}
}
