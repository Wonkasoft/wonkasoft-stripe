<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wonkasoft.com
 * @since             1.0.0
 * @package           Wonkasoft_Stripe
 *
 * @wordpress-plugin
 * Plugin Name:       Wonkasoft Stripe Payments
 * Plugin URI:        https://wonkasoft.com/wonkasoft-stripe
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Wonkasoft
 * Author URI:        https://wonkasoft.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wonkasoft-stripe
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WONKASOFT_STRIPE_PATH', plugin_dir_path( __FILE__ ) );
define( 'WONKASOFT_STRIPE_URL', plugin_dir_url( __FILE__ ) );
define( 'WONKASOFT_STRIPE_SLUG', plugin_basename( dirname( __FILE__ ) ) );
define( 'WONKASOFT_STRIPE_FULLNAME', 'Wonkasoft Stripe Integration' );
define( 'WONKASOFT_STRIPE_NAME', 'Stripe Integration' );
define( 'WONKASOFT_STRIPE_BASENAME', plugin_basename( __FILE__ ) );
define( 'WONKASOFT_STRIPE_IMG_PATH', plugins_url( WONKASOFT_STRIPE_SLUG . '/admin/img' ) );
define( 'WONKASOFT_STRIPE_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wonkasoft-stripe-activator.php
 */
function activate_wonkasoft_stripe() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wonkasoft-stripe-activator.php';
	Wonkasoft_Stripe_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wonkasoft-stripe-deactivator.php
 */
function deactivate_wonkasoft_stripe() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wonkasoft-stripe-deactivator.php';
	Wonkasoft_Stripe_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wonkasoft_stripe' );
register_deactivation_hook( __FILE__, 'deactivate_wonkasoft_stripe' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wonkasoft-stripe.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wonkasoft_stripe() {

	$plugin = new Wonkasoft_Stripe();
	$plugin->run();

}
run_wonkasoft_stripe();
