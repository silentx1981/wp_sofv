<?php

/*
 * Plugin Name: Sofv Integration
 * Plugin URI: https://wyssinet.ch/WpSofv
 * Description:Damit können Inhalte von der sofv-Seite im Wordpress integriert werden
 * Author: Raffael Wyss
 * Author: URI: https://wyssinet.ch
 * Version: 1.0.5
 */

require_once(WP_PLUGIN_DIR.'/wp_sofv/vendor/autoload.php');

// Direkten Aufruf verhindern
if (!defined('WPINC')) {
	die;
}

\Silentx\Wp\AddShortcodes::run();
\Silentx\Wp\AddActions::run();





