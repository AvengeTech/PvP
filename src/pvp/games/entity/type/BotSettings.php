<?php

namespace pvp\games\entity\type;

use pvp\games\type\Game;

final class BotSettings {
	public const DEFAULT_REACH = 2.575;
	public const DEFAULT_STRAFE = true;
	public const DEFAULT_NAME = "Bot";

	public const REACH_BUFFER = 0.085;

	public function __construct(public float $reach, public bool $strafe, public string $displayName, public ?Game $game = null) {
	}

	public static function create(float $reach = self::DEFAULT_REACH, bool $strafe = self::DEFAULT_STRAFE, string $displayName = self::DEFAULT_NAME, ?Game $game = null): self {
		return new self($reach, $strafe, $displayName, $game);
	}
}
