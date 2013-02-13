<?php

//languages

load_plugin_textdomain( 'oqp', false, OQP_DIRNAME . 'lang' );

require_once( OQP_PLUGIN_DIR . '/includes/classes.php');

require_once( OQP_PLUGIN_DIR . '/includes/ajax.php');
require_once( OQP_PLUGIN_DIR . '/includes/rewrite.php');
require_once( OQP_PLUGIN_DIR . '/includes/oqp-fields.php');

require_once( OQP_PLUGIN_DIR . '/includes/theme.php');
require_once( OQP_PLUGIN_DIR . '/includes/extensions.php');


require_once( OQP_PLUGIN_DIR . '/includes/oqp-upload.php');
require_once( OQP_PLUGIN_DIR . '/includes/notifications.php');
require_once( OQP_PLUGIN_DIR . '/includes/form-template.php');

if (is_admin()){//ADMIN
        require_once(OQP_PLUGIN_DIR.'/admin/admin.php');
}else{//FRONT
	require_once( OQP_PLUGIN_DIR . '/includes/terms-template.php');

}

require_once(ABSPATH . "wp-admin" . '/includes/post.php');
//FOR STATS
require_once(ABSPATH . 'wp-includes/class-snoopy.php');


//THEMING
$use_custom_theme=true;
if ($use_custom_theme){
	require_once( OQP_PLUGIN_DIR . '/theme/functions.php');
}

function oqp_get_forms_page_ids() {
	global $wpdb;
	
	$query = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='oqp_form_settings'";
	$page_ids = $wpdb->get_col($query);
	return $page_ids;
}

//TO FIX :
//problem when using quotes in an input field.  Need to convert the content into html chars.
//missing taxonomies message must appear on page load, not only on submit

function oqp_get_form_options($slug=false,$option=false) {
	$options = get_option('oqp_form_'.$slug);
	
	if ($option) {
		$options[$option];
	}else {
		return $options;
	}
}

//get saved option
function oqp_get_option($option_name=false) {
	$options = get_option('oqp_options');

	if ($option_name) {
            
		return $options[$option_name];
	}else {
		return $options;
	}

}

//get saved option if exist; else get default
function oqp_get_option_or_default($option_name=false){
	//from DB
	$option=oqp_get_option($option_name);
	
	//from defaults
	if(!$option){
		$option=oqp_get_default_settings($option_name);
	}

	return $option;
}

//get default option
function oqp_get_default_settings($option_name=false) {
	$options=array(

	);
	
	$options=apply_filters('oqp_get_default_settings',$options);
	
	
	if ($option_name) {
		return $options[$option_name];
	}else{
		return $options;
	}

	return $options;
}



function oqp_set_default_settings($force=false) {
	$force=true;
	$options_default = oqp_get_default_form_settings();
	$current_options = oqp_get_option();
	
	if (($current_options) && (!$force)) {
		return $current_options;
	}
	
	if ((!$current_options) || ($force)) {
		if (update_option('oqp_options', $options_default )) {
			return $options_default;
		}
	}
}


function oqp_query_vars( $qvars ){
	$qvars[] = 'oqp_action';
	$qvars[] = 'oqp_step';
	return $qvars;
}


function oqp_is_multiste() {

	if ( function_exists( 'is_multisite' ) )
		return is_multisite();

	if ( !function_exists( 'wpmu_signup_blog' ) )
		return false;

	return true;
}

function oqp_is_post_a_draft($post=false) {

	if (!$post) return true;

	$post_status=$post->post_status;

	if (($post_status=='pending') || ($post_status=='publish')) return false;
	
	return true;
}

//checks if the post has been saved with OQP; and load the associated OQP FORM.
function oqp_get_form_from_post($post_id) {

	$the_post=get_post($post_id);
	
	if(!$the_post) return false;
	
	if (!is_oqp_post($post_id)) return false;
	
	$form_slug = oqp_post_get_form_page_id($post_id);
	$form= new Oqp_Form($form_slug);
	return $form;
}

//TO FIX URGENT REMOVE ?
function oqp_build_url_args($url,$args=false){
	global $oqp_form;
	
	//available
	//$args['oqp_action']
	//$args['oqp_step']
	//$args['oqp-post-key']
	//$args['oqp-post-id']
	
	
	//ACTION
	//STEP
	//OQP_KEY
	//
	
	$args=array_filter((array)$args);
	
	//TO FIX

	//TO FIX + ADD REWRITE RULES
	//if (!get_option('permalink_structure')){
		$url=add_query_arg($args,$url);
	//}else{
		//$url.=_x('create','slug','oqp').'/'._x('step','slug','oqp').'/'.$step_slug;
	//}

	return $url;
	
}






function oqp_get_post_type_label($type=false,$post_type=false){

	if(!$type)$type='singular_name';
	$obj=oqp_get_post_type_obj($post_type);
	return $obj->labels->$type;
}

function oqp_get_post_type_obj($post_type=false){
	global $oqp_form;
	
	if(!$post_type) $post_type=$oqp_form->post_type;

	
	return get_post_type_object($post_type);
}

function oqp_post_is_matching($page_id,$post_id=false){
    global $post,$wp_query;
    if (!$post_id)$post_id=$post->ID;
    
    
    $cache_key='oqp_post_'.$post_id.'_is_matching_form_'.$page_id;
    $is_matching=wp_cache_get($cache_key,'oqp');
    
//    if (!$is_matching) {

        $post_query = oqp_get_form_query($page_id);
       
        $post_query['numberposts']=1;
        $post_query['p']=$post_id;
        $post_query['post_status']=oqp_get_allowed_single_post_stati();

        
        oqp_debug(array('page_id'=>$page_id,'post_id'=>$post_query['p']),"CHECK POST IS MATCHING QUERY","error");

        $posts = get_posts($post_query);

        if ($posts[0]->ID){
            $is_matching='yes';
        }else{
            $is_matching='no';
        }
        
        oqp_debug($is_matching,"matching");
        
        //oqp_debug($is_matching,"oqp_post_is_matching","error");


        wp_cache_set($cache_key,$is_matching,'oqp');

//    }
    return ($is_matching=='yes');
    
}

function oqp_post_get_form_page_id($post_id=false){
        global $post;
	if (!$post_id)$post_id=$post->ID;

        $forms_page_ids=oqp_get_forms_page_ids();

        foreach($forms_page_ids as $page_id){
            $oqp_settings = Oqp_Form::get_options($page_id);
            if(oqp_post_is_matching($page_id,$post_id)) return $page_id;
        }

}

function is_oqp_post($post_id=false) {
        global $post;
	if (!$post_id)$post_id=$post->ID;

        $is_oqp_post = oqp_post_get_form_page_id($post_id);
	return(bool)$is_oqp_post;
}


function oqp_posts_count_for_user($user_id=false,$post_status='publish',$post_type='post') {
	echo oqp_get_posts_count_for_user($user_id,$post_status,$post_type);
}
function oqp_get_posts_count_for_user($user_id=false,$post_status='publish',$post_type='post') {
	global $wpdb;
	if (!$user_id) {
		global $oqp_form;
		$user_id=get_current_user_id();
	}

	if (oqp_user_is_dummy($user_id)) {
		if (!$oqp_form->guest['email']) return (int)false;
	}

	$query = $wpdb->prepare( "SELECT count(*) FROM $wpdb->posts WHERE post_author = %d AND post_status = '%s' AND post_type = '%s'", $user_id,$post_status,$post_type);
	$count = $wpdb->get_var($query);
	return $count;
}

function oqp_stats_update_posts_count() {
	//remove donated every 100 posts
	$nposts = get_option('oqp_posts_stats');
	update_option( 'oqp_posts_stats', $nposts+1 );
	$is_hundred=$nposts/100;
	
	if (is_int($is_hundred)){
		$oqp_options = oqp_get_option();
		unset($oqp_options['donated']);
		update_option( 'oqp_options', $oqp_options );
	}
}

//style,css,stuff...



function is_oqp($page_id=false){
    global $oqp_form;
    
    if($page_id){
        if ($oqp_form->page_id==$page_id)return true;
    }else{
        return ($oqp_form);
    }
    
}

function oqp_is_directory(){
    global $oqp_form;
    if((is_oqp())&&($oqp_form->type=='directory')) $is_dir=true; 
    return apply_filters('is_oqp_directory',$is_dir);

}
function is_oqp_form(){
    global $oqp_form;
    if((is_oqp())&&($oqp_form->type=="form")) $is_form=true; 
    return apply_filters('is_oqp_form',$is_form);
}

function oqp_is_single(){
    global $oqp_form;
    if((is_oqp())&&($oqp_form->type=="single")) $is_single=true; 
    return apply_filters('is_oqp_single',$is_single);
}

function oqp_debug_enabled(){
    return oqp_get_option("debug");
}

function oqp_debug($code,$label=false,$type='info'){
    //FB::log('Log message');
    //FB::info('Info message');
    //FB::warn('Warn message');
    //FB::error('Error message');
    
    if(!oqp_debug_enabled())return false;
    //if (!current_user_can('manage_options')) return false;
    
    if (!class_exists('FirePHP'))return false;

    ob_start();

    FB::$type($code,$label);

    ob_end_flush();

}
function oqp_debug_main_query(){
    global $wp_query;
    oqp_debug($wp_query->request,"main query");
}

function oqp_debug_redirect($redirect){
    if(is_admin())return $redirect;
    if(!is_oqp()) return $redirect;
    if (!oqp_debug_enabled())return $redirect;
    ?>
    <script type="text/javascript">
        <!--

        alert("redirect to <?php echo $redirect;?> ?")
        window.location = "<?php echo $redirect;?>";
        //-->
    </script>
    <?php
    exit;
}
add_filter('wp_redirect','oqp_debug_redirect',10);

function oqp_directory_query(){
    global $wp_query;
    global $oqp_form;
    
    if(!oqp_is_directory()) return false;
    
    
    $form_args['post_type']=$oqp_form->post_type;
    $form_args['context']='oqp_directory';
    
    foreach((array)$oqp_form->query_args as $name=>$arg){
        $form_args[$name]=$arg;
    }
    
    $args = array_merge( $wp_query->query, $form_args );
    
    //SELF POSTS //TO FIX SHOULD BE IN A FILTER ?
    if(($args['author'])&&($args['author']==get_current_user_id()))$args['post_status']='any';
    
    unset($args['page_id']);
    unset($args['pagename']);
    
    oqp_debug($args,'oqp_directory_query');
    
    query_posts( $args );
}

function oqp_get_form_query($page_id=false){
    global $oqp_form;
    if(!$page_id)$page_id=$oqp_form->page_id;
    
    $oqp_settings = Oqp_Form::get_options($page_id);

    $form_query['post_type']=$oqp_settings['post_type'];
    foreach((array)$oqp_settings['query_args'] as $name=>$value){
        $form_query[$name]=$value;
    }

    $form_query = array_filter($form_query);
    
    return $form_query;
}


function oqp_query_is_matching($page_id,$query=false){

    $form_query = oqp_get_form_query($page_id);

    //COMPARE FORM QUERIES AGAINST QUERY
    //foreach form queries

    $is_matching=true;
    foreach($form_query as $f_var=>$f_value){
        if($query->query_vars[$f_var]!=$f_value){
            $is_matching=false;
            break;
        }
    }

    $is_matching = apply_filters("oqp_query_is_matching",$is_matching,$page_id,$query);
    
    if($is_matching){
        oqp_debug(array('wp_args'=>$form_query,'wp_args'=>$query->query_vars),"WP query is matching form settings#".$page_id);
    }

    return $is_matching;

}

function oqp_redirect_to_archives_page($query){
   
    if(is_admin())return $query;
    if (is_single()) return $query;
    if(!$query->is_main_query()) return $query;

    $forms_page_ids=oqp_get_forms_page_ids();
    
     foreach($forms_page_ids as $page_id){
        $oqp_settings = Oqp_Form::get_options($page_id);
        if(!$oqp_settings['templates']['archives'])continue; //do not load archives template
        if(!oqp_query_is_matching($page_id,$query)) return $query;

        $url_args = $query->query;
        unset($url_args['post_type']);

        oqp_debug(array('page_id'=>$page_id,'new_query'=>$query),"oqp_load_templates_archives","warn");
        $redirect_url = get_permalink($page_id);
        $redirect_url = add_query_arg($url_args,$redirect_url);

        wp_redirect($redirect_url);die;
     }
     
     return $query;
     
}

function oqp_get_allowed_single_post_stati(){
    $stati = array('publish','pending','draft');
    return apply_filters('oqp_get_allowed_single_post_stati',$stati);
}

//after creating a new post;
//the post status is "auto-draft".
//we need to be able to load that post as WP will not show it; only if we are the post author.

function oqp_allow_post_stati($query){
    
    if (!$query->get('oqp_post_stati'))return $query;

    /*
    //GET USER
    $user_id = get_current_user_id();
    if(!$user_id)return $query;
    
    
    
    //CHECK WE ARE TRYING TO LOAD A POST
    if(!is_singular()) return $query;
    if(is_page()) return $query;

    */
    
    //GET POST
    $p = get_query_var('p');
    $post = get_post($p);
    

    //ENABLE ANY POST STATUS
    $query->set('post_status',oqp_get_allowed_single_post_stati());
    
    oqp_debug($query,"oqp_allow_post_stati");

    add_filter('the_posts','oqp_always_allow_self_post_reload');
    
    //oqp_debug($post,"oqp_always_allow_self_post","warn");

    return $query;
    
}

function oqp_always_allow_self_post_reload($posts){

    global $wp_query, $wpdb;


    /*
    //if(!oqp_is_single()) return $query;
    if(!oqp_is_single()) return $posts;

        */
    if($posts) return $posts;
    
    if(!$wp_query->request)return $posts;
    
    oqp_debug($wp_query->request,"STATI QUERY");

    $posts = $wpdb->get_results($wp_query->request);

    oqp_debug($posts,"author_reload_hidden_single_post","warn");
    
    remove_filter('the_posts','oqp_always_allow_self_post_reload',1);

    return $posts;
}


function oqp_always_allow_self_post($query){
    global $wp_query;
    
    if(is_admin())return $query;
    if(!$query->is_main_query()) return $query;

    //CHECK WE ARE TRYING TO ADMIN A NEW POST
    $action=$query->get('oqp_action');   
    if($action!=_x('create','slug','oqp')) return $query;
    
    oqp_debug("oqp_always_allow_self_post FOR MAIN QUERY");
    
    $query->set('oqp_post_stati',true);

    return $query;

}




     
function oqp_detect_page_type($query){
    global $oqp_form;
    global $wp_query;

    if(is_admin())return $query;
    if(!$query->is_main_query()) return $query;
    


    $action=$query->get('oqp_action');   
    $valid_actions=array(_x('create','slug','oqp'),_x('admin','slug','oqp'),_x('create','slug','oqp'),_x('delete','slug','oqp'),_x('republish','slug','oqp'));

    //$p = oqp_pre_get_posts_get_singular_id();

    //if(!$p)return $query;
    
    //GET POST ID
    $p = $wp_query->get_queried_object_id();
    

    if(!$p){
        $p = get_query_var('p');
    }
    
    if(!$p){
            $post_type = get_query_var('post_type');
            $post_name = get_query_var('name');
            $post = get_page_by_path($post_name,false,$post_type);
            $p = $post->ID;
    }
    
    if(!$p) return $query;

    //CHECK WE HAVE TO LOAD OQP
    
    if (is_page()){

        $page_id = $p;
        if(!Oqp_Form::get_options($page_id)) return $query;
        oqp_debug($page_id,"IS OQP PAGE","warn");
        
    }elseif(is_singular()){
        
        $post_id = $p;
        if(!is_oqp_post($post_id)) return $query;
        oqp_debug($post_id,"IS OQP POST","warn");
        
    }
    
    //ALLOW POST STATUS FOR AUTHOR
    
    

    if ($page_id){
            if(in_array($action,$valid_actions)){
                ////CREATION FORM - EMPTY POST
                oqp_debug($page_id,"IS CREATION PAGE - NEW POST","warn");
                $oqp_form = new Oqp_Creation_Page(false,$page_id);

            }else{
                ////DIRECTORY
                oqp_debug($page_id,"IS OQP DIRECTORY","warn");
                $oqp_form = new Oqp_Form($page_id);
                $oqp_form->type='directory';
 
            }
    }elseif($post_id){

        if(in_array($action,$valid_actions)){////CREATION FORM

            oqp_debug($p,"IS OQP CREATION PAGE","warn");

            $oqp_form = new Oqp_Creation_Page($p);

        }else{
            
            $form_id = oqp_post_get_form_page_id($p);
           
            //check if we should autoload the single template
            $form_settings = Oqp_Form::get_options($form_id);
            
             oqp_debug($form_settings,"IS OQP FORM#".$form_id,"warn");

            if($form_settings['templates']['singular']){
                $load_single=true;
            }else{
                if(isset($_REQUEST['oqp']))$load_single=true;
            }    
            
            if($load_single){
                oqp_debug($p,"IS OQP SINGLE","warn");
                $oqp_form = new Oqp_Single_Post($p);
                
            }
        }

    }

    if(!$oqp_form->page_id){
        unset($oqp_form);
        return $query;
    }
    
    //populate steps, extensions, ...
    do_action('oqp_init');
    
    if(!$oqp_form->steps){
        unset($oqp_form);
        return $query;
    }

    do_action('oqp_has_init');

    $query=apply_filters('oqp_pre_get_posts',$query);

    return $query;
    
    
}  

function oqp_get_custom_pos_cap_name($generic_cap,$post_type=false){
    global $oqp_form;
	
    //POST TYPE
    if (!$post_type) $post_type=$oqp_form->post_type;
    
    //TO FIX URGENT
    //BROKE WHEN GUEST DOES INIT
    $post_type='yclad';

    $ptype=get_post_type_object($post_type);

    return $ptype->cap->$generic_cap;
}

function oqp_get_regular_pos_cap_name($search_cap,$post_type=false){
    global $oqp_form;
	
    //POST TYPE
    if (!$post_type) $post_type=$oqp_form->post_type;
    
    //TO FIX URGENT
    //BROKE WHEN GUEST DOES INIT
    $post_type='yclad';

    $ptype=get_post_type_object($post_type);
    
    $caps=$ptype->cap;
    
    foreach($caps as $regular_cap=>$custom_cap){
        if($custom_cap==$search_cap) return $regular_cap;
    }

}

function oqp_user_can_for_ptype($cap,$post_type=false,$user_id=false){
	global $current_user,$oqp_form;
        
        $cap_name = oqp_get_custom_pos_cap_name($cap,$post_type);
        $can = oqp_user_can($cap_name,$user_id);

	return $can;
}
function oqp_user_can($cap,$user_id=false){
        if(!$user_id) $user_id=get_current_user_id();
        $has_cap=current_user_can($cap);

        
        oqp_debug(array('result'=>$has_cap,'cap'=>$cap,'user_id'=>$user_id),oqp_user_can);
	return (bool)$has_cap;
}



function oqp_init(){

        if(!is_admin()){
            
            //add oqp_action & oqp_step vars
            add_filter('query_vars', 'oqp_query_vars' );
            

            //detect OQP
            add_action('pre_get_posts','oqp_redirect_to_archives_page',7);
            
            //always allow an author to view his single post (even if auto-draft, etc.)
            //add_action('pre_get_posts','oqp_always_allow_self_post',6);
            //add_action('pre_get_posts','oqp_allow_post_stati',7);
            
            add_action('pre_get_posts','oqp_detect_page_type',8);
            
            //update_main_query
            add_action('wp','oqp_directory_query');

            add_action('oqp_before_content','oqp_debug_main_query');

            add_filter('get_delete_post_link','oqp_get_delete_post_link', 10, 3 );
            
        }

	//update posts stats count (donation button)
	add_action('oqp_insert_new_post','oqp_stats_update_posts_count');

	//NOTIFICATIONS
	add_action('transition_post_status','oqp_notify_check',10,3);

        //OQP POST DISPLAY
        //load the single post form
        add_action('oqp_init_forms','oqp_single_post_init_form');
        

	if(!is_admin()){

		add_action('oqp_after_item_body','wp_reset_query');
		

		//sidebars
		if (!defined('BP_VERSION')) {
			//add_action('oqp_after_container','oqp_wp_only_sidebar');
		}else{
			add_action('oqp_after_content','oqp_bp_only_sidebar');
		}

		//replace admin link with frontend link if any
		add_filter('get_edit_post_link','oqp_get_edit_post_link', 10, 3 );
		//replace delete link with frontend link if any

	}
	
}


function oqp_get_form_input_name(){
    return "oqp_form";
}

function oqp_get_form_step_input_name($step_id=false,$base=false){
    global $oqp_form;
    if(!$step_id)$step_id=$oqp_form->current_step;
    $base=oqp_get_form_input_name();
    return $base.'[steps]['.$step_id.']';
}

function oqp_get_form_field_input_name($field_id=false,$step_id=false){
    global $oqp_form;
    if(!$step_id)$step_id=$oqp_form->current_step;
    if(!$field_id)$field_id=$oqp_form->steps[$step_id]->current_field;
    
    $base=oqp_get_form_step_input_name();
    
    return $base.'[fields]['.$field_id.']';
}

function get_requested_step_key(){
    global $oqp_form;
    return (int)$oqp_form->get_step_key($oqp_form->requested_step);
}

function get_requested_step(){
    global $oqp_form;
    $requested_step_key = get_requested_step_key();
    return $oqp_form->steps[$requested_step_key];
}

function setup_requested_step(){
    global $oqp_form;

    $oqp_form->current_step = get_requested_step_key();
    $oqp_form->step = get_requested_step();

    
}
function oqp_get_next_key($array, $current_key) {

    if(!$array) {

        return false;
    }
    

    $array_keys=array_keys($array);

    if($current_key==-1){
        $new_key=$array_keys[0];
    }else{

        $position = array_search($current_key,$array_keys);
        
        if($array_keys[$position+1])
            $new_key=$array_keys[$position+1];

    }

    if (isset($new_key)) return (int)$new_key;
    return false;
}

function oqp_comment_nonce($action,$id=false){
    global $comment;
    if(!$id)$id=$comment->comment_ID;
    if(!$id)return false;
    
    return wp_create_nonce($action.'-'.$id);
}

function oqp_comments_actions(){
    global $oqp_form;

    $id = $_REQUEST['c_id'];
    $action = $_REQUEST['c_action'];
    $nonce = $_REQUEST['c_nonce'];
    
    if((!$id)||(!$action)||(!$nonce))return false;


    //CHECK NONCE
    if (!wp_verify_nonce($nonce,$action.'-'.$id)){
        $oqp_form->notices->error('oqp_step','attachment_nonce');
        return false;
    }

    switch ($action) {
        case 'delete':
        die("delete_com");
        break;
        case 'flag':
        die("flag_com");
        break;
    }
    
    do_action('oqp_comments_do_actions',$action,$id);

}

//comment actions TO FIX TO MOVE
add_action('wp','oqp_comments_actions');


add_action('init','oqp_init',5);

?>