<?php
/**
 * Plugin Name: Dokan Custom Fields
 * Plugin URI: https://mariomelembe.com
 * Description: Adiciona campos personalizados ao registo de vendedores no Dokan.
 * Version: 1.0.0
 * Author: Mario Melembe
 * Author URI: https://mariomelembe.com
 *  Requires Plugins: dokan-lite
 * License: GPL2
 */

// TODO: don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define o diretório do plugin
if ( ! defined( 'MV_PLUGIN_DIR' ) ) {
	define( 'MV_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

// Inclui os arquivos de funções
require_once MV_PLUGIN_DIR . 'includes/functions.php';
require_once MV_PLUGIN_DIR . 'includes/dynamic-fields.php';
require_once MV_PLUGIN_DIR . 'includes/Admin/admin.php';






