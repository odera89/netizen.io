<?php
/*
Plugin Name: One Quick Post
Plugin URI: http://dev.pellicule.org/?page_id=19
Description: One Quick Post is a WordPress plugin that allows you to enable frontend posting on your blog; even for custom post types.
Version: 0.9.6.3-beta
Revision Date: April 25, 2012
Requires at least: WP 3.1
Tested up to: WP 3.3.2
License: (GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html
Author: G.Breant
Author URI: http://dev.pellicule.org
Site Wide Only: true
*/

//TO DO : default options
//TO FIX : no notice after posting with the shortcode under BP



/* Define a slug constant that will be used to view this components pages (http://example.org/SLUG) */
if ( !defined( 'OQP_SLUG' ) ) {
	define ( 'OQP_SLUG', 'one-quick-post' );

	define ( 'OQP_IS_INSTALLED', 1 );
	define ( 'OQP_DEBUG', true ); //set to one for testing
	define ( 'OQP_VERSION', '0.9.6.3' );

	define ( 'OQP_WORDPRESS_URL', 'http://wordpress.org/extend/plugins/one-quick-post/' );
	define ( 'OQP_SUPPORT_URL', 'http://dev.pellicule.org/bbpress/forum/one-quick-post/' );
	define ( 'OQP_DONATION_URL', 'http://dev.pellicule.org/one-quick-post-plugin/#donate' );
	
	define ( 'OQP_DIRNAME', str_replace( basename( __FILE__ ), "", plugin_basename( __FILE__ ) ) );

	define ( 'OQP_PLUGIN_DIR',  WP_PLUGIN_DIR . '/' . OQP_DIRNAME );
	define ( 'OQP_PLUGIN_URL', WP_PLUGIN_URL . '/' . OQP_DIRNAME );
}


require_once(OQP_PLUGIN_DIR.'/includes/oqp-core.php');


//BUDDYPRESS
function bp_oqp_load() {
		require_once(OQP_PLUGIN_DIR.'/buddypress/one-quick-post-bp-core.php');
		require_once(OQP_PLUGIN_DIR.'/buddypress/includes/notifications.php');
}


//buddypress
add_action('bp_loaded', 'bp_oqp_load' );
//activation hook
register_activation_hook(__FILE__,'oqp_activation');
//deactivation hook
register_deactivation_hook(__FILE__, 'oqp_deactivation');





?>