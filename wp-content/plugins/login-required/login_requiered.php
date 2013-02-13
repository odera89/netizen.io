<?php
/*
Plugin Name: Login requiered WordPress Plugin
Plugin URI: http://wordpress.org/extend/plugins/login-required/
Based on : http://blog.taragana.com/index.php/archive/angsumans-authenticated-wordpress-plugin-password-protection-for-your-wordpress-blog/
Description: This plugin allows you to make your WordPress site accessible to logged in users only. In other words to view your site they have to create / have an account in your site and be logged in. No configuration necessary. Simply activating the plugin is all that is required from you.

Author: Stefan Nicolae
Version: 0.1
Author URI: http://shakabut.com/
License: Free to use non-commercially.
Warranties: None.
*/
//print_r($user);
function ac_auth_redirect() {
	// Checks if a user is logged in, if not redirects them to the login page
	//echo session_id();
	//print_r($_COOKIE);
	//echo COOKIEHASH."|".PASS_COOKIE."|".AUTH_COOKIE;
	
	if (!is_user_logged_in())
	{
		nocache_headers();
		header("HTTP/1.1 302 Moved Temporarily");
		header('Location: ' . get_settings('siteurl') . '/wp-login.php?redirect_to=' . urlencode($_SERVER['REQUEST_URI']));
		header("Status: 302 Moved Temporarily");
		exit();
	}
	else {
		//echo "you are registered!";
	};
}

if('wp-login.php' != $pagenow && 'wp-register.php' != $pagenow) add_action('template_redirect', 'ac_auth_redirect');
?>