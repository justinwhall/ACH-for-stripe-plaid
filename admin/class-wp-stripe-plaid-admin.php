<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       htps://www.justinwhall.com
 * @since      1.0.1
 *
 * @package    Wp_Stripe_Plaid
 * @subpackage Wp_Stripe_Plaid/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_Stripe_Plaid
 * @subpackage Wp_Stripe_Plaid/admin
 * @author     Justin W Hall <justin@windsorup.com>
 */
class Wp_Stripe_Plaid_Admin {

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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->settings_api = new WeDevs_Settings_API;

		add_action( 'admin_init', array($this, 'admin_init') );
		add_action( 'admin_menu', array($this, 'admin_menu') );

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-stripe-plaid-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-stripe-plaid-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function admin_init() {
		//set the settings
		$this->settings_api->set_sections( $this->get_settings_sections() );
		$this->settings_api->set_fields( $this->get_settings_fields() );
		//initialize settings
		$this->settings_api->admin_init();
	}

	public function admin_menu() {
		add_options_page( 'Stripe + Plaid', 'Stripe + Plaid', 'delete_posts', 'stripe_plaid', array($this, 'plugin_page') );
	}

	public function get_settings_sections() {
		$sections = array(
			array(
				'id'    => 'stripe_plaid_settings',
				'title' => __( 'Settings', 'stripe_plaid' )
			),
			array(
				'id'    => 'stripe_plaid_log',
				'title' => __( 'Log', 'stripe_plaid' )
			)
		);
		return $sections;
	}
	/**
	 * Returns all the settings fields
	 *
	 * @return array settings fields
	 */
	public function get_settings_fields() {
		$settings_fields = array(
			'stripe_plaid_settings' => array(
				array(
					'name'    => 'sp_environment',
					'label'   => __( 'Environments', 'wp-stripe-plaid' ),
					'desc'    => __( 'FYI, Plaid allows for 100 live bank accounts in "development" mode. Selecting "Live (Stripe) Development (Plaid)" will use the live Stripe keys and make a live charge to a bank account.', 'wp-stripe-plaid' ),
					'type'    => 'radio',
					'options' => array(
						'live' => 'Live (Stripe) Production (Plaid)',
						'development'  => 'Live (Stripe) Development (Plaid)',
						'test'  => 'Test (Stripe) Sandbox (Plaid)',
					),
				),
				array(
					'name'    => 'form_auth',
					'label'   => __( 'Form Visablility', 'wp-stripe-plaid' ),
					'desc'    => __( 'Require users to be logged in to make a payment.', 'wp-stripe-plaid' ),
					'type'    => 'radio',
					'options' => array(
						'public' => 'No',
						'private'  => 'Yes',
					),
				),
				array(
					'name'              => 'stripe_live_api_key',
					'label'             => __( 'Stripe Secret LIVE Key', 'wp-stripe-plaid' ),
					'desc'              => __( '', 'wp-stripe-plaid' ),
					'placeholder'       => __( '', 'wp-stripe-plaid' ),
					'type'              => 'text',
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				),
				array(
					'name'              => 'stripe_test_api_key',
					'label'             => __( 'Stripe Secret TEST Key', 'wp-stripe-plaid' ),
					'desc'              => __( '', 'wp-stripe-plaid' ),
					'placeholder'       => __( '', 'wp-stripe-plaid' ),
					'type'              => 'text',
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				),
				array(
					'name'        => 'stripe_help',
					'desc'        => __( 'Stripe keys are located: <a target="_blank" href="https://dashboard.stripe.com/account/apikeys">https://dashboard.stripe.com/account/apikeys</a>', 'wp-stripe-plaid' ),
					'type'        => 'html'
				),
				array(
					'name'              => 'plaid_client_id',
					'label'             => __( 'Plaid Client ID', 'wp-stripe-plaid' ),
					'desc'              => __( '', 'wp-stripe-plaid' ),
					'placeholder'       => __( '', 'wp-stripe-plaid' ),
					'type'              => 'text',
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				),
				array(
					'name'              => 'plaid_public_key',
					'label'             => __( 'Plaid Public Key', 'wp-stripe-plaid' ),
					'desc'              => __( '', 'wp-stripe-plaid' ),
					'placeholder'       => __( '', 'wp-stripe-plaid' ),
					'type'              => 'text',
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				),
				array(
					'name'              => 'plaid_secret',
					'label'             => __( 'Plaid Secret', 'wp-stripe-plaid' ),
					'desc'              => __( '', 'wp-stripe-plaid' ),
					'placeholder'       => __( '', 'wp-stripe-plaid' ),
					'type'              => 'text',
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				),
				array(
					'name'        => 'plaid_help',
					'desc'        => __( 'Plaid keys are located: <a target="_blank" href="https://dashboard.plaid.com/account/keys">https://dashboard.plaid.com/account/keys</a>', 'wp-stripe-plaid' ),
					'type'        => 'html',
				),
				array(
					'name'  => 'log',
					'label' => __( 'Turn on logging', 'wp-stripe-plaid' ),
					'desc'  => __( 'Should not be left on indefinitely.', 'wp-stripe-plaid' ),
					'type'  => 'checkbox',
				),
			)

		);
		return $settings_fields;
	}

	/**
	 * Renders settings page.
	 *
	 * @return void
	 */
	public function plugin_page() {
		echo '<div class="wrap">';
		$this->settings_api->show_navigation();
		$this->settings_api->show_forms();
		echo '</div>';
	}
	/**
	 * Get all the pages
	 *
	 * @return array page names with key value pairs
	 */
	public function get_pages() {
		$pages = get_pages();
		$pages_options = array();
		if ( $pages ) {
			foreach ($pages as $page) {
				$pages_options[$page->ID] = $page->post_title;
			}
		}
		return $pages_options;
	}

}
