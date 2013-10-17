<?php
/**
 * Constants used by this plugin
 * 
 * @package Drip
 * @author Samuel Hulick <service@getdrip.com>
 * @version 1.0.0
 * @since 1.0.0
 */

// The current version of this plugin
if( !defined( 'WP_DRIP_VERSION' ) ) define( 'WP_DRIP_VERSION', '1.0.0' );

// The directory the plugin resides in
if( !defined( 'WP_DRIP_DIRNAME' ) ) define( 'WP_DRIP_DIRNAME', dirname( dirname( __FILE__ ) ) );

// The URL path of this plugin
if( !defined( 'WP_DRIP_URLPATH' ) ) define( 'WP_DRIP_URLPATH', WP_PLUGIN_URL . "/" . plugin_basename( WP_DRIP_DIRNAME ) );