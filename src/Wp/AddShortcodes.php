<?php


namespace Silentx\Wp;

use Silentx\Sofv\Games;
use Silentx\Sofv\Ranking;

class AddShortcodes
{
	public static function run()
	{
		add_shortcode('sofvGames', [self::class, 'games']);
		add_shortcode('sofvRanking', [self::class, 'ranking']);
	}

	public static function games($attrs = [])
	{
		$ajaxUrl = admin_url( 'admin-ajax.php' );
		$url = $attrs['url'] ?? null;
		$type = $attrs['type'] ?? 'current';
		$groupBy = $attrs['groupby'] ?? 'day';
		$resultMode = $attrs['resultmode'] ?? 'renderGrid';
		if (!$url)
			return 'Url not found';

		return '<div class="sofvGames text-center" ajaxUrl="'.$ajaxUrl.'" url="'.$url.'" type="'.$type.'" groupby="'.$groupBy.'" resultmode="'.$resultMode.'">
					<i class="fas fa-3x fa-spinner fa-spin"></i><br>
					Spiele werden geladen
				</div>';

		$games = new Games($url, $type);

		return $games->getGames($groupBy, $resultMode);
	}

	public static function ranking($attrs = [])
	{
		$ajaxUrl = admin_url( 'admin-ajax.php' );
		$url = $attrs['url'] ?? null;
		if (!$url)
			return 'Url not found';

		return '<div class="sofvRanking text-center" ajaxUrl="'.$ajaxUrl.'" url="'.$url.'">
					<i class="fas fa-3x fa-spinner fa-spin"></i><br>
					Rangliste wird geladen
				</div>';
	}
}
