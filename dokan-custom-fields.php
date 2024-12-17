<?php
/**
 * Plugin Name: Dokan Custom Fields
 * Plugin URI: https://mariomelembe.com
 * Description: Adiciona campos personalizados ao registo de vendedores no Dokan.
 * Version: 1.0.0
 * Author: Mário Melembe
 * Author URI: https://mariomelembe.com
 * Requires Plugins: dokan-lite
 * License: GPL2
 */

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define o diretório do plugin
if ( ! defined( 'MV_PLUGIN_DIR' ) ) {
	define( 'MV_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

// Verifique se o autoloader existe antes de carregar
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    error_log('Autoloader do PHPSpreadsheet não encontrado. Verifique a instalação.');
}


function enqueue_custom_admin_scripts($hook): void {
	if ($hook !== 'toplevel_page_custom_fields') {
		return;
	}

	wp_enqueue_script('jquery-ui-sortable');
	wp_enqueue_script('custom-fields-script', plugin_dir_url(__FILE__) . 'assets/js/custom-fields.js', ['jquery', 'jquery-ui-sortable'], null, true);
	wp_enqueue_style('custom-fields-style', plugin_dir_url(__FILE__) . 'assets/css/custom-fields.css');
}
add_action('admin_enqueue_scripts', 'enqueue_custom_admin_scripts');

function enqueue_jquery_ui_in_frontend(): void {
	// Enfileirar jQuery UI Sortable
	wp_enqueue_script('jquery-ui-sortable');

	// Enfileirar o estilo do jQuery UI
	wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css');
}
add_action('wp_enqueue_scripts', 'enqueue_jquery_ui_in_frontend');



// TODO: Inclui os arquivos de funções
require_once MV_PLUGIN_DIR . 'includes/functions.php';
require_once MV_PLUGIN_DIR . 'includes/dynamic-fields.php';
require_once MV_PLUGIN_DIR . 'includes/Admin/admin.php';
