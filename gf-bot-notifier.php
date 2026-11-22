<?php
/**
 * Plugin Name:       GF Bot Notifier
 * Description:       Send Gravity Forms submissions to Telegram and Rubika bots, with a background queue, automatic retries and delivery logs.
 * Version:           2.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Mahan Banibayrami
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gf-bot-notifier
 * Domain Path:       /languages
 *
 * @package GF_Bot_Notifier
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GFBN_VERSION', '2.0.0' );
define( 'GFBN_DB_VERSION', '2' );
define( 'GFBN_FILE', __FILE__ );
define( 'GFBN_PATH', plugin_dir_path( __FILE__ ) );
define( 'GFBN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Notice shown when the server is too old.
 */
function gfbn_requirements_notice() {
	echo '<div class="notice notice-error"><p><strong>GF Bot Notifier</strong>: requires PHP 7.4+ and WordPress 5.8+. The plugin has not been loaded.</p></div>';
}

if ( version_compare( PHP_VERSION, '7.4', '<' ) || version_compare( $GLOBALS['wp_version'], '5.8', '<' ) ) {
	add_action( 'admin_notices', 'gfbn_requirements_notice' );
	return;
}

$gfbn_includes = array(
	'util',
	'result',
	'settings',
	'http',
	'service',
	'telegram',
	'rubika',
	'forms',
	'message',
	'logs',
	'queue',
	'installer',
	'admin',
);
foreach ( $gfbn_includes as $gfbn_file ) {
	require_once GFBN_PATH . 'includes/class-gfbn-' . $gfbn_file . '.php';
}
unset( $gfbn_includes, $gfbn_file );

register_activation_hook( __FILE__, array( 'GFBN_Installer', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'GFBN_Installer', 'deactivate' ) );

/**
 * Load translations.
 */
function gfbn_load_textdomain() {
	load_plugin_textdomain( 'gf-bot-notifier', false, dirname( plugin_basename( GFBN_FILE ) ) . '/languages' );
}
add_action( 'init', 'gfbn_load_textdomain', 1 );
add_action( 'init', array( 'GFBN_Installer', 'maybe_upgrade' ), 5 );

GFBN_Queue::init();
if ( is_admin() ) {
	GFBN_Admin::init();
}
