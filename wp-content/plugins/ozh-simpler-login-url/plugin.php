<?php
/*
Plugin Name: Ozh' Simpler Login URL
Plugin URI: http://planetozh.com/blog/2011/01/pretty-login-url-a-simple-rewrite-api-plugin-example/
Description: Pretty Login URL: /login instead of /wp-login.php (a Rewrite API example)
Version: 0.1
Author: Ozh
Author URI: http://ozh.org/
*/

// Add rewrite rule and flush on plugin activation
register_activation_hook( __FILE__, 'wp_ozh_plu_activate' );
function wp_ozh_plu_activate() {
	wp_ozh_plu_rewrite();
	flush_rewrite_rules();
}

// Flush on plugin deactivation
register_deactivation_hook( __FILE__, 'wp_ozh_plu_deactivate' );
function wp_ozh_plu_deactivate() {
	flush_rewrite_rules();
}

// Create new rewrite rule
add_action( 'init', 'wp_ozh_plu_rewrite' );
function wp_ozh_plu_rewrite() {
	add_rewrite_rule( 'login/?$', 'wp-login.php', 'top' );
}