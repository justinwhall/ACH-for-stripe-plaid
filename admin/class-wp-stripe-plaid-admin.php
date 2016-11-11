<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       htps://www.justinwhall.com
 * @since      1.0.0
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

		var_dump( $this->settings );

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
	        	    'label'   => __( 'Environment', $plugin_name ),
	        	    'desc'    => __( 'Live or test modes?', $plugin_name ),
	        	    'type'    => 'radio',
	        	    'options' => array(
	        	        'live' => 'Live',
	        	        'test'  => 'Test'
	        	    )
	        	),
	            array(
	                'name'              => 'stripe_live_api_key',
	                'label'             => __( 'Stripe Secret LIVE Key', $plugin_name ),
	                'desc'              => __( '', $plugin_name ),
	                'placeholder'       => __( '', $plugin_name ),
	                'type'              => 'text',
	                'default'           => '',
	                'sanitize_callback' => 'sanitize_text_field'
	            ),
	            array(
	                'name'              => 'stripe_test_api_key',
	                'label'             => __( 'Stripe Secret TEST Key', $plugin_name ),
	                'desc'              => __( '', $plugin_name ),
	                'placeholder'       => __( '', $plugin_name ),
	                'type'              => 'text',
	                'default'           => '',
	                'sanitize_callback' => 'sanitize_text_field'
	            ),
	            array(
	                'name'        => 'stripe_help',
	                'desc'        => __( 'Stripe keys are located: <a href="https://dashboard.stripe.com/account/apikeys">https://dashboard.stripe.com/account/apikeys</a>', $plugin_name ),
	                'type'        => 'html'
	            ),
	            array(
	                'name'              => 'plaid_client_id',
	                'label'             => __( 'Plaid Client ID', $plugin_name ),
	                'desc'              => __( '', $plugin_name ),
	                'placeholder'       => __( '', $plugin_name ),
	                'type'              => 'text',
	                'default'           => '',
	                'sanitize_callback' => 'sanitize_text_field'
	            ),
	            array(
	                'name'              => 'plaid_secret',
	                'label'             => __( 'Plaid Secret', $plugin_name ),
	                'desc'              => __( '', $plugin_name ),
	                'placeholder'       => __( '', $plugin_name ),
	                'type'              => 'text',
	                'default'           => '',
	                'sanitize_callback' => 'sanitize_text_field'
	            ),
	            array(
	                'name'              => 'plaid_public_key',
	                'label'             => __( 'Plaid Public Key', $plugin_name ),
	                'desc'              => __( '', $plugin_name ),
	                'placeholder'       => __( '', $plugin_name ),
	                'type'              => 'text',
	                'default'           => '',
	                'sanitize_callback' => 'sanitize_text_field'
	            ),
	            array(
	                'name'        => 'plaid_help',
	                'desc'        => __( 'Plaid keys are located: <a href="https://dashboard.plaid.com/account/keys">https://dashboard.plaid.com/account/keys</a>', $plugin_name ),
	                'type'        => 'html'
	            ),



	            array(
	                'name'              => 'number_input',
	                'label'             => __( 'Number Input', $plugin_name ),
	                'desc'              => __( 'Number field with validation callback `floatval`', $plugin_name ),
	                'placeholder'       => __( '1.99', $plugin_name ),
	                'min'               => 0,
	                'max'               => 100,
	                'step'              => '0.01',
	                'type'              => 'number',
	                'default'           => 'Title',
	                'sanitize_callback' => 'floatval'
	            ),
	            array(
	                'name'        => 'textarea',
	                'label'       => __( 'Textarea Input', $plugin_name ),
	                'desc'        => __( 'Textarea description', $plugin_name ),
	                'placeholder' => __( 'Textarea placeholder', $plugin_name ),
	                'type'        => 'textarea'
	            ),

	            array(
	                'name'  => 'checkbox',
	                'label' => __( 'Checkbox', $plugin_name ),
	                'desc'  => __( 'Checkbox Label', $plugin_name ),
	                'type'  => 'checkbox'
	            ),
	            array(
	                'name'    => 'radio',
	                'label'   => __( 'Radio Button', $plugin_name ),
	                'desc'    => __( 'A radio button', $plugin_name ),
	                'type'    => 'radio',
	                'options' => array(
	                    'yes' => 'Yes',
	                    'no'  => 'No'
	                )
	            ),
	            array(
	                'name'    => 'selectbox',
	                'label'   => __( 'A Dropdown', $plugin_name ),
	                'desc'    => __( 'Dropdown description', $plugin_name ),
	                'type'    => 'select',
	                'default' => 'no',
	                'options' => array(
	                    'yes' => 'Yes',
	                    'no'  => 'No'
	                )
	            ),
	            array(
	                'name'    => 'password',
	                'label'   => __( 'Password', $plugin_name ),
	                'desc'    => __( 'Password description', $plugin_name ),
	                'type'    => 'password',
	                'default' => ''
	            ),
	            array(
	                'name'    => 'file',
	                'label'   => __( 'File', $plugin_name ),
	                'desc'    => __( 'File description', $plugin_name ),
	                'type'    => 'file',
	                'default' => '',
	                'options' => array(
	                    'button_label' => 'Choose Image'
	                )
	            )
	        )
	    );
	    return $settings_fields;
	}
	
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
