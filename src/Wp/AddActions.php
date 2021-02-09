<?php

namespace Silentx\Wp;

class AddActions
{
	public static function run()
	{
		add_action('wp_enqueue_scripts',[self::class, 'style']);
	}

	public static function style()
	{
		$dir = plugins_url().'/wp_sofv/';
		wp_enqueue_style('style', $dir . 'css/style.css');
	}
}