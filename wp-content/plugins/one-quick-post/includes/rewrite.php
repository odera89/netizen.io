<?php


// Remember to flush_rules() when adding rules
function oqp_rewrite_flush(){
	global $wp_rewrite;
   	$wp_rewrite->flush_rules();
}
//TODO TO FIX : hook on plugin activation, not init
//add_action('init','oqp_rewrite_flush');
add_action('oqp_activation','oqp_rewrite_flush');

//see http://gskinner.com/RegExr/
// http://www.ballyhooblog.com/custom-post-types-wordpress-30-with-template-archives/


function oqp_rewrite_rules($wp_rewrite) {
	$newrules = array();

	$wp_rewrite->rules = $newrules + $wp_rewrite->rules;

}
add_action('generate_rewrite_rules', 'oqp_rewrite_rules');



?>