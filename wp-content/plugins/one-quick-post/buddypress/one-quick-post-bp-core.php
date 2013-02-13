<?php
/**
 * bp_example_setup_globals()
 *
 * Sets up global variables for your component.
 */
 


//replace wp template by bp template if exists.
function oqp_bp_locate_default_theme_template($located,$template_name){
    $default_bp_theme_path = OQP_PLUGIN_DIR.'buddypress/theme/';

    
    $file_path = $default_bp_theme_path . $template_name;

    if ( file_exists( $file_path ) ) return $file_path;

    return $located;
      
}
  


add_filter('oqp_locate_default_theme_template','oqp_bp_locate_default_theme_template',10,2);
//TO FIX URGENT !
return false;

 



function oqp_bp_admin_form_field_form_enabled(){
	global $oqp_admin_form;
	add_settings_section('oqp_form_bp', 'BuddyPress', 'oqp_bp_admin_section_bp', 'oqp_forms');
	add_settings_field('bp_disabled', __('Disable this form','oqp'), 'oqp_bp_admin_section_bp_field_disable', 'oqp_forms', 'oqp_form_bp');
}
function oqp_bp_admin_section_bp(){}

function oqp_bp_admin_section_bp_field_disable() {
	global $oqp_admin_form;
	if ($oqp_admin_form->form->bp_disabled) $checked=" CHECKED";

	
	echo "<input id='bp_disabled' name='oqp_forms[".$oqp_admin_form->form->slug."][bp_disabled]' type='checkbox' value='1'".$checked."/>";
	
	$message = __('Do not show this form in the Quick Post menu.','oqp');
	oqp_form_balloon_info($message);

}

/**
 * oqp_bp_setup_nav()
 *
 * Sets up the user profile navigation items for the component. This adds the top level nav
 * item and all the sub level nav items to the navigation array. This is then
 * rendered in the template.
 **/

function oqp_bp_setup_nav() {
	global $oqp_form;
	global $bp;

	//$oqp_link = oqp_bp_get_oqp_url();

	// Add 'OQP' to the main navigation
	bp_core_new_nav_item(
		array(
			'name' => __('Quick Post', 'oqp'),
			'slug' => $bp->oqp->slug,
			'position' => 50,
			'show_for_displayed_user' => false,
			'screen_function' => 'oqp_bp_main_screen',
			'default_subnav_slug' => _x('create','slug','oqp'),
			'item_css_id' => $bp->oqp->id
		)
	);



	
	//bp_core_new_subnav_item( array( 'name' => __( 'Add new', 'oqp' ), 'slug' => _x('create','slug','oqp'), 'parent_url' => $oqp_link, 'parent_slug' => $bp->oqp->slug, 'screen_function' => 'oqp_bp_main_screen', 'position' => 10, 'user_has_access' => bp_is_my_profile() ) );
	//bp_core_new_subnav_item( array( 'name' => __( 'Admin', 'oqp' ), 'slug' => _x( 'admin','slug','oqp' ), 'parent_url' => $oqp_link, 'parent_slug' => $bp->oqp->slug, 'screen_function' => 'oqp_bp_main_screen', 'position' => 10, 'user_has_access' => $oqp_form_loaded ) );
	//bp_core_new_subnav_item( array( 'name' => __( 'Delete', 'oqp' ), 'slug' => _x( 'delete','slug','oqp' ), 'parent_url' => $oqp_link, 'parent_slug' => $bp->oqp->slug, 'screen_function' => 'oqp_bp_main_screen', 'position' => 20, 'user_has_access' => $oqp_form_loaded ) );
	//bp_core_new_subnav_item( array( 'name' => __( 'Edit published', 'oqp' ), 'slug' => _x( 'published-posts','slug','oqp' ), 'parent_url' => $oqp_link, 'parent_slug' => $bp->oqp->slug, 'screen_function' => 'oqp_bp_main_screen', 'position' => 30, 'user_has_access' => bp_is_my_profile() ) );
	


	
	do_action( 'oqp_bp_setup_nav' );

}



function oqp_bp_load(){
	add_action('oqp_admin_options_form','oqp_bp_admin_form_field_form_enabled');
	add_action( 'bp_setup_globals', 'oqp_bp_setup_globals' );
	add_action( 'bp_setup_root_components', 'oqp_bp_setup_root_component' );
	add_action( 'bp_setup_nav', 'oqp_bp_setup_nav' );
}
oqp_bp_load();


function oqp_bp_init() {
	global $bp;
	global $wp_query;


	if ( $bp->current_component != OQP_SLUG ) return false;
	
	global $oqp;

	//Unset posts so they are not parsed for shortcode
	global $posts;
	$posts=array();

	$action = $wp_query->get('oqp_action');
	$step = $wp_query->get('oqp_step');
	
	if ($action)
		$oqp->action=$action;
		
	if (!$oqp->action) return false;
	
	if ($step)
		$oqp->step=$step;
	
	$bp->is_directory=true; //else bp_is_blog_page returns true and our template will not be filtered correctly

	if ($oqp->action) {
		oqp_populate_form('default-form');
		global $oqp_form;
	;
	}else {
		global $wp_query;
		
		global $query_string;
		$query_args['post_type']='post';
		$query_args['meta_key']='oqp_page_id';
		//TO FIX CHECK IF USER IS CORRECT HERE
		$query_args['author']=$oqp_form->user_id;
		
		$query_args['post_status']='publish';
		if ($bp->action_variables[0]) {
			$query_args['post_status']=$bp->action_variables[0];
		}

		//if author is guest; query will be filtered with oqp_guest_pre_get_posts
		if (is_user_logged_in()) {
			$query_args_str = http_build_query($query_args);
			$total_query = $query_args_str;
			query_posts($total_query);
		}else { //deny access to posts list for non logged users
			$redirect_url = oqp_form_page_get_link();
			wp_redirect( $redirect_url );die();
		}
	}

}
add_action('wp','oqp_bp_init',3);


function oqp_bp_oqp_init_get_action($action) {
	global $bp;
	if ( $bp->current_component != OQP_SLUG ) return $action;
	return $bp->current_action;
}
add_filter('oqp_init_get_action','oqp_bp_oqp_init_get_action');

function oqp_bp_oqp_init_get_step($step) {
	global $bp;
	if ( $bp->current_component != OQP_SLUG ) return $step;
	if ($bp->action_variables[0]==_x('step','slug','oqp'))
		return $bp->action_variables[1];
}
add_filter('oqp_init_get_step','oqp_bp_oqp_init_get_step');

function oqp_bp_main_screen() {
	global $bp;

	if ( $bp->current_component != OQP_SLUG ) return false;
	global $oqp;
	global $oqp_form;

	if (!$oqp->action) { //LIST POSTS

	}
	
	do_action('oqp_bp_main_screen');
//	add_action( 'bp_template_title', 'oqp_bp_screen_title' );
	//add_action( 'bp_template_content', 'oqp_bp_oqp_block' );
	
	global $wp_query;

	bp_core_load_template('oqp/bp-home');

}
add_action( 'wp', 'oqp_bp_main_screen',3);

function oqp_bp_oqp_block() {
	die("oqp_bp_oqp_block");
	echo Oqp_Post::oqp_block();
}




function oqp_bp_screen_title() {
	//TO FIX
}



?>