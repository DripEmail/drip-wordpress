<?php
/**
 * Email Marketing by Drip
 * 
 * The official Drip Wordpress plugin.
 * 
 * @package Drip
 * @global object $WP_Drip
 * @author Samuel Hulick <service@drip.com>
 * @version 1.0.0
 */
/*
Plugin Name: Email Marketing by Drip
Plugin URI: http://www.getdrip.com/?utm_source=wp-plugin&utm_medium=link&utm_campaign=plugins
Description: Instantly include the Drip tracking script so you can use email and years of best practices to create a double-digit jump in your conversion rate.
Version: 1.0.0
Author: Drip
Author URI: http://www.getdrip.com
License: GPLv2
*/

// Include constants file
require_once( dirname( __FILE__ ) . '/lib/constants.php' );

class WP_Drip {
	var $namespace = "drip";
	var $friendly_name = "Drip";
	var $version = "1.0.0";
	var $options_name = "drip_options";
	var $options;
	var $settings_path;
	
	/**
	 * Instantiate a new instance
	 * 
	 * @uses get_option()
	 */
	public function __construct() {
		// Store the settings page path
		$this->settings_path = 'options-general.php?page=' . $this->namespace;
		
		// Fetch options
		$this->options = get_option( $this->options_name );
		
		// Load all library files used by this plugin
		$libs = glob( WP_DRIP_DIRNAME . '/lib/*.php' );
		foreach( $libs as $lib ) {
			include_once( $lib );
		}
		
		// Register hooks
		$this->_add_hooks();
	}
	
	/**
	 * Sets default options upon activation
	 *
	 * Hook into register_activation_hook action
	 *
	 * @uses update_option()
	 */
	public function activate() {
		// Set default options
		if ( ! isset( $this->options['account_id'] ) ) { $this->options['account_id'] = ""; }
		
		// Redirect to settings page
		$this->options['do_redirect'] = true;

		// Save options
		update_option( $this->options_name, $this->options );
		
		// Redirect to settings page
		wp_redirect($this->settings_path);
	}
	
	/**
	 * Clean up after deactivation
	 *
	 * Hook into register_deactivation_hook action
	 */
	public function deactivate() {
		// Deactivation stuff here...
	}
	
	/**
	 * Add various hooks and actions here
	 *
	 * @uses add_action()
	 */
	private function _add_hooks() {
		// Activation and deactivation
		register_activation_hook( __FILE__, array( &$this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( &$this, 'deactivate' ) );
		
		// Options page for configuration
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );

		// Register admin settings
		add_action( 'admin_init', array( &$this, 'admin_register_settings' ) );
		
		// Register activation redirect
		add_action( 'admin_init', array( &$this, 'do_activation_redirect' ) );
		
		// Add settings link on plugins page
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( &$this, 'plugin_action_links' ) );

		// Place tracking code in the footer
		add_action( 'wp_footer', array( &$this, 'tracking_code' ) );
	}
	
	/**
	 * Lookup an option from the options array
	 *
	 * @param string $key The name of the option you wish to retrieve
	 *
	 * @return mixed Returns the option value or NULL if the option is not set or empty
	 */
	public function get_option( $key ) {
		if ( isset( $this->options[ $key ] ) && $this->options[ $key ] != "" ) {
			return $this->options[ $key ];
		} else {
			return NULL;
		}
	}
	
	/**
	 * Deletes an option from the options array
	 *
	 * @param string $key The name of the option you wish to delete
	 *
	 * @uses update_option()
	 */
	public function delete_option( $key ) {
		unset( $this->options[ $key ] );
		update_option( $this->options_name, $this->options );
	}
	
	/**
	 * Look up the account ID from the options array
	 *
	 * @return mixed Returns the account ID or NULL if it is not set or empty
	 */
	public function account_id() {
		return $this->get_option( 'account_id' );
	}
	
	/**
	 * Output the Drip tracking script
	 *
	 * Displays nothing if tracking script is disabled or the account ID is not set.
	 */
	public function tracking_code() {
		// Check if the ID is set and is an integer
		if ( ! $this->get_option( 'is_disabled' ) ) {
			if ( $this->get_option( 'account_id' ) ) { 
				$account_id = $this->get_option( 'account_id' );
				include( WP_DRIP_DIRNAME . "/views/tracking-code.php" );
			} else {
				echo '<!-- Drip: Set your account ID to begin tracking -->';
			}
		}
	}
	
	/**
	 * Performs a redirect to the settings page if the flag is set.
	 * To be called on admin_init action.
	 *
	 * @uses wp_redirect()
	 */
	public function do_activation_redirect() {
		if ( $this->get_option( 'do_redirect' ) ) {
			// Prevent future redirecting
			$this->delete_option( 'do_redirect' );
			
			// Only redirect if it's a single activation
			if( ! isset( $_GET['activate-multi'] ) ) {
				wp_redirect( $this->settings_path );
			}
		}
	}
	
	/**
	 * Define the admin menu options for this plugin
	 * 
	 * @uses add_action()
	 * @uses add_options_page()
	 */
	public function admin_menu() {
		$page_hook = add_options_page( 'Drip Settings', $this->friendly_name, 'manage_options', $this->namespace, array( &$this, 'admin_options_page' ) );
		
		// Add admin scripts and styles
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );
	}
	
	/**
	 * The admin section options page rendering method
	 * 
	 * @uses current_user_can()
	 * @uses wp_die()
	 */
	public function admin_options_page() {
		// Ensure the user has sufficient permissions
		if ( ! current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		
		include( WP_DRIP_DIRNAME . "/views/options.php" );
	}
	
	/**
	 * Add links on the plugin page
	 *
	 * @param array $links An array of existing action links
	 * 
	 * @uses current_user_can()
	 * @return array Returns the new array of links
	 */
	public function plugin_action_links( $links ) {
		// Ensure the user has sufficient permissions
		if ( current_user_can( 'manage_options' ) )  {
			$settings_link = '<a href="' . $this->settings_path . '">Settings</a>';
			array_unshift($links, $settings_link);
		}
		
		return $links;
	}
	
	/**
	 * Register all the settings for the options page (Settings API)
	 *
	 * @uses register_setting()
	 * @uses add_settings_section()
	 * @uses add_settings_field()
	 */
	public function admin_register_settings() {
		register_setting( $this->options_name, $this->options_name, array( &$this, 'validate_settings' ) );
		add_settings_section( 'drip_code_settings', 'Tracking Code', array( &$this, 'admin_section_code_settings' ), $this->namespace );
		add_settings_field( 'drip_account_id', 'Account ID', array( &$this, 'admin_option_account_id' ), $this->namespace, 'drip_code_settings' );
		add_settings_field( 'drip_is_disabled', 'Visibility', array( &$this, 'admin_option_is_disabled' ), $this->namespace, 'drip_code_settings' );
	}
	
	/**
	 * Validates user supplied settings and sanitizes the input
	 *
	 * @param array $input The set of option parameters
	 *
	 * @return array Returns the set of sanitized options to save to the database
	 */
	public function validate_settings( $input ) {
		$options = $this->options;
		
		if ( isset( $input['account_id'] ) ) {
			// Remove padded whitespace
			$account_id = trim( $input['account_id'] );
			
			// Only allow an integer or blank string
			if ( is_int( $account_id ) || ctype_digit( $account_id ) || $account_id == "" ) {
				$options['account_id'] = $account_id;
			} else {
				add_settings_error( 'account_id', $this->namespace . '_account_id_error', "Please enter a valid account ID", 'error' );
			}
		}
		
    if ( isset( $input['is_disabled'] ) ) {
      $options['is_disabled'] = $input['is_disabled'] == "1";
    } else {
      $options['is_disabled'] = false;
    }
    
		return $options;
	}
	
	/** 
	 * Output the input for the account ID option
	 */
	public function admin_option_account_id() {
		echo "<input type='text' name='drip_options[account_id]' size='20' value='{$this->get_option( 'account_id' )}'>";
	}
	
	/** 
	 * Output the input for the disabled option
	 */
	public function admin_option_is_disabled() {
		echo "<label><input type='checkbox' name='drip_options[is_disabled]' value='1' " . 
			checked( 1, $this->get_option( 'is_disabled' ), false ) . " /> " .
			"Disable tracking code on all pages</label>";
	}
	
	/** 
	 * Output the description for the Tracking Code settings section
	 */
	public function admin_section_code_settings() {
		echo '<p>You can find your account ID under <a href="http://getdrip.com/settings/site" target="_blank">Settings &rarr; Site Setup</a> in your Drip account.</p>';
	}
	
	/**
	 * Load stylesheet for the admin options page
	 * 
	 * @uses wp_enqueue_style()
	 */
	function admin_enqueue_scripts() {
		wp_enqueue_style( "{$this->namespace}_admin_css", WP_DRIP_URLPATH . "/css/admin.css" );
	}
	
	/**
	 * Initialization function to hook into the WordPress init action
	 * 
	 * Instantiates the class on a global variable and sets the class, actions
	 * etc. up for use.
	 */
	static function instance() {
		global $WP_Drip;
		
		// Only instantiate the Class if it hasn't been already
		if( ! isset( $WP_Drip ) ) $WP_Drip = new WP_Drip();
	}
}

if( !isset( $WP_Drip ) ) {
	WP_Drip::instance();
}