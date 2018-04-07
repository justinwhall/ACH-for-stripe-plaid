<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              htps://www.justinwhall.com
 * @since             1.0.0
 * @package           Wp_Stripe_Plaid
 *
 * @wordpress-plugin
 * Plugin Name:       LittleBot ACH for Stripe + Plaid
 * Plugin URI:        https://www.littlbot.io/plugins/wp-stripe-plaid-ach-wordpress-plugin
 * Description:       Accept Stripe ACH payments with Stripe + Plaid.
 * Version:           1.1.4
 * Author:            Justin W Hall
 * Author URI:        https://www.littlebot.io
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-stripe-plaid
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WP_STRIPE_PLAID_PATH', plugin_dir_path( __FILE__ ) );
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-stripe-plaid-activator.php
 */
function activate_wp_stripe_plaid() {
	require_once WP_STRIPE_PLAID_PATH . 'includes/class-wp-stripe-plaid-activator.php';
	Wp_Stripe_Plaid_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-stripe-plaid-deactivator.php
 */
function deactivate_wp_stripe_plaid() {
	require_once WP_STRIPE_PLAID_PATH . 'includes/class-wp-stripe-plaid-deactivator.php';
	Wp_Stripe_Plaid_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wp_stripe_plaid' );
register_deactivation_hook( __FILE__, 'deactivate_wp_stripe_plaid' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require WP_STRIPE_PLAID_PATH . 'includes/class-wp-stripe-plaid.php';
require WP_STRIPE_PLAID_PATH . 'admin/class-wp-stripe-plaid-settings-api.php';
require WP_STRIPE_PLAID_PATH . 'vendor/autoload.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wp_stripe_plaid() {

	$plugin = new Wp_Stripe_Plaid();
	$plugin->run();

}
run_wp_stripe_plaid();
