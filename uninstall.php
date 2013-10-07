<?php
/**
 * Uninstall procedures
 * 
 * @package Drip
 * @author Samuel Hulick <service@drip.com>
 * @version 1.0.2
 * @since 1.0.0
 */

// Exit if not called from WordPress
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit; }

// Remove options
delete_option( 'drip_options' );