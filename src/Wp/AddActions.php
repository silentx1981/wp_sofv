<?php

namespace Silentx\Wp;

use Silentx\Sofv\Games;
use Silentx\Sofv\Ranking;

class AddActions
{
	public static function run()
	{
		add_action('wp_enqueue_scripts',[self::class, 'style']);
		add_action('wp_enqueue_scripts',[self::class, 'javascript']);

		add_action('wp_ajax_sofvRankingRequest', [Ranking::class, 'request']);
		add_action('wp_ajax_nopriv_sofvRankingRequest', [Ranking::class, 'request']);

		add_action('wp_ajax_sofvGamesRequest', [Games::class, 'request']);
		add_action('wp_ajax_nopriv_sofvGamesRequest', [Games::class, 'request']);
	}

	public static function javascript()
	{
		$dir = plugins_url().'/wp_sofv/';
		wp_enqueue_script('jquery', $dir.'node_modules/jquery/dist/jquery.min.js', [], false, true);
		wp_enqueue_script('bootstrap', $dir.'node_modules/bootstrap/dist/js/bootstrap.bundle.min.js', [], false, true);
		wp_enqueue_script('owlcarousel', $dir.'node_modules/owl.carousel2/dist/owl.carousel.min.js', [], false, true);
		wp_enqueue_script('wpSofv', $dir.'js/wp_sofv.js', [], false, true);
	}

	public static function style()
	{
		$dir = plugins_url().'/wp_sofv/';
		//wp_enqueue_style('stylebootstrap', $dir.'node_modules/bootstrap/dist/css/bootstrap.min.css');
		wp_enqueue_style('styleowlcarousel', $dir.'node_modules/owl.carousel2/dist/assets/owl.carousel.min.css');
		wp_enqueue_style('styleowlcarouseltheme', $dir.'node_modules/owl.carousel2/dist/assets/owl.theme.default.css');
		wp_enqueue_style('style', $dir . 'css/style.css');
	}
}