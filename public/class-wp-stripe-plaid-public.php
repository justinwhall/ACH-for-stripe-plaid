<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       htps://www.justinwhall.com
 * @since      1.0.0
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
		var_dump( $this->settings );
		add_shortcode( 'wp_stripe_plaid', array( $this, 'render_form' ) );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Stripe_Plaid_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Stripe_Plaid_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-stripe-plaid-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Stripe_Plaid_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Stripe_Plaid_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-stripe-plaid-public.js', array( 'jquery' ), $this->version, true );
		wp_localize_script($this->plugin_name, 'ajax_object', array( 'ajax_url' => admin_url('admin-ajax.php'), 'ajax_nonce' => wp_create_nonce('stripe_plaid_nonce') ) );

	}

	public function render_form(){
		
		?>
		<div class="sp-field-wrap">
			<label>Amount</label><br/>
			<input type="number" id="sp-amount">
		</div>

		<div class="sp-field-wrap">
			<label>Note</label><br/>
			<input type="text" id="sp-desc">
		</div>

		<div>
			<button data-publickey="<?php echo $this->settings['plaid_public_key']; ?>" id='linkButton'>Select Bank Account</button>
			<button  id='sp-pay'>Pay</button>
		</div>

		<div id="sp-response"></div>


		<script src="https://cdn.plaid.com/link/stable/link-initialize.js"></script>
		<?php

	}

	public function call_stripe( $amount, $currency, $source, $description ){

		// Live or test?
		$stripe_key = ( $this->settings['sp_environment'] === 'live' ) ? $this->settings['stripe_live_api_key'] : $this->settings['stripe_test_api_key'];
		\Stripe\Stripe::setApiKey( $stripe_key );

		$return = array( 'error' => false );

		try {

		  $charge = \Stripe\Charge::create(array(
		    "amount"      => $amount,
		    "currency"    => $currency,
		    "source"      => $source,
		    "description" => $description
		  ));

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
		// TODO: add option to turn this on/off.
		if ( $return['error'] ) {
			$message = 'DESCRIPTION: ' . $description . ' CHARGE: ' . $amount . ' TYPE: ' . $return['error']['type'] . ' PARAM: ' . $return['error']['param'] . ' MESSAGE: ' . $return['error']['message'];
			Wp_Stripe_Plaid_Public::write_error( $message );
		}

		return $return;

	}

	public function call_plaid(){

		check_ajax_referer('stripe_plaid_nonce', 'nonce');

		$data = array(
	        'client_id'    => $this->settings['plaid_client_id'],
	        'secret'       => $this->settings['plaid_secret'],
	        'public_token' => $_POST['public_token'],
	        'account_id'   => $_POST['account_id']
		 );

		$string = http_build_query( $data );

		//initialize session
		$ch = curl_init( "https://tartan.plaid.com/exchange_token" );

		//set options
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $string );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

		//execute session
		$keys = curl_exec( $ch );
		$keys = json_decode( $keys );
		//close session
		curl_close( $ch );

		$charge = $this->call_stripe( $_POST['amount'], 'USD', $keys->stripe_bank_account_token, $_POST['description'] );

		wp_send_json( $charge );
		wp_die();

	}

	static function write_error( $message ) {
		
		$ts = date( '[ m.d.Y | H:i:s e ]' );
		$message = $ts . " - " . $message . "\n" ;
		error_log( $message, 3, WP_STRIPE_PLAID_PATH . 'errorlog.log');

	}

}
