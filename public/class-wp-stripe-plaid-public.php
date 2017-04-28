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
			printf( '<div class="lb-ach-not-logged-in" ><a href="%s">Login to make a paymet</a></div>', wp_login_url( get_the_permalink() ) );
		} 

		else{

			wp_enqueue_script( $this->plugin_name );
			wp_enqueue_script( 'stripe_plaid' );
			wp_enqueue_style( $this->plugin_name );

			if ( empty( $this->user_message ) ) {
				$env = ( $this->settings['sp_environment'] === 'live' ) ? 'production' : 'sandbox';

				$user = wp_get_current_user();
			?>
				<form action="javascript:void(0);" id="sc-form" data-env="<?php echo $env;  ?>" novalidate>

					<input id="lb-ach-email" type="hidden" value="<?php echo $user->data->user_email; ?>" >

					<div class="sp-field-wrap">
						<label>Amount</label><br/>
						<input type="number" id="sp-amount" >
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
			} else {

				foreach ( $this->user_message as $message ) {
					echo '- ' . $message . '<br />';
				}

			}

		}

	}

	public function call_stripe( $amount, $currency, $source, $description, $email ){

		// Live or test?
		$stripe_key = ( $this->settings['sp_environment'] === 'live' ) ? $this->settings['stripe_live_api_key'] : $this->settings['stripe_test_api_key'];
		$meta_key = '_lb_ach_' . $this->settings['sp_environment'] . '_customer';

		\Stripe\Stripe::setApiKey( $stripe_key );

		$return = array( 'error' => false );

		try {

			$user = wp_get_current_user();
			$customer_id = get_user_meta( $user->ID, $meta_key, true );

			// Returning customer - charge the customer
			if ( $customer_id ) {
				
				$charge = \Stripe\Charge::create(array(
				  "amount" => $amount,
				  "currency" => $currency,
				  "customer" => $customer_id,
				  "description" => $description
				));

			} 
			// New customer. Create, charge and store customer token.
			else {

				// Create a Customer:
				$customer = \Stripe\Customer::create(array(
				  "email" => $email,
				  "source" => $source,
				  "description" => 'WordPress Login: ' . $user->user_login
				));

				// Charge the Customer instead of the card:
				$charge = \Stripe\Charge::create(array(
				  "amount" => $amount,
				  "currency" => $currency,
				  "customer" => $customer->id,
				  "description" => $description
				));

				// save customer meta
				add_user_meta( get_current_user_id(), $meta_key, $customer->id, true );
			}



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

		$data = array(
	        'client_id'    => $this->settings['plaid_client_id'],
	        'secret'       => $this->settings['plaid_secret'],
	        'public_token' => $_POST['public_token']
	        // 'account_id'   => $_POST['account_id']
		 );

		$string = json_encode(  $data );

		//initialize session
		$ch = curl_init( "https://" . $env . ".plaid.com/item/public_token/exchange" );

		//set options
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $string );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

		//execute session
		$keys = curl_exec( $ch );
		$keys = json_decode( $keys );
		//close session
		curl_close( $ch );

		$data = array(
	        'client_id'    => $this->settings['plaid_client_id'],
	        'secret'       => $this->settings['plaid_secret'],
	        'access_token' => $keys->access_token,
	        'account_id'   => $_POST['account_id']
		 );

		$string = json_encode(  $data );


		//initialize session
		$ch2 = curl_init( "https://" . $env . ".plaid.com/processor/stripe/bank_account_token/create" );

		//set options
		curl_setopt($ch2, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt( $ch2, CURLOPT_POST, true );
		curl_setopt( $ch2, CURLOPT_POSTFIELDS, $string );
		curl_setopt( $ch2, CURLOPT_RETURNTRANSFER, true );

		//execute session
		$btoken = curl_exec( $ch2 );
		$btoken = json_decode( $btoken );
		//close session
		curl_close( $ch2 );


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
