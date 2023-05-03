<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://example.com/
 * @since             1.0.0
 * @package           Currency_Pairs
 *
 * @wordpress-plugin
 * Plugin Name:       Currency Pairs
 * Plugin URI:        https://example.com/
 * Description:       Displays pair currency information
 * Version:           1.0.0
 * Author:            Access24
 * Author URI:        https://example.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       currency-pairs
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}	

define( 'CURRENCY_PAIRS_VERSION', '1.0.0' );
define( 'CURRENCY_PAIRS_ROOT_DIR_ADMIN', dirname(__FILE__).'/admin' );
define( 'CURRENCY_PAIRS_ROOT_DIR_PUBLIC', plugin_dir_url(__FILE__).'/public' );
define( 'CURRENCY_PAIRS_PLUGIN_DIR_URL', plugin_dir_url(__FILE__).'admin/' );
define( 'CURRENCY_PAIRS_PLUGIN_DIR', dirname(__FILE__) );
define( 'CURRENCY_CONVERTER_KEY', 'eecda7a6439543009d528a95754da7b0' );


function activate_currency_pairs() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-currency-pairs-activator.php';
	Currency_Pairs_Activator::activate();
}

function deactivate_currency_pairs() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-currency-pairs-deactivator.php';
	Currency_Pairs_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_currency_pairs' );
register_deactivation_hook( __FILE__, 'deactivate_currency_pairs' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-currency-pairs.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_currency_pairs() {

	$plugin = new Currency_Pairs();
	$plugin->run();

}
run_currency_pairs();
