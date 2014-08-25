<?php
/*
 * Plugin Name: Hasher
 * Description: Monitor and record Twitter hashtags using WordPress
 * Plugin URI: http://github.com/philipjohn/hasher.git
 * Version: 0.1
 * Author: Philip John
 * Author URI: http://philipjohn.me.uk
 * Depends: lib-twitter-api, Extended CPTs
 */

/**
 * Require the Twitter API plugin
 */
function hasher_init() {

	// Check for the Dependencies plugin
	if ( ! is_plugin_active( 'wp-plugin-dependencies/plugin-dependencies.php' ) )
		die( __('Oops, you don\'t have the <a href="https://github.com/x-team/wp-plugin-dependencies">WP Dependencies Plugin</a> installed!') );

}

register_activation_hook( __FILE__, 'hasher_init' );

require 'inc/class.hasher.php';