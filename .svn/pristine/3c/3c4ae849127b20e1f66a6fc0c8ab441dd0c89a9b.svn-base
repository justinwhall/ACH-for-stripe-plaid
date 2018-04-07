<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       htps://www.justinwhall.com
 * @since      1.0.1
 *
 * @package    Wp_Stripe_Plaid
 * @subpackage Wp_Stripe_Plaid/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_Stripe_Plaid
 * @subpackage Wp_Stripe_Plaid/public
 * @author     Justin W Hall <justin@windsorup.com>
 */
class Wp_Stripe_Plaid_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The plugin settings.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $settings    The current settings of this plugin.
	 */
	private $settings;

	/**
	 * The plugin settings.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $user_message  holds message(s) for user if they don't have Stripe or Plaid creds.
	 */
	private $user_message = array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->settings = get_option( 'stripe_plaid_settings' );
		$this->has_creds();
		add_shortcode( 'wp_stripe_plaid', array( $this, 'render_form' ) );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_register_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-stripe-plaid-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_register_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-stripe-plaid-public.js', array( 'jquery', 'stripe_plaid' ), $this->version, true );
		wp_register_script( 'stripe_plaid', 'https://cdn.plaid.com/link/v2/stable/link-initialize.js', array(), null, true );
		wp_localize_script($this->plugin_name, 'ajax_object', array( 'ajax_url' => admin_url('admin-ajax.php'), 'ajax_nonce' => wp_create_nonce('stripe_plaid_nonce') ) );

	}

	public function has_creds(){

		// check for test creds if in test mode, otherwise live key
		if ( $this->settings['sp_environment'] === "test" ) {

			if ( !strlen( trim( $this->settings['stripe_test_api_key'] ) ) ) {
				$this->user_message[] = 'Missing Stripe test API Key';
			}

		} else {

			if ( !strlen( trim( $this->settings['stripe_live_api_key'] ) ) ) {
				$this->user_message[] = 'Missing Stripe live API Key';
			}

		}

		// Plaid keys
		if ( !strlen( trim( $this->settings['plaid_client_id'] ) ) ) {
			$this->user_message[] = 'Missing Plaid client ID';
		}

		if ( !strlen( trim( $this->settings['plaid_secret'] ) ) ) {
			$this->user_message[] = 'Missing Plaid Secret';
		}

		if ( !strlen( trim( $this->settings['plaid_public_key'] ) ) ) {
			$this->user_message[] = 'Missing Plaid public key';
		}


	}

	public function render_form(){

		if ( !is_user_logged_in() ) {
			ob_start();
			printf( '<div class="lb-ach-not-logged-in" ><a href="%s">Login to make a payment</a></div>', wp_login_url( get_the_permalink() ) );
			return ob_get_clean();
		}

		else{

			wp_enqueue_script( $this->plugin_name );
			wp_enqueue_script( 'stripe_plaid' );
			wp_enqueue_style( $this->plugin_name );

			if ( empty( $this->user_message ) ) {
				$env = ( $this->settings['sp_environment'] === 'live' ) ? 'production' : 'sandbox';
				$amount = ( isset( $_GET['amount']  ) ) ? (float) $_GET['amount'] : '';
				$user = wp_get_current_user();
				ob_start();
			?>
				<form action="javascript:void(0);" id="sc-form" data-env="<?php echo $env;  ?>" novalidate>

					<input id="lb-ach-email" type="hidden" value="<?php echo $user->data->user_email; ?>" >

					<div class="sp-field-wrap">
						<label>Amount</label><br/>
						<input type="number" value="<?php echo $amount; ?>" id="sp-amount" >
					</div>

					<div class="sp-field-wrap">
						<label>Note</label><br/>
						<input type="text" id="sp-desc">
					</div>

					<div>
						<button data-publickey="<?php echo $this->settings['plaid_public_key']; ?>" id='linkButton'>Select Bank Account</button>
						<button  id='sp-pay'>Pay</button>
						<div class="sp-spinner">
						  <div class="double-bounce1"></div>
						  <div class="double-bounce2"></div>
						</div>
					</div>
				</form>

				<div id="sp-response"></div>

			<?php
			return ob_get_clean();
			} else {

				foreach ( $this->user_message as $message ) {
					echo '- ' . $message . '<br />';
				}

			}

		}

	}

	public function call_stripe( $amount, $currency, $token, $description, $email ){

		// Live or test?
		$stripe_key = ( $this->settings['sp_environment'] === 'live' ) ? $this->settings['stripe_live_api_key'] : $this->settings['stripe_test_api_key'];
		$meta_key = '_lb_ach_' . $this->settings['sp_environment'] . '_customer';
		$current_user = wp_get_current_user();
		$return = array( 'error' => false );

		\Stripe\Stripe::setApiKey( $stripe_key );
		$stripe_customer_id = get_user_meta( $current_user->ID, $meta_key, true );


		if (!empty($stripe_customer_id)) {
			$customer = \Stripe\Customer::retrieve($stripe_customer_id);
		} else {
			// Create a Customer:
			$customer = \Stripe\Customer::create(array(
				'email' => $current_user->user_email,
				'source' => $token,
				'description' => 'WordPress User: ' . $current_user->user_login
			));

			$stripe_customer_id = $customer->id;

			//Add to user's meta
			update_user_meta($current_user->ID, $meta_key, $stripe_customer_id);
		}



		// Figure out if the user is using a stored bank account or a new bank account by comparing bank account fingerprints
		$token_data = \Stripe\Token::retrieve($token);

		$this_bank_account = $token_data['bank_account'];
		$cust_banks = $customer['sources']['data'];

		foreach ($cust_banks as $bank) {
			if ($bank['fingerprint'] == $this_bank_account['fingerprint']) {
				$source = $bank['id'];
			}
		}

		// If this bank is not an existing one, we'll add it
		if ($source == false) {
			$new_source = $customer->sources->create(array('source' => $token));
			$source = $new_source['id'];
		}

		// Try to authorize the bank
		$charge_args = array(
			'amount' => $amount,
			'currency' => 'usd',
			'description' => $description,
			'customer' => $stripe_customer_id,
			'source' => $source
		 );


		try {

			$charge = \Stripe\Charge::create($charge_args);

		} catch(\Stripe\Error\Card $e) {

			// Since it's a decline, \Stripe\Error\Card will be caught
			$return = $e->getJsonBody();

		} catch (\Stripe\Error\RateLimit $e) {
			// Too many requests made to the API too quickly
			$return = $e->getJsonBody();

		} catch (\Stripe\Error\InvalidRequest $e) {

			// Invalid parameters were supplied to Stripe's API
			$return = $e->getJsonBody();

		} catch (\Stripe\Error\Authentication $e) {

			// Authentication with Stripe's API failed
			$return = $e->getJsonBody();

		} catch (\Stripe\Error\ApiConnection $e) {

			// Network communication with Stripe failed
			$return = $e->getJsonBody();

		} catch (\Stripe\Error\Base $e) {

			// Display a very generic error to the user, and maybe send
			$return = $e->getJsonBody();

		} catch (Exception $e) {

			// Something else happened, completely unrelated to Stripe
			$return = $e->getJsonBody();

		}

		// log error if there is any.
		if ( $return['error'] && $this->settings['log'] === 'on' ) {
			$message = 'DESCRIPTION: ' . $description . ' CHARGE: ' . $amount . ' TYPE: ' . $return['error']['type'] . ' PARAM: ' . $return['error']['param'] . ' MESSAGE: ' . $return['error']['message'];
			Wp_Stripe_Plaid_Public::write_error( $message );
		}

		return $return;

	}

	public function call_plaid(){

		check_ajax_referer('stripe_plaid_nonce', 'nonce');

		$env = ( $this->settings['sp_environment'] === 'live' ) ? 'production' : 'sandbox';
		$headers[] = 'Content-Type: application/json';

		$params = array(
			'client_id'    => $this->settings['plaid_client_id'],
			'secret'       => $this->settings['plaid_secret'],
			'public_token' => $_POST['public_token']
		 );

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://" . $env . ".plaid.com/item/public_token/exchange");
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_TIMEOUT, 80);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		if(!$result = curl_exec($ch)) {
			trigger_error(curl_error($ch));
		}
		curl_close($ch);

		$jsonParsed = json_decode($result);


		$btok_params = array(
			'client_id'    => $this->settings['plaid_client_id'],
			'secret'       => $this->settings['plaid_secret'],
			'access_token' => $jsonParsed->access_token,
			'account_id'   => $_POST['account_id']
		 );

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://" . $env . ".plaid.com/processor/stripe/bank_account_token/create");
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($btok_params));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_TIMEOUT, 80);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		if(!$result = curl_exec($ch)) {
			trigger_error(curl_error($ch));
		}
		curl_close($ch);

		$btoken = json_decode($result);

		$charge = $this->call_stripe( $_POST['amount'], 'USD', $btoken->stripe_bank_account_token, $_POST['description'], $_POST['email'] );

		wp_send_json( $charge );

		wp_die();

	}

	static function write_error( $message ) {

		$ts = date( '[ m.d.Y | H:i:s e ]' );
		$message = $ts . " - " . $message . "\n" ;
		error_log( $message, 3, WP_STRIPE_PLAID_PATH . 'errorlog.log');

	}

}
