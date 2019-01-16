<?php

/**
 * Fired during plugin activation
 *
 * @link       htps://www.justinwhall.com
 * @since      1.0.0
 *
 * @package    Wp_Stripe_Plaid
 * @subpackage Wp_Stripe_Plaid/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wp_Stripe_Plaid
 * @subpackage Wp_Stripe_Plaid/includes
 * @author     Justin W Hall <justin@windsorup.com>
 */
class Wp_Stripe_Plaid_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		$options = get_option( 'stripe_plaid_settings' );

		if ( ! $options ) {
			$settings = array(
				'sp_environment'      => 'live',
				'stripe_live_api_key' => '',
				'stripe_test_api_key' => '',
				'plaid_client_id'     => '',
				'plaid_secret'        => '',
				'plaid_public_key'    => '',
				'log'                 => 'off',
				'form_auth'           => 'public',
			);

			update_option( 'stripe_plaid_settings', $settings );
		}
	}

}
