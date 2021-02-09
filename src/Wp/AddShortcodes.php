<?php


namespace Silentx\Wp;

use Silentx\Sofv\Games;

class AddShortcodes
{
	public static function run()
	{
		add_shortcode('sofvGames', [self::class, 'games']);
	}

	public static function games()
	{
		$games = new Games();
		return $games->getGames('hhhaaa');
	}
}
