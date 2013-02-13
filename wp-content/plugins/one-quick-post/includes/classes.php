<?php


class Oqp_Form {
        var $type;
	var $page_id;
	var $post_type;
        var $query_args;
        
	var $guest_posting;
	var $email_notifications_enabled;
        
	var $current_step = -1; 
	var $step;

        
	var $steps=array(); //all steps
	var $notices; 
        
        var $extensions;
        var $extensions_slugs;

        var $templates;
        
        private $steps_options;
        
	function __construct($page_id=false) {
                
                $this->notices = new Oqp_Notices();

		$page_id=intval($page_id);
 
                if ($page_id){
			$options = self::get_options($page_id);
                        if(!$options) unset($page_id);
                }

		$this->page_id = $page_id;
                
                if(!$this->page_id) return false;

                if(!$options['post_type']) return false;

                //TO CHECK TO FIX (see duplicates in load_extensions)
		$default_options = self::get_default_form_settings($options['post_type']);
		$options = wp_parse_args( $options, $default_options );

                
		extract($options);

		$this->name=$name;
		$this->post_type=$post_type;
                $this->query_args=$query_args;    
                
		$this->guest_posting=$guest_posting;
		$this->email_notifications_enabled=$email_notifications_enabled;
		$this->post_type=$post_type;
                
                $this->templates=$templates;
                
                $this->steps_options=$steps;

                //EXTENSIONS
                $this->extensions_slugs=$extensions; //extention slugs
                add_action('oqp_init',array(&$this,'populate_extensions'));
                add_action('oqp_init',array(&$this,'populate_steps'));

	}

        function populate_extensions(){

                $this->extensions=oqp_populate_extensions($this->extensions_slugs);
        }

	function get_default_form_settings($post_type) {

		//
		$options['name']=__('Default Form','oqp');

		$options['email_notifications_enabled']=true;
		$options['post_type']='post';
                $options['templates']=array('archives'=>false,'singular'=>true);
		//
		//
		//
		
		$options['steps'][0]['name']=__('Details','oqp');

		$options['steps'][0]['fields'][0]['model']='title';
		$options['steps'][0]['fields'][0]['required']=true;
                
		$options['steps'][0]['fields'][1]['model']='section';
		$options['steps'][0]['fields'][1]['required']=true;
                $options['steps'][0]['fields'][1]['wysiwyg']=true;
                
		$options['steps'][0]['fields'][2]['model']='excerpt';
                


		$options['steps'][1]['name']=__('Settings','oqp');

		$options['steps'][1]['fields'][0]['model']='taxonomy';
		$options['steps'][1]['fields'][0]['required']=true;
		$options['steps'][1]['fields'][0]['taxonomy']='post_tag';
		
		$options['steps'][1]['fields'][1]['model']='taxonomy';
		$options['steps'][1]['fields'][1]['required']=true;
		$options['steps'][1]['fields'][1]['taxonomy']='category';
		$options['steps'][1]['fields'][1]['taxonomy_args']='exclude=1';
                
                

		$options['steps'][2]['name']=__('Pictures','oqp');
		$options['steps'][2]['fields'][0]['model']='upload';
		$options['steps'][2]['fields'][0]['value']='5';
		$options['steps'][2]['fields'][0]['type']='image';
                
                
		
		$options['steps'][3]['name']=__('Location','oqp');
		$options['steps'][3]['fields'][0]['model']='location';

		$options = apply_filters('oqp_get_default_form_settings',$options,$post_type);
                
                $extensions = oqp_get_avalaible_extensions();
                
                foreach($extensions as $slug=>$ext){
                   $options['extensions'][]=$slug;
                }

                

                return $options;
	}
	
	
	function get_options($page_id=false,$option=false) {

            
		if(!$page_id) $page_id=$this->page_id;
		
		$options = get_post_meta($page_id,"oqp_form_settings",true);

		if ($option) {
			return $options[$option];
		}else {
			return $options;
		}
	}
	
	function save_options($form_options){

		if(!$this->page_id) return false;

                //QUERY
                $q_args=$form_options['query_args'];
                if($q_args){
                    if(!is_array($q_args)){//STRING
                        $q_args_str=trim($q_args);
                        parse_str($q_args_str,$q_args);
                    }
                    $form_options['query_args']=$q_args;
                    unset( $form_options['query_args']['post_type']);
                }

                //STEPS
                foreach ((array)$form_options['steps'] as $step_key=>$step_options) {

                        $step_options = Oqp_Form_Step::step_prepare_for_save($step_options);

                        if (!$step_options['fields']) continue;
                        $step_options = array_filter($step_options);

                        $new_options['steps'][$step_key]=$step_options;

                }
                
                if($new_options['steps'])$form_options['steps']=$new_options['steps'];

		return update_post_meta($this->page_id,"oqp_form_settings",$form_options);
	}
	
	function delete_options(){
		if(!$page_id) $page_id=$this->page_id;
		return delete_post_meta($page_id,"oqp_form_settings");
	}

        function get_step_slug($step_key=false) {
 
		if (!is_int($step_key))
			$step_key = $this->current_step;
                
                
                
		$step = $this->steps[$step_key];
	
		return $step->slug;
	}
	
	
	function populate_steps() {

		$step_key=0;	
		
		foreach ((array)$this->steps_options as $step_key=>$step_args) {
		
			$new_step = new Oqp_Form_Step($step_args);

			if (!$new_step->fields) continue;
  

			if (($new_step->fields) || (!$hide_empty)){
				$new_steps[$step_key] = $new_step;
				$step_key++;
			}
		}

		$this->steps = $new_steps;

                unset($this->steps_options);

                do_action('oqp_populated_steps');
 
                
	}
	
	function is_first_step() {
		$keys = array_keys($this->steps);
		if ($this->current_step==$keys[0]) return true;
	}
	
	function is_last_step() {
		$keys = array_keys($this->steps);
		$rkeys = array_reverse($keys);
		if ($this->current_step==$rkeys[0]) return true;
	}
	
	function next_step() {
                if(!$this->steps) return false;
                
                $next_key = oqp_get_next_key($this->steps,$this->current_step);
                if(!is_int($next_key))return false;
                
                $this->current_step=(int)$next_key;

                $this->step = $this->steps[$this->current_step];

	}
	
	function the_step($step_id=false) {
            if((is_int($step_id))&&($this->steps[$this->current_step])){
                $this->current_step = (int)$step_id;
                $this->step=$this->steps[$this->current_step];
                
            }else{
		$this->next_step();
            }
            return $this->step;
	} 
	
	function rewind_steps() {
                $steps_count = count($this->steps);
		$this->current_step = -1;
		/*if ( $steps_count > 0 ) {
                        reset($this->steps);
                        $this->current_step = key($this->steps);
			$this->step = current($this->steps);
		}*/
	}
        
	function have_steps() {
                $steps_count = count($this->steps);
                
                if(!$steps_count) return false;
                
                $next_key = oqp_get_next_key($this->steps,$this->current_step);
                
                if(!is_int($next_key)) {
                    $this->rewind_steps();
                    return false;
                }else{
                    return true;
                }

	}
        
	function get_step_key($step_slug=false) {
            
                if(!$step_slug)$step_slug = $this->requested_step;

		foreach ($this->steps as $step_key=>$step) {

			if ($step_slug==$step->slug) {
                                //oqp_debug($step,"get_step_key");
				return (int)$step_key;
			}
		}
		return (bool)false;
	}
        function get_permalink(){
            $link = get_permalink($this->page_id);
            return apply_filters('oqp_form_page_get_link',$link,$this->page_id);
        }
        
}


class Oqp_Form_Step {
	var $slug;
	var $name;

	var $current_field=-1;
	var $field; //current field
	var $fields; //array

	var $options;
	var $required;

	
	var $template;
	var $url;
	var $classes;

	
	function __construct($options=false) {

		$this->options = $options;

		extract($this->options);
		
		$this->name=$name;
		$this->slug=$slug;

		$this->template=$template;
		$this->url=$url;
		$this->classes=$classes;


		$this->delete=$delete;

                if($fields) self::populate_fields($fields);
		//TO FIX
		//if (!$this->fields) return false;

		return $this;


	}

	function get_step_name(){
                global $oqp_form;
                
                $name = strip_tags($this->name);
		return apply_filters('step_name',$name);
	}
        
        function is_required(){
            return $this->required;
        }
	



	function populate_fields($fields=false){
		global $oqp_form;

        	foreach ((array)$fields as $field_key=>$field_args) {
                    
                        $classname = oqp_field_get_classname($field_args['model']);
   
			if(!$classname)continue;

			$field = new $classname();

			$field->populate_field($field_key,$field_args);
                        
                        if($oqp_form->type=='form')$field->edit=true;

			if (!$field->is_enabled()) continue;

			if ($field->slug) {
				if ($field->required) {
					$step_required=true;
				}
				$stepfields[]=$field;
				

			}


		}
  

		if ($stepfields){
			$this->fields=array_values($stepfields);

		}


		if ($step_required) {
			$this->required=true;
		}


	}

	
	function step_check_capability(){
		foreach ($this->fields as $fieldkey=>$field){
			$field_enabled = $field->field_check_capability();
			if (!$field_enabled) unset ($this->fields[$fieldkey]);
		}
		
		if(!$this->have_fields())return false;
		
		return true;
	}
	
	function is_first_field() {
		$keys = array_keys($this->fields);
		if ($this->current_field==$keys[0]) return true;
	}
	
	function is_last_field() {
		$keys = array_keys($this->fields);
		$rkeys = array_reverse($keys);
		if ($this->current_field==$rkeys[0]) return true;
	}
	
	function next_field() {
                if(!$this->fields) return false;
                
                $next_key = oqp_get_next_key($this->fields,$this->current_field);
                if(!is_int($next_key))return false;
                
                $this->current_field=(int)$next_key;

                $this->field = $this->fields[$this->current_field];

	}
	
	function the_field($field_id=false) {
            if((is_int($field_id))&&($this->fields[$this->current_field])){
                $this->current_field = (int)$field_id;
                $this->field=$this->fields[$this->current_field];
                
            }else{
		$this->next_field();
            }
            return $this->field;
	} 
	
	function rewind_fields() {
                $fields_count = count($this->fields);
		$this->current_field = -1;
		/*if ( $fields_count > 0 ) {
                        reset($this->fields);
                        $this->current_field = key($this->fields);
			$this->field = current($this->fields);
		}*/
	}
        
	function have_fields() {

                $fields_count = count($this->fields);
                
                if(!$fields_count) return false;
                
                $next_key = oqp_get_next_key($this->fields,$this->current_field);
                
                if(!is_int($next_key)) {
                    $this->rewind_fields();
                    return false;
                }else{
                    return true;
                }

	
	}
	


	
	function get_step_classes() {
		$classes[]='oqp-step';
		$classes[]=$this->slug;
		return $classes;
	}

        
	function step_prepare_for_save($step_options) {
		global $oqp_form;
		global $oqp_form_existing_step_slugs;
                


		//DELETE STEP
		if ($step_options['delete'])return false;
		if (!$step_options['fields'])return false;
                


		$new_options['name']=trim(strip_tags($step_options['name']));
		
		//SLUG
                
                if($step_options['slug']){
                    $do_slug=$step_options['slug'];
                }else{
                    $do_slug=$step_options['name'];
                }
                
                $new_options['slug']=oqp_step_slug_unique($do_slug);
                



		//FIELDS
		$fields = $step_options['fields'];
                
                

		foreach ($fields as $field_key=>$field_data) {

                        if($field_data["delete-field"])continue;

                        $classname = oqp_field_get_classname($field_data['model']);
                        
                        if(!$classname)continue;

                        $field = new $classname();

			$new_options['fields'][$field_key] = $field->field_admin_prepare_for_save($field_data);	
		}

		return $new_options;

	}
        function scripts(){
            if ( $this->have_fields() ) { 
                while ( $this->have_fields() ) {
                    $this->the_field();
                    $this->field->scripts();
                }
            }
            do_action('oqp_step_scripts');
        }
        function styles(){
            if ( $this->have_fields() ) { 
                while ( $this->have_fields() ) {
                    $this->the_field();
                    $this->field->styles();
                }
            }
            do_action('oqp_step_styles');
        }
        
	function is_step_complete() {
            global $post;
            
            if(!$post)return false;

            if ( $this->have_fields() ) { 
                while ( $this->have_fields() ) {

                    $field = $this->the_field();
                    $field->populate_postdata();

                    if (($field->is_required())&&(!$field->value)) {
                            return false;
                    }
                }
            }

            return true;

	}



}


		

function oqp_requested_post_id(){
    $post_id = $_REQUEST['oqp-post-id'];
    if(!$post_id) $post_id = $_COOKIE['oqp-post-id'];
    return $post_id;
}



class Oqp_Post extends Oqp_Form {
        var $requested_post_id;
        var $loaded_post;

        var $requested_step;
        

	function __construct($post_id,$page_id=false) {
		global $wp_query;
                global $post;
              

                //GET PAGE
                if(!$page_id) $page_id=oqp_post_get_form_page_id($post_id); 

                parent::__construct($page_id);   

                if(!$this->page_id) return false;

                $this->requested_step=$wp_query->get('oqp_step');

                if($post_id){
                    $this->requested_post_id = $post_id;
                }

		//GET REQUESTED POST
		if ($this->requested_post_id){
                    
                        if($post->ID!=$this->requested_post_id){
                            $this->loaded_post=$this->populate_post($this->requested_post_id);
                        }else{
                            $this->loaded_post=$post;
                        }

                        if(!$this->loaded_post->ID){//redirect to archive
                            $this->notices->error('oqp_form','unknown_post',$this->requested_post_id);
                            $redirect_link = $this->get_permalink();//oqp_get_creation_link();
                            oqp_debug($this->requested_post_id,"post not found");

                            wp_redirect($redirect_link);die();
                        }else{
                            setcookie( 'oqp-post-id',$this->loaded_post->ID, time()+60*60*24, COOKIEPATH ); //save in cookie so we can access the post through the tabs without having to save it
                        }

		}


                if($this->loaded_post->ID) {
                    //add delete post button
                    add_action('oqp_post_tabs','oqp_tabs_delete_button');
                }

                //post actions
                add_action('wp',array(&$this,'oqp_post_actions'));

                //populate post fields
                add_action('oqp_post_actions',array(&$this,'populate_postdata'));

                //remove steps with no capabilities
                //TO FIX TO CHECK
                add_filter('oqp_populated_steps',array(&$this,'form_check_capability'),99);

                //set enabled steps
                add_action('oqp_populated_steps',array(&$this,'set_enabled_steps'),100);


                add_action('wp_print_scripts',array(&$this,'scripts'));
                add_action('wp_print_scripts',array(&$this,'styles'));
                
                //add step class
                add_filter('post_class',array(&$this,'add_step_postclass'));
                


	}
        
        function add_step_postclass($classes){
            setup_requested_step();

            $classes[]='oqp-step-'.get_requested_step_key();
            $classes[]='oqp-step-'.oqp_get_step_slug();
            return $classes;
        }
        

        function set_enabled_steps(){

                if($this->action==_x('create','slug','oqp')){
                    $this->enabled_steps=unserialize( stripslashes( $_COOKIE['oqp_enabled_steps'] ) );
                }else{
                    //all steps available
                    $this->enabled_steps=array_keys((array)$this->steps);
                }

        }
        
        function scripts(){
            setup_requested_step();
            
            if(!$this->step) return false;
            
            $this->step->scripts();
        }
        function styles(){
            setup_requested_step();
            
            if(!$this->step) return false;
            
            $this->step->styles();
        }

	
	function form_check_capability($steps){
		
		if (!$steps) return false;
		
		foreach ($steps as $stepkey=>$step){
			$step_enabled = $step_check_capability();
			
			if(!$step_enabled) unset($steps[$stepkey]);
			
		}

		return $steps;

	}


	
	function switch_post_start(){
		global $oqp_switch_post;
		global $post;	

                if($post->ID==$this->loaded_post->ID) return false;

		//SWITCH POSTS
		$oqp_switch_post = $post;
		$post=$this->loaded_post;

                oqp_debug($post->ID,"switch to post#","warn");

		setup_postdata($post);
	}
	function switch_post_end(){
		global $oqp_switch_post;
		global $post;

                if(!$oqp_switch_post)return false;

		//SWITCH BACK POSTS
		$post = $oqp_switch_post;
		unset($oqp_switch_post);
                
                oqp_debug($post->ID,"switch back to post","warn");
	}
	

	
	function populate_post($post_id){

		if (!$post_id) return false;

		$post = get_post($post_id);
                
                oqp_debug($post,"populate_post");

		//POST DO NOT EXIST
		if (!$post->ID){
                    oqp_debug("the post#".$post_id." do not exists");
                    return false;
                    
                }
                
                //MISMATCH
		if ($post->post_type!=$this->post_type){
                        oqp_debug($this->post_type,"post type mismatch",'error');
                        $this->notices->error('oqp_form','post_type_mismatch');
			return false;
		}

		do_action('oqp_populated_post',$post->ID);
		
		return $post;
		
	}
        
        function oqp_post_actions(){
            oqp_debug(false,"oqp_post_actions","warn");
            do_action('oqp_post_actions');

        }
        
        function populate_postdata(){
            global $post;
            
            if ( $this->have_steps() ) { 
                while ( $this->have_steps() ) {

                    $this->the_step();

                    while ( $this->step->have_fields() ) {
                        $this->step->the_field();
                        $this->step->field->populate_postdata();
                        //oqp_debug($this->step->field->value,"field value for ".$this->step->field->name);

                    }
                }
            }
            oqp_debug($post,"populated postdata for post","warn");
            do_action('populated_postdata');
        }
        

}




class Oqp_Single_Post extends Oqp_Post {
	var $type='single';

	function __construct($post_id=false,$page_id=false) {
		global $post;
                
                if(!$post_id) $post_id = $post->ID;

		parent::__construct($post_id,$page_id);

                add_action('oqp_has_init',array(&$this,'init'));


	}
        
        function init(){
                //add admin post button
                add_action('oqp_post_tabs','oqp_tabs_admin_button');
                
                //add "oqp_single_post_actions" hook
                add_action('oqp_post_actions',array(&$this,'oqp_single_post_actions'));

                //remove empty steps
                add_action('populated_postdata',array(&$this,'remove_empty_steps'),5);
        }
        
        function oqp_single_post_actions(){
            oqp_debug(false,"oqp_single_post_actions","warn");
            do_action("oqp_single_post_actions");
        }
        
       function remove_empty_steps(){
           
           $this->rewind_steps();

            if (!$this->have_steps() ) return false;
                
                
            
            while ( $this->have_steps() ) {

                $this->the_step();

                //$this->step->rewind_fields();

                if (!$this->step->have_fields() ) return false;

                while ( $this->step->have_fields() ) {

                    $this->step->the_field();

                    oqp_debug($this->step->field->value,"checking field S".$this->current_step."F".$this->step->current_field." '".$this->step->field->name."'","warn");
                    
                    if(!$this->step->field->value) oqp_delete_field();

                }

                //check if step is empty
                if (!$this->step->have_fields() ) { 
                        //check if steps are empty and remove them
                        oqp_debug("step#".$this->current_step." is empty");
                        oqp_delete_step($this->current_step);
                }

                

            }
            


 

        }
        

}


class Oqp_Creation_Page extends Oqp_Post {
	var $type='form';
	var $action;
        var $new_post;
	var $enabled_steps=array();

	
	function __construct($post_id=false,$page_id=false) {
		global $wp_query,$post,$oqp_old_post;

		//GET REQUESTED ACTION
		$this->action = $wp_query->get('oqp_action');

		if (!$this->action){
			$this->action=_x('create','slug','oqp');
		}
                
		//GET REQUESTED STEP
                $requested_step=$wp_query->get('oqp_step');
                
                if($post_id)$page_id=oqp_post_get_form_page_id($post_id);

		//NEW POST / RESET.
                //


                //
                //create auto-draft post:
                //some stuff (uploading file, assigning taxonomies...) needs the post id.
		if((($this->action==_x('create','slug','oqp'))&&(!$requested_step))){
                    
                    //CANNOT CREATE NEW POSTS
                    $can_post_create = oqp_user_can_for_ptype('edit_posts',$this->post_type);
                    
                    if($can_post_create){

                        $this->new_post=true;

                        $form_options = Oqp_Form::get_options($page_id);

                        $new_post->post_title = __( 'Auto Draft' );
                        $new_post->post_status='draft';
                        $new_post->post_type = $form_options['post_type'];

                        $new_post = apply_filters('oqp_blank_post',$new_post);

                        $new_post->ID = wp_insert_post( $new_post );
                        $post_id = $new_post->ID;

                        oqp_debug($new_post,"NEW POST CREATED");

                        do_action('oqp_after_new_post',$post_id);
                        
                    }else{
                        //we will be redirected with redirect_cannot_create() @hook oqp_has_init
                    }
                    
                    

		}

		//FETCH POST
		parent::__construct($post_id,$page_id);

                

                if($this->loaded_post->ID){

                        $is_draft = oqp_is_post_a_draft($this->loaded_post);

                        if(!$is_draft){
                                //all steps available
                                $this->enabled_steps=array_keys($this->steps);
                        }

                        if (!$this->current_step) { //REQUESTED STEP
                                if (!empty($this->enabled_steps)) {
                                        if ($this->action==_x('create','slug','oqp')){//GET LAST COMPLETED STEP
                                                end($this->enabled_steps);
                                        }else{
                                                reset($this->enabled_steps);
                                        }

                                }
                        }

                }

		if($this->loaded_post->ID) {
				//add view post button
				add_action('oqp_post_tabs','oqp_tabs_viewpost_button');
		}
                
                
                //no capability to create new posts
                add_action('oqp_has_init',array(&$this,'redirect_cannot_create'));
                
                //no step set; reset form 
                add_action('oqp_has_init',array(&$this,'redirect_reset_form'));

                //no capability to edit current post
                add_action('oqp_has_init',array(&$this,'redirect_cannot_edit'));

                //cannot "admin" draft post (redirect to "create")
                add_action('oqp_has_init',array(&$this,'redirect_cannot_admin_draft'));

                //cannot "create" non-draft post (redirect to "create")
                add_action('oqp_has_init',array(&$this,'redirect_cannot_create_non_draft'));
                
                //INIT FORM
                add_action('oqp_has_init',array(&$this,'init'));

	}
        
        function init(){

            
               //add "oqp_creation_post_actions" hook
                add_action('oqp_post_actions',array(&$this,'oqp_creation_post_actions'));
                
                
                add_action('oqp_creation_post_actions',array(&$this,'switch_post_start'),1);
                add_action('oqp_creation_post_actions',array(&$this,'switch_post_end'),100);
                
                //delete post
		add_action('oqp_creation_post_actions',array(&$this,'delete_post'));
                
                //save post
		add_action('oqp_creation_post_actions',array(&$this,'save_post'));

                
		add_action('oqp_before_content',array(&$this,'switch_post_start'),1);
		add_action('oqp_after_content',array(&$this,'switch_post_end'),100);

        }
        
        
        function oqp_creation_post_actions(){
            oqp_debug(false,"oqp_creation_post_actions","warn");
            do_action("oqp_creation_post_actions");
        }

        function redirect_reset_form(){
             global $wp_query,$oqp_form;
             
            //RESET FORM CREATION
            $requested_step=$wp_query->get('oqp_step');
            if((($oqp_form->action==_x('create','slug','oqp'))&&(!$requested_step))){

                    $oqp_form->reset_form_creation();

                    $redirect_args['oqp_step']=$oqp_form->steps[0]->slug;
                    $redirect_url = add_query_arg($redirect_args,oqp_get_creation_link());

                    wp_redirect( $redirect_url );die();
            }
            
        }
        
        function redirect_cannot_create(){
            global $oqp_form;

            if ($oqp_form->loaded_post->ID)return false;

            //CANNOT CREATE NEW POSTS
            $can_post_create = oqp_user_can_for_ptype('edit_posts',$this->post_type);
            if($can_post_create)return false;
            
            if(!get_current_user_id()){
                $login_url=wp_login_url( oqp_get_creation_link() );
                $oqp_form->notices->error('oqp_form','not_logged',$login_url);
            }else{
                $oqp_form->notices->error('oqp_form','no_creation_cap',get_current_user_id());
            }
            $redirect_url = oqp_form_page_get_link();
            wp_redirect( $redirect_url );die();
            
        }
        
        function redirect_cannot_edit(){
            global $oqp_form;
            
            if (!$oqp_form->loaded_post->ID)return false;
            
            //CANNOT EDIT THIS POST
            $can_post_edit = current_user_can( 'edit_post', $oqp_form->loaded_post->ID );
            if($can_post_edit)return false;
            
            $oqp_form->notices->error('oqp_form','no_edit_cap',$oqp_form->loaded_post->ID);
            $redirect_url = oqp_get_base_link(false,$oqp_form->loaded_post->ID);
            wp_redirect( $redirect_url );die();

        }
        
        function redirect_cannot_admin_draft(){
            global $oqp_form;
            if (!$oqp_form->loaded_post->ID)return false;
            
            $is_draft = oqp_is_post_a_draft($oqp_form->loaded_post);


            if ($oqp_form->action==_x('admin','slug','oqp') && $is_draft) {
                    $redirect_args['oqp_step']=$oqp_form->get_step_slug();
                    $redirect_url = oqp_get_creation_link($redirect_args,$oqp_form->loaded_post->ID);

                    wp_redirect( $redirect_url );die();
            }
            
        }
        
        function redirect_cannot_create_non_draft(){
            global $oqp_form;
            if (!$oqp_form->loaded_post->ID)return false;
            
            $is_draft = oqp_is_post_a_draft($oqp_form->loaded_post);

            if ($oqp_form->action==_x('create','slug','oqp') && !$is_draft) {
                    $redirect_args['oqp_step']=$oqp_form->get_step_slug();
                    $redirect_url = oqp_get_edition_link($redirect_args,$oqp_form->loaded_post->ID);
                    wp_redirect( $redirect_url );die();
            }
            
        }


	
	function reset_form_creation(){
		setcookie( 'oqp-post-id', false, time() - 1000, COOKIEPATH );
		setcookie( 'oqp_enabled_steps', false, time() - 1000, COOKIEPATH );

                oqp_debug("reset_form_creation");
		
		do_action('oqp_reset_form_creation');
	
	}


	function save_post() {

		if ((!$_POST[oqp_get_form_input_name()]) && (!$_FILES[oqp_get_form_input_name()])) return false;
                //TO FIX WPNONCE


                setup_requested_step();
		$post_saved = $this->save_postdata();


		if ($this->notices->notices_count('errors')) $no_errors = true;
		
		$url_args['oqp_action']=$this->action;
		$url_args['step_key']=$this->current_step;


		if ($post_saved && $no_errors) { //all went well, tell them !

			if (did_action( 'oqp_validated_last_step' )) { //post creation finished
				$url_args['oqp_action']=_x('admin','slug','oqp');
                                if($this->action==_x('create','slug','oqp')){
                                        $this->notices->message('oqp_form','post_created',$this->loaded_post->ID);
                                }
			}else {
		
				if ($this->notices->notices_count('messages')) $no_messages = true;
				
				if ($no_messages){
                                        $this->notices->message('oqp_form','post_updated',$this->loaded_post->ID);
				}

                                if($this->action==_x('create','slug','oqp')){
                                    $url_args['step_key']=oqp_get_next_key($this->steps,$this->current_step);
                                }
			}


		}

		$redirect_url=oqp_get_base_link($url_args);

		wp_redirect( $redirect_url );die();

	}
	

	function save_postdata() {	
		global $post;
		global $wp_query;

                $post->ID = get_the_ID();


                oqp_debug($post->ID,"save_postdata for post");

		$post->post_author = get_current_user_id();
		$post->post_type =  $this->post_type;

		if (!$post->post_author) return false;

		if (!$post->ID) { //post do not exists yet, save it so we can use its ID when saving the fields

                    
                    //we need a post title to be able to save the post
                    
                        $post->post_title = __( 'Auto Draft' );
                        $post->post_status='draft';
			$post->ID = wp_insert_post( $post );

                        oqp_debug($post->ID,"oqp_new_post");
                        
			if (!get_the_ID()) {

                                $this->notices->error('oqp_form','saving_error');
				return false;
			}
			setcookie( 'oqp-post-id', get_the_ID(), time()+60*60*24, COOKIEPATH );
			do_action( 'oqp_insert_new_post' );
		}
                
		//VALIDATION
                //post id must be defined
		if (!$this->step_validation())$validation_errors=true;
                

                
                //COMPLETED STEPS
                if ($this->action==_x('create','slug','oqp')){

                    $previous_steps_completed = self::are_previous_steps_complete( $this->current_step );

                    if (!$previous_steps_completed)$this->notices->error('oqp_form','previous_steps_missing');
                }

		$post->ID = wp_update_post( $post );
                
                oqp_debug($post,"save_postdata");

		if (!get_the_ID()) {
			$this->notices->error('oqp_form','saving_error');
			return false;
		}


		if ((!in_array((int)$this->current_step,(array)$this->enabled_steps,true) ) && (!$validation_errors)) {
			$this->enabled_steps[] =(int)$this->current_step;
                        
		}

		$completed_steps = array_unique((array)$this->enabled_steps); 

		setcookie( 'oqp_enabled_steps', serialize( $completed_steps ), time()+60*60*24, COOKIEPATH );

                
		/* If we have completed all steps and hit done on the final step we can redirect to the completed ad */
		if ( count( $this->enabled_steps ) == count( $this->steps ) && $this->current_step == array_pop( array_keys( $this->steps ) ) ) {

			if (oqp_user_can_for_ptype('publish_posts',$this->post_type)) {
				$post_status='publish';
			}else{
				$post_status='pending';
			}

			$update_post['ID']=get_the_ID();
			$update_post['post_status']=$post_status;
			
			$post->ID = wp_update_post( $update_post );
			
			if (!get_the_ID()) {
				$this->notices->error('oqp_form','saving_error');
				return false;
                        }

                        do_action( 'oqp_validated_last_step', get_the_ID() );



		}

                if($post->ID)return true;
	}
	
	function step_validation() {

                

                if ( $this->step->have_fields() ) {

                    while ( $this->step->have_fields() ) {

                        $field = $this->step->the_field();

                        //get sent data
                        $field_datas = $field->get_form_value();

                        
                        $field_datas = apply_filters('oqp_sent_field_datas',$field_datas);
                        
                        //validate datas
                        $field_datas = $field->validate_postdata($field_datas);
                        $field_datas = apply_filters('oqp_validated_field_datas',$field_datas);

                        if (($field->is_required())&&(!$field_datas)) {
				$field->add_field_notice('error','field_required',$field->get_label());
			}else{
				$field->save_postdata($field_datas);
			}

			do_action('oqp_saved_field_data',$field_datas);
                        
                        
                        //field has errors
                        if ($this->notices->has_notices($field->get_notice_code()))$this->notices->error('oqp_step','missing_fields');
                
                        
                    }
                }

                do_action('step_validation');

 

                
                if (!$this->notices->has_notices('oqp_step'))return true;

	}
        
	function are_previous_steps_complete( $step_key=false ) {
            
            
            
            if(!$step_key)$step_key=$this->current_step;
            
            if($step_key==0) return true;
            
            $complete = true;
            
            $this->rewind_steps();
            
            if ( $this->have_steps() ) { 
                
                

                while ( $this->have_steps() ) {
                    
                    $this->the_step();
                    

                    if(!$this->step->is_step_complete()){
                        $complete=false;
                        break;
                    }


                    if($this->current_step==$step_key){
                        
                        oqp_debug($step_key,"are_previous_steps_complete");
                        break;
                    }

                }
            }
            
            
            $this->current_step = $step_key;
            return $complete;
        }
	
	function delete_post() {         
            global $post,$oqp_form;
            $slug = _x('delete','slug','oqp');
            if ($oqp_form->action!=$slug) return false;
            if (!$post->ID) return false;

            //TO FIX TO CHECK URGENT
            //CAPABILITY
            if (!check_admin_referer('oqp-'.$slug.'-'.$post->ID)) return false;

		$cap_needed = 'delete_posts';
		if ($post->post_status=='publish') {
			$cap_needed = 'delete_published_posts';
		}
		if ($post->post_author != get_current_user_id()) {
			$cap_needed = 'delete_others_posts';
		}
		
		if (!oqp_user_can_for_ptype($cap_needed,false)) {
			$this->notices->error('oqp_form','no_edit_cap',$this->loaded_post->ID);
		}else {

			if (!wp_delete_post($post->ID)) {
				$this->notices->error('oqp_form','saving_error',$this->loaded_post->ID);
                                $args['oqp_action']=_x('create','slug','oqp');
                                $args['step_key']=$this->current_step;
			}else {
				$this->notices->message('oqp_form','post_updated',$this->loaded_post->ID);
				$args['oqp_action']=_x('create','slug','oqp');
                                $args['post_id']=false;
				
			}

		}
                $redirect_url = oqp_get_creation_link($args,false);

		wp_redirect( $redirect_url );die();

	}
	

	

}

class Oqp_Notices {

	var $cookiename;
	var $errors;
	var $messages;
	var $notices_texts;

	function __construct(){
		global $oqp_form;
		global $post;
		
		$this->cookiename = 'oqp_notices';
		$this->errors = new WP_Error();
		$this->messages = new WP_Error();

		$post_label = strtolower(oqp_get_post_type_label());


		//add_action('oqp_post_actions',array(&$this,'load'),6);//must hook after oqp_save_form_data
                add_action( 'oqp_has_init', Array( &$this, 'load' ) );
		add_action( 'oqp_before_content', Array( &$this, 'form_notices' ) );
                add_action( 'oqp_before_content', Array( &$this, 'step_notices' ) );
	}
	
	
	
	function error($code,$slug,$data=false) {
                //$datas[$code]=$data;
		$this->errors->add($code,$slug,$data);
                oqp_debug($slug,"error:".$code);
		$this->save();

	}
	function message($code,$slug,$data=false) {
                //$datas[$code]=$data;
		$this->messages->add($code,$slug,$data);
                oqp_debug($slug,"error:".$code);
		$this->save();
	}
	//TO FIX USED ?
	function form_notices($style=false) {
		$this->html('oqp_form');
	}
	function step_notices($style=false) {
		$this->html('oqp_step');
	}
	
	function has_notices($code,$type='errors') {
		if ($this->get_notices($code)) return true;
	}


        function get_notices($code,$type='errors'){
            return $this->$type->get_error_messages($code);
        }
        
        function format_message($place,$slug,$type){
            global $oqp_form;
            $class=$this->$type;
            $msg_datas=$class->error_data;
            $msg_data=$msg_datas[$place];

            $messages['unknown_post']=__("The post requested do not exists","oqp");
            $messages['no_edit_cap']=sprintf(__("You do not have sufficient permissions to edit this %s","oqp"),strtolower(oqp_get_post_type_label()));
            $messages['no_creation_cap']=sprintf(__("You do not have sufficient permissions to create new %s.","oqp"),  strtolower(oqp_get_post_type_label('name')));
            $messages['not_logged']=sprintf(__("You do not have sufficient permissions to create new %s.  Please %s !","oqp"),strtolower(oqp_get_post_type_label('name')),'<a href="'.$msg_data.'">'.__('login','oqp').'</a>');

            
            
            $messages['post_created']=sprintf(__("The %s#%d has been created","oqp"),strtolower(oqp_get_post_type_label()),$oqp_form->loaded_post->ID);
            $messages['post_updated']=sprintf(__("The %s#%d has been updated","oqp"),strtolower(oqp_get_post_type_label()),$oqp_form->loaded_post->ID);
            
            
            $messages['previous_steps_missing']=__("Please complete the previous steps","oqp");
            $messages['saving_error']=sprintf(__("An error occured while updating this %s","oqp"),strtolower(oqp_get_post_type_label()));
            $messages['missing_fields']=__("Please complete all the required fields","oqp");
            

            
            $messages['field_required']=sprintf(__("This field is required","oqp"),$msg_data);
            $messages['upload_error']=__("Error while uploading files","oqp");
            $messages['upload_type_error']=sprintf(__("File type is incorrect for file '%s'","oqp"),$msg_data);
            
            $messages['attachment_nonce']=__("You are not allowed to do this action","oqp");
            
            $messages['attachment_deleted']=sprintf(__("The attachment #%d has been deleted","oqp"),$msg_data);
            $messages['attachment_deleted_error']=sprintf(__("Error while trying to delete the attachment #%d","oqp"),$msg_data);
            
            $messages['attachment_set_default']=sprintf(__("The attachment #%d has been set as default","oqp"),$msg_data);
            $messages['attachment_set_default_error']=sprintf(__("Error while trying to set the attachment #%d as default","oqp"),$msg_data);
            

            
            $messages = apply_filters('oqp_notices_messages',$messages);

            return $messages[$slug];
            
            return $msg;
        }
        
        
	function get_html($place) {

		$errors = $this->get_notices($place);
		
		$messages = $this->get_notices($place,'messages');
                


		$html.='<div class="notice">';
			foreach ($errors as $slug) {

                                $message = $this->format_message($place,$slug,'errors');
       
				$html.='<p class="oqp_notice_error">';
					$html.=$message;
				$html.='</p>';
			}
			foreach ($messages as $slug) {
                            
                                $message = $this->format_message($place,$slug,'messages');
     
				$html.='<p class="oqp_notice_msg">';
					$html.=$message;
				$html.='</p>';
			}
		$html.='</div>';
		return $html;
	}
	function html($code) {
		echo $this->get_html($code);
	}

	function notices_count($type='errors'){
		$notices = $this->$type->errors;
		if (!count($notices)) return true;
	}
	
	//TO FIX
	//PROBLEM : WE NEVER KNOW WHEN WE WILL DO A REDIRECTION, ETC.
	//SO WE SAVE THE COOKIE EACH TIME A NOTICE IS SET.
	//ANY BETTER IDEA ?
	function save(){
            
                if($this->errors->errors)
                    $errors['errors']=$this->errors->errors;
                
                if($this->errors->error_data)
                    $errors['error_data']=$this->errors->error_data;
                
                if($this->messages->errors)
                    $messages['errors']=$this->messages->errors;
                
                if($this->errors->error_data)
                    $messages['error_data']=$this->messages->error_data;



		$messages = array(
			'errors'=>array_unique((array)$this->messages->errors),
			'error_data'=>array_unique((array)$this->messages->error_data),
		);

		$datas=array(
			'errors'=>$errors,
			'messages'=>$messages
		);


		setcookie( $this->cookiename, serialize( $datas ), time()+60*60*24, COOKIEPATH );
	}
	

	function load(){
		$datas = unserialize( stripslashes( $_COOKIE[$this->cookiename] ) );

		//delete stored errors
		setcookie( $this->cookiename, false, time() - 1000, COOKIEPATH );
		
		$this->errors->errors = $datas['errors']['errors'];
		$this->errors->error_data = $datas['errors']['error_data'];
		
		$this->messages->errors = $datas['messages']['errors'];
		$this->messages->error_data = $datas['messages']['error_data'];

	}

}



?>