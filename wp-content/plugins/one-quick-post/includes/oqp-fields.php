<?php
/**
 * API for creating field types
 * 
 *
 * This class must be extended for each type and the following methods overridden:
 *
 * OQP_Form_Step_Field_Type::widget_display(), OQP_Form_Step_Field_Type::display(),
 * OQP_Form_Step_Field_Type::edit_screen_save(), OQP_Form_Step_Field_Type::edit_screen(),
 * OQP_Form_Step_Field_Type::create_screen_save(), OQP_Form_Step_Field_Type::create_screen()
 *
 */
 
function oqp_field_get_classname($model){
    $classname = 'OQP_Form_Step_Field_'.ucfirst($model);
    //CLASS DO NOT EXISTS
    if (!class_exists($classname)) return false;
    
    return $classname;
}

function oqp_admin_form_new_field() {
        global $oqp_form;
        global $oqp_fields_types;

        $step=$oqp_form->step;

        //FIELD MODEL
        ?>
        <select name='<?php echo oqp_get_form_input_name();?>[new-field][model]'>
        <option value="">---</option>
        <?php
                foreach ($oqp_fields_types as $model) {
                        //TO FIX TO CHECK
                        if ((!$model['multiple']) && (oqp_count_form_fields($model['slug']))) continue;

                echo '<option value="'.$model['slug'].'">'.$model['name'].'</option>';
                }

        ?>
        </select>
        <?php
        
        //STEP
        $next_step=$step->step_key+1;
 
        echo __("in Step","oqp").":";
        ?>
        <select name='<?php echo oqp_get_form_input_name();?>[new-field][step_id]'>
            <option value="<?php echo count($oqp_form->steps);?>"><?php _e("New Step","oqp");?></option>
        <?php
                foreach ($oqp_form->steps as $step_key=>$step) {
                echo '<option value="'.$step_key.'">'.$step->get_step_name().'</option>';
                }

        ?>
            
        </select>
        <?php
}

function oqp_count_form_fields($model=false){
    global $oqp_form;
    
    $count=0;
    
    foreach($oqp_form->steps as $step){
        $fields = $step->fields;
        
        if(!$model){
            $count+=count($fields);
        }else{
            foreach((array)$fields as $field){
                if($field->model==$model)$count+=1;
            }
        }
        
    }
    return $count;
}

 
class OQP_Form_Step_Field {
	var $key;
	var $slug;
	var $name;
	var $multiple=true;
	var $required=false;
	var $enabled=true;
	var $value;
	var $label;
	var $description;
        var $edit;
        var $completed;

	
	var $cap_edit='edit_posts'; //capability needed to edit this field
	var $cap_view='read'; //capability needed to view this field
        
        function __construct() {

        }


        
        function field_admin_notice($message,$type="error"){
            add_settings_error('oqp_edit_form',self::get_notice_code(),$message,$type);
        }

	function field_admin_prepare_for_save($options) {
		if ($options['delete']) return false;

		if (($options['new'])&&(!$options['model'])) return false;
                
               
                
                $options['name']=trim(strip_tags($options['name']));

                //VALIDATE EXTRA OPTIONS
                if(!$options['new']){
                    if(method_exists($this,'extra_admin_options_save')) $options = $this->extra_admin_options_save($options);
                }
                
                unset($options['new']);

                if (!$options) return false;

                
                return array_filter($options);

	}


	function display() {
		die( 'function OQP_Form_Step_Field::display() must be over-ridden in a sub-class.' );
	}

	function edit() {
		die( 'function OQP_Form_Step_Field::edit() must be over-ridden in a sub-class.' );
	}

	function post_value() {
		die( 'function OQP_Form_Step_Field::post_value() must be over-ridden in a sub-class.' );
	}

	function save_post_value() {
		die( 'function OQP_Form_Step_Field::save_post_value() must be over-ridden in a sub-class.' );
	}
	
	function get_label(){
		//LABEL
		$label = $this->label;
		if (!$label) $label=$this->name;
                $label=strip_tags($label);
		return apply_filters('oqp_field_label',$label);
	}
        
        function is_required(){
            return $this->required;
        }
        
        function is_enabled(){
            return true;
        }
        
        function get_cap_edit(){
            return $this->cap_edit;
        }
        function get_cap_view(){
            return $this->cap_view;
        }
	
	function get_description(){
		return $this->description;
	}
	
	function get_placeholder(){
		return $this->placeholder;
	}
	
	
	function get_field(){
		return $this->value;
	}

	function populate_field($key,$options){

		foreach($options as $var=>$option) {
			$this->$var=$option;
		}

		$this->key=$key;

	}

	///NOTICES
        
        function get_notice_code(){
            global $oqp_form;
            $code='oqp_step_'.$oqp_form->current_step.'_field_'.$oqp_form->step->current_field;
            return $code;
        }
        
	function add_field_notice($type,$slug,$data=false){

		global $oqp_form;

		if($type=='message'){
                        $oqp_form->notices->message($this->get_notice_code(),$slug,false);
		}else{
			$oqp_form->notices->error($this->get_notice_code(),$slug,false);
		}
	}
	
	function field_classes(){
		//field classes
		$classes[]='oqp-form-field';
		$classes[]=$this->model->slug;

		if ($this->required) {
			$classes[]='required';
		}
                
		if ($this->edit) {
			$classes[]='editable';
                        if($this->completed)$classes[]="completed";
		}
                
		
		if ($this->has_notices('errors')) {
			$classes[]='validation-error';
		}
		
		$classes[]='field-'.$this->slug;

		
		return $classes;

		
	}

	
	function has_notices($type){
		//TO FIX

		global $oqp_form;
                
              
		$notices = $oqp_form->notices->has_notices($this->get_notice_code(),$type);

                return $notices;

	}
	function get_notice_html(){

		global $oqp_form;

		echo $oqp_form->notices->get_html($this->get_notice_code());
	}

	function get_form_value(){
		global $oqp_form;

		return $_POST[oqp_get_form_input_name()]['steps'][$oqp_form->current_step]['fields'][$oqp_form->step->current_field];
	}
	
	function scripts(){
	}
	
	function styles(){
	}


	// Private Methods

	function _register() {

		global $oqp_fields_types;
		$oqp_fields_types[]=array(
			'slug'=>$this->slug,
			'name'=>$this->name,
			'multiple'=>$this->multiple
		);
		/*
		global $bp;

		if ( $this->enable_create_step ) {
			// Insert the group creation step for the new group extension
			$bp->groups->group_creation_steps[$this->slug] = array( 'name' => $this->name, 'slug' => $this->slug, 'position' => $this->create_step_position );

			// Attach the group creation step display content action
			add_action( 'groups_custom_create_steps', array( &$this, 'create_screen' ) );

			// Attach the group creation step save content action
			add_action( 'groups_create_group_step_save_' . $this->slug, array( &$this, 'create_screen_save' ) );
		}

		// Construct the admin edit tab for the new group extension
		if ( $this->enable_edit_item && $bp->is_item_admin ) {
			add_action( 'groups_admin_tabs', create_function( '$current, $group_slug', 'if ( "' . esc_attr( $this->slug ) . '" == $current ) $selected = " class=\"current\""; echo "<li{$selected}><a href=\"' . $bp->root_domain . '/' . $bp->groups->slug . '/{$group_slug}/admin/' . esc_attr( $this->slug ) . '\">' . esc_attr( $this->name ) . '</a></li>";' ), 10, 2 );

			// Catch the edit screen and forward it to the plugin template
			if ( $bp->current_component == $bp->groups->slug && 'admin' == $bp->current_action && $this->slug == $bp->action_variables[0] ) {
				add_action( 'wp', array( &$this, 'edit_screen_save' ) );
				add_action( 'groups_custom_edit_steps', array( &$this, 'edit_screen' ) );

				if ( '' != locate_template( array( 'groups/single/home.php' ), false ) ) {
					bp_core_load_template( apply_filters( 'groups_template_group_home', 'groups/single/home' ) );
				} else {
					add_action( 'bp_template_content_header', create_function( '', 'echo "<ul class=\"content-header-nav\">"; bp_group_admin_tabs(); echo "</ul>";' ) );
					add_action( 'bp_template_content', array( &$this, 'edit_screen' ) );
					bp_core_load_template( apply_filters( 'bp_core_template_plugin', '/groups/single/plugins' ) );
				}
			}
		}

		// When we are viewing a single group, add the group extension nav item
		if ( $this->visibility == 'public' || ( $this->visibility != 'public' && $bp->groups->current_group->user_has_access ) ) {
			if ( $this->enable_nav_item ) {
				if ( $bp->current_component == $bp->groups->slug && $bp->is_single_item )
					bp_core_new_subnav_item( array( 'name' => ( !$this->nav_item_name ) ? $this->name : $this->nav_item_name, 'slug' => $this->slug, 'parent_slug' => BP_GROUPS_SLUG, 'parent_url' => bp_get_group_permalink( $bp->groups->current_group ), 'position' => $this->nav_item_position, 'item_css_id' => 'nav-' . $this->slug, 'screen_function' => array( &$this, '_display_hook' ), 'user_has_access' => $this->enable_nav_item ) );

				// When we are viewing the extension display page, set the title and options title
				if ( $bp->current_component == $bp->groups->slug && $bp->is_single_item && $bp->current_action == $this->slug ) {
					add_action( 'bp_template_content_header', create_function( '', 'echo "' . esc_attr( $this->name ) . '";' ) );
			 		add_action( 'bp_template_title', create_function( '', 'echo "' . esc_attr( $this->name ) . '";' ) );
				}
			}

			// Hook the group home widget
			if ( $bp->current_component == $bp->groups->slug && $bp->is_single_item && ( !$bp->current_action || 'home' == $bp->current_action ) )
				add_action( $this->display_hook, array( &$this, 'widget_display' ) );
		}
		*/
	}
	
	function field_check_capability(){

		global $oqp_form;

		if ($oqp_form->type=='form'){
			$check_cap='cap_edit';
		}else{
			$check_cap='cap_view';
		}
		
		$cap = $this->$check_cap;

		$field_enabled = oqp_user_can_for_ptype($cap,$oqp_form->post_type);

		return $field_enabled;
	}
	
}

function oqp_register_field_type( $field_type_class ) {

	if ( !class_exists( $field_type_class ) )
		return false;

	/* Register the group extension on the bp_init action so we have access to all plugins */
	$field = new $field_type_class;
	$field->_register();
	//add_action( 'init', create_function( '', '$extension = new ' . $field_type_class . '; add_action( "wp", array( &$extension, "_register" ), 2 );' ), 11 );
}

class OQP_Form_Step_Field_Title extends OQP_Form_Step_Field {
	function __construct(){
		$this->slug='title';
		$this->name=__('Title');
		$this->required=true; //title always required else the post is not saved
                $label = strtolower(oqp_get_post_type_label());
		$this->placeholder=sprintf(__('Please enter a title for your %s','oqp'),$label);
	}
	
	function is_enabled(){
                global $oqp_form;
		if(post_type_supports($oqp_form->post_type,'title')) return true;
		return false;
	}

	function get_field() {
		$html = $this->value;
		if ($html==__( 'Auto Draft' )) return false;
		return $html;
	}

	function get_edit_field() {
		$html = '<span class="oqp-post-item">'.$this->get_field().'</span>';
		$html = '<input type="text" placeholder="'.$this->get_placeholder().'" name="'.oqp_get_form_field_input_name().'" value="'.$this->value.'"/>';
		return $html;
	}
	
	function populate_postdata() {
		$title = get_the_title();
		if($title!=__( 'Auto Draft' )) $this->value=$title;
	}
        
        function validate_postdata($title){
            $title = trim($title);
            //if(!$title) $title=__( 'Auto Draft' );
            return $title;
        }
	
	//SAVE POST
	function save_postdata($title) {
		global $post;

                if ($title==__( 'Auto Draft' )){
			$has_title=false;
		}else {
			$has_title=true;
		}

		if (!$has_title && ($this->required)) {
                        $this->add_field_notice('error','field_required',$this->get_label());
		}

		$post->post_title=$title;

		return $has_title;

	}
}
class OQP_Form_Step_Field_Section extends OQP_Form_Step_Field {
    
        var $slug;
        var $name;
        var $placeholder;
        var $section_id;
        var $wysiwyg;
        var $upload;
        var $multiple=true;
        var $split_tag='<!--oqp-section-->';
    
	function __construct(){
		$this->slug='section';
		$this->name=__('Text section','oqp');
	}

        
	function extra_admin_options(){
		global $oqp_form;
                
                if(!$this->section_id) $this->section_id = oqp_count_form_fields($this->model);

		?>
		<p>
                        <label for="<?php echo oqp_get_form_field_input_name();?>[section_id]"><?php _e('Section id','oqp');?></label>
			<input size="2" type="text" disabled="disabled" value="<?php echo $this->section_id;?>"/>
                        <input size="2" type="hidden" value="<?php echo $this->section_id;?>" name="<?php echo oqp_get_form_field_input_name();?>[section_id]"/>
		</p>
		<p>
                        <label for="<?php echo oqp_get_form_field_input_name();?>[wysiwyg]"><?php _e('WYSIWYG','oqp');?></label>
                        <input size="2" type="checkbox" <?php echo checked(isset($this->wysiwyg), true, false );?> name="<?php echo oqp_get_form_field_input_name();?>[wysiwyg]"/>
		</p>
		<p>
                        <label for="<?php echo oqp_get_form_field_input_name();?>[upload]"><?php _e('Media Upload','oqp');?></label>
                        <input size="2" type="checkbox" <?php echo checked(isset($this->upload), true, false );?> name="<?php echo oqp_get_form_field_input_name();?>[upload]"/> (<?php echo _e('WYSIWYG must be checked','oqp');?>)
		</p>
		<?php
	}

	
	function is_enabled(){ 
                global $oqp_form;
		if(post_type_supports($oqp_form->post_type,'editor')) return true;
		return false;
	}
	
	function get_placeholder(){
		global $oqp_form;
		$label = strtolower(oqp_get_post_type_label());
		return sprintf(__('Enter a description for your %s','oqp'),$label);
	}
        
        function filter_media_upload_iframe_src($src) {
            global $post;
            global $oqp_form;
            return add_query_arg(array("post_id" => $post->ID,"oqp"=>$oqp_form->page_id), $src);
        }
        
        


	function get_edit_field() {
            
            $content = html_entity_decode($this->value);
            $field_id = "testeditor"; //TO FIX URGENT !!!
            
            $settings =   array(
                'wpautop' => true, // use wpautop?
                'media_buttons' => $this->upload, // show insert/upload button(s)
                'textarea_name' => oqp_get_form_field_input_name(), 
                'textarea_rows' => get_option('default_post_edit_rows', 10), // rows="..."
                'tabindex' => '',
                'editor_css' => '', // intended for extra styles for both visual and HTML editors buttons, needs to include the <style> tags, can use "scoped".
                'editor_class' => '', // add extra class(es) to the editor textarea
                'teeny' => true, // output the minimal editor config used in Press This
                'dfw' => false, // replace the default fullscreen with DFW (needs specific css)
                'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
                'quicktags' => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()
            );

            if($this->wysiwyg){
                
                //MEDIA LIBRARY - loads the correct post ID 
                add_filter('_upload_iframe_src', array(&$this,'filter_media_upload_iframe_src'));
                
                wp_editor( $content,$field_id, $settings );
                
                
                
            }else{
                echo '<textarea placeholder="'.$this->get_placeholder().'" name="'.oqp_get_form_field_input_name().'">'.wptexturize($this->value).'</textarea>';
            }
	}

	function populate_postdata() {
		global $post;
                
                $sections_count = oqp_count_form_fields($this->model);
                $post_content = $post->post_content;

                if($sections_count==1){
                    $this->value=$post_content;
                }else{
                    $sections = preg_split('/'.$this->split_tag.'/',$post_content);
                    $this->value=trim($sections[$this->section_id-1]);
                }
	}
        
        function validate_postdata($desc){
            $desc = trim($desc);
            return $desc;
        }
	
	//SAVE POST
	function save_postdata($desc) {
		global $post;
		global $oqp_form;

                $sections_count = oqp_count_form_fields($this->model);
                $post_content = $post->post_content;

                if($sections_count==1){
                    $post->post_content = $desc;
                }else{
                    $delimiter = $split_tag;
                    $sections = preg_split('/'.$this->split_tag.'/',$post_content);
                    $sections[$this->section_id-1]=$desc;
                    $post->post_content = implode("\r".$this->split_tag."\r",$sections);
                }

		return true;
	}

}

class OQP_Form_Step_Field_Excerpt extends OQP_Form_Step_Field {
	function __construct(){
		$this->slug='excerpt';
		$this->name=__('Excerpt');
	}
	
	function is_enabled(){ 
                global $oqp_form;
		if(post_type_supports($oqp_form->post_type,'excerpt')) return true;
		return false;
	}
	
	function get_placeholder(){
		global $oqp_form;
		$label = strtolower(oqp_get_post_type_label());
		return sprintf(__("Enter a few words to summarize your %s.  If empty, the excerpt will be auto-generated from the content.",'oqp'),$label);
		
	}

	function get_edit_field() {
		return '<textarea placeholder="'.$this->get_placeholder().'" name="'.oqp_get_form_field_input_name().'">'.wptexturize($this->value).'</textarea>';

	}
	
	function populate_postdata() {
		global $post;
		$this->value=$post->post_excerpt;
	}
        
        function validate_postdata($excerpt){
            $excerpt = trim($excerpt);
            return $excerpt;
        }
	
	//SAVE POST
	function save_postdata($excerpt) {
		global $post;
		global $oqp_form;

                $post->post_excerpt = $excerpt;
                return true;
	}

}

class OQP_Form_Step_Field_Taxonomy extends OQP_Form_Step_Field {
	var $taxonomy;
	var $taxonomy_args;
	var $tax_obj;
	var $label;
	var $hierarchical;
	var $autocomplete;

	function OQP_Form_Step_Field_Taxonomy(){

		$this->slug='taxonomy';
		$this->name=__('Taxonomy');
		
                //$this->cap_edit=;
                
                
		
		//TO FIX
		//should not be required if no taxonomy terms existing & taxonomy is hierarchical
		

	}
	//ADMIN
	function extra_admin_options(){
		global $oqp_form;
		$taxonomies = get_object_taxonomies( $oqp_form->post_type, 'names' );
                $tax_obj = get_taxonomy($this->taxonomy);
		?>
                <p>
                    <label for="<?php echo oqp_get_form_field_input_name();?>[taxonomy]"><?php _e('Taxonomy','oqp');?></label>
                    <select name="<?php echo oqp_get_form_field_input_name();?>[taxonomy]">
                    <option value=""></option>
                    <?php

                    foreach ((array)$taxonomies as $tax_slug) {
                            $taxonomy = get_taxonomy($tax_slug); 



                            $tax_name = $taxonomy->labels->name;
                            $tax_slug = $taxonomy->name;
                            unset($atts);
                            if ($tax_slug==$this->taxonomy){

                                    //SELECTED
                                    $atts.=" SELECTED";
                            }
                            ?>

                            <option<?php echo $atts;?> value="<?php echo $tax_slug;?>"><?php echo $tax_name;?></option>
                            <?php
                    }




                    ?>
                    </select>
                </p>
		<p>
			<label for="<?php echo oqp_get_form_field_input_name();?>[taxonomy_args]"><?php _e('Taxonomy Settings','oqp');?></label>
			<input type="text" value="<?php echo $this->taxonomy_args;?>" name="<?php echo oqp_get_form_field_input_name();?>[taxonomy_args]">
		</p>
		<p>
                        <label for="<?php echo oqp_get_form_field_input_name();?>[can_add_terms]"><?php _e('Assign Terms','oqp');?></label>
			<input type="checkbox" value="1" <?php echo checked(($this->can_add_terms), true, false );?> name="<?php echo oqp_get_form_field_input_name();?>[can_add_terms]">
                        <?php printf(__('User can create new %s','oqp'),  strtolower($tax_obj->labels->name));?>
                        <?php if ($this->get_cap_edit()!=$tax_obj->cap->assign_terms){?>
                            (<?php printf(__('only if he has the capability "%s"','oqp'),'<em>'.$tax_obj->cap->assign_terms.'</em>');?>)
                        <?php }?>
                        
		</p>
		<?php
	}
	
	function get_placeholder(){
		//THIS TAX
		$selected_tax=get_taxonomy($this->taxonomy);
		//PLACEHOLDER
		if (!$selected_tax->hierarchical){
			return sprintf(__('Please enter some %s, separated with commas','oqp'),strtolower($selected_tax->labels->name));
		}
		
	}
	
	function extra_admin_options_save($options) {
		if (!$options['taxonomy'])return false;
		return $options;
	}
	
        //TO CHECK
	function populate_field($key,$args){

		parent::populate_field($key,$args);
		
		if ($this->taxonomy) {
			$tax_obj = get_taxonomy($this->taxonomy);
                        
                    //disabled if tax do not exists
                    if (!$tax_obj) {
                            $this->enabled=false;
                            return false;
                    }
                    
                    //disabled if user cannot assign terms
                    if(($oqp_form->type=='form')&&(!oqp_user_can($tax_obj->cap->assign_terms))){
                        $this->enabled=false;

                        return false;
                    }

                    //not required if tax has no terms set.
                    if($args['required']){
                        $taxonomy_terms = get_terms($this->taxonomy,array('hide_empty'=>false));
                        if(!$taxonomy_terms)$this->required=false;
                    }
                        
		

			$this->tax_obj=$tax_obj;
			$this->label=$this->tax_obj->label;
                        
                        $this->hierarchical=$tax_obj->hierarchical;


		}
	}
	
	//FRONTEND
	
	function scripts(){
            
            
                if ($this->hierarchical){

                    wp_enqueue_script( 'jquery.easyListSplitter', oqp_get_theme_file_url('jquery.easyListSplitter.js','_inc/js'),array('jquery'), '1.0.2' );

                            wp_enqueue_script( 'jquery.collapsibleCheckboxTree', oqp_get_theme_file_url('jquery.collapsibleCheckboxTree.js','_inc/js'),array('jquery'), '1.0.1' );
                            wp_enqueue_script( 'oqp.collapsibleCheckboxTree', oqp_get_theme_file_url('oqp.collapsibleCheckboxTree.js','_inc/js'),array('jquery','jquery.collapsibleCheckboxTree'), OQP_VERSION );

                }else {
			//wp_enqueue_script( 'jquery.autocomplete', oqp_get_theme_file_url('jquery.autocomplete.pack.js','_inc/js'),array('jquery'), '1.1' );
		}
	}
	
	function styles(){
		if ($this->hierarchical) {
			wp_enqueue_style('jquery.collapsibleCheckboxTree', oqp_get_theme_file_url('jquery.collapsibleCheckboxTree.css','_inc/css'));
		} else {
			//wp_enqueue_style('oqp-autocomplete', oqp_get_theme_file_url('jquery.autocomplete.css','_inc/js/jquery-autocomplete'));
		}
	}
	
	function get_field() {
		return get_the_term_list( get_the_ID(), $this->tax_obj->name);
	}

	function get_edit_field() {
                //edit_terms
                //assign_terms
		parse_str($this->taxonomy_args, $tax_settings);

		$hierarchical=$this->tax_obj->hierarchical;

		if (!$hierarchical) {
                        foreach($this->value as $term_id){
                            $term = get_term_by('id',$term_id,$this->tax_obj->name);
                            
                            $term_names[]=$term->name;
                        }
                        $value=implode(', ',(array)$term_names);
                        
			$tax_html.="\t".'<input placeholder="'.$this->get_placeholder().'" type="text" name="'.oqp_get_form_field_input_name().'" class="autocomplete" value="'.$value.'"/>'."\n";
		}else {
                    
                 
                       //GET CATEGORIES
                        $default_args = array(
                            'hide_empty'=>false,
                            'show_count'=>false,
                            'taxonomy'=>$this->tax_obj->name,
                            'echo'=>false,
                            'title_li'=>false,
                            'walker'=>new Oqp_Walker_Category(),
                            'link'=>false,
                            'input_type'=>'radio',
                            'input_name'=>oqp_get_form_field_input_name()
                        );
                        
                        parse_str($this->taxonomy_args, $args);

                        
                        $args['selected']=$this->value;
                        if (!is_array($args['selected']))$args['selected']=explode(',',$args['selected']);
                        
                        

                        $args = wp_parse_args( $args,$default_args);

				$tax_html.="\t".'<ul class="expandable" id="oqp_tax_'.$this->tax_obj->name.'">'."\n";
				$tax_html.=wp_list_categories( $args );
                                
                                //ADD TAX
                                if(oqp_user_can($this->tax_obj->cap->edit_terms)){
                                    $tax_html_new.='<li class="oqp-add-term '.$this->tax_obj->name.'">';
                                    $tax_html_new.='<input type="radio" value="add-new" name="'.oqp_get_form_field_input_name().'[]" id="'.$this->tax_obj->name.'-new">';

                                    $tax_html_new.=' <input size="5" placeholder="'.__('Add new','oqp').'" name="'.oqp_get_form_field_input_name().'[add-new][value]" type="input" value=""/>';
                                   
                                    
                                    if($hierarchical){
                                        $dropdown_args = array(
                                            'echo'=>false,
                                            'hide_empty'=>$args['hide_empty'],
                                            'taxonomy'=>$args['taxonomy'],
                                            'hierarchical'=>$hierarchical,
                                            'show_option_all'=>'('.__('no parent','oqp').')',
                                            'name'=>oqp_get_form_field_input_name().'[add-new][parent]'
                                        );
                                        $dropdown = wp_dropdown_categories( $dropdown_args );
                                        $tax_html_new.=' <small class="oqp-add-term-childof">'.__('in','oqp').': '.$dropdown.'</small>';
                                    }
                                    
                                     $tax_html_new.='</li>';
                                    
                                    
                                    
                                    $tax_html.=$tax_html_new;
                                }
                                
				$tax_html.="\t</ul>\n";
		}
                

                
		return $tax_html;
	}
	
	function populate_postdata() {

		$terms = get_the_terms(get_the_ID(), $this->taxonomy );

                if(empty($terms)) {
                    $this->value = array();
                }else{                
                    foreach ($terms as $term) {
                            $terms_ids[]=$term->term_id;
                    }
                    $this->value=$terms_ids;
                }
	}
        
        //TO FIX TO CHECK
        function validate_postdata($tax_data){
            if(is_array($tax_data)){ //input = array of ids
                //INSERT NEW TERM
                foreach($tax_data as $key=>$value){
                    if($value!="add-new") continue;
                    unset($tax_data[$key]);

                    if(oqp_user_can($this->tax_obj->cap->edit_terms)){

                        $new_term_value=trim($tax_data['add-new']['value']);
                        if(!$new_term_value)continue;
                        $new_term_parent=$tax_data['add-new']['parent'];

                        $new_term = wp_insert_term($new_term_value,$this->tax_obj->name, array("parent" => $new_term_parent));

                        $tax_data[]=$new_term['term_id'];
                    }
                }


                foreach($tax_data as $key=>$value){
                    $term = get_term_by('id',$value,$this->tax_obj->name);
                    if($term){
                        $terms_ids=$term->term_id;
                    }

                }
            }else{//input = string of names
                $terms_string=$tax_data;
                if(!oqp_user_can($this->tax_obj->cap->edit_terms)){//remove unexistant terms,user cannot create them.
                    $terms_names = explode(',',$terms_string);
                    unset($terms_string);
                    foreach($terms_names as $key=>$name){
                        $term = get_term_by('name',$name,$this->tax_obj->name);
                        if($term){
                            $terms_ids=$term->term_id;
                        }

                    }
                }
            }


            if($terms_ids){
                $save_terms = $terms_ids;
            }else{
                $save_terms = $terms_string;
            }

            return $save_terms;
        }
	
	//SAVE POST
	function save_postdata($terms) {
		if ((!$terms)&&(!$this->required)) return true;

                wp_set_post_terms(get_the_ID(),$terms,$this->taxonomy);
                return true;

	}
	
	
}
class OQP_Form_Step_Field_Custom extends OQP_Form_Step_Field {
	var $meta_key;
	var $multiple_values=false;
	var $type='text';
	function __construct(){
		$this->slug='custom';
		$this->name=__('Custom Field');
	}
	

	function is_enabled(){ 
                global $oqp_form;
		if(post_type_supports($oqp_form->post_type,'custom-fields')) return true;
		return false;
	}
	
	
	function extra_admin_options(){
		global $oqp_form;
                
                if($this->meta_key){
                    $meta_key_split=explode('oqp_field_',$this->meta_key);
                    $meta_key_suffix=$meta_key_split[1];
                }
                
		?>
		<p>
                        <label for="<?php echo oqp_get_form_field_input_name();?>[meta_key]"><?php _e('Meta key name','oqp');?></label>
			<code>oqp_field_</code><input type="text" value="<?php echo $meta_key_suffix;?>" name="<?php echo oqp_get_form_field_input_name();?>[meta_key]">
		</p>
		<p>
                        <label for="<?php echo oqp_get_form_field_input_name();?>[multiple_values]"><?php _e('Multiple values','oqp');?></label>
			<input type="checkbox" value="1" <?php echo checked(($this->multiple_values), true, false );?> name="<?php echo oqp_get_form_field_input_name();?>[multiple_values]">
		</p>
		<?php
	}
        
	function extra_admin_options_save($options) {
                
                if($options['meta_key']) {
                    $options['meta_key']='oqp_field_'.$options['meta_key'];
                }else{
                    self::field_admin_notice(__('You need to enter a meta key name for the field','oqp'));
                }
            
		
		return $options;
	}
        

	function get_edit_field(){
            
            $value = $this->value;

            if(($this->multiple_values)&&(is_array($value))) {

                foreach($value as $val){
                    $html.=self::get_edit_field_single($val);

                }

                //empty value
                $html.=self::get_edit_field_single();
            }else{
                $html=self::get_edit_field_single($value);
            }
            
            
                return $html;
                

	}
        
        function get_edit_field_single($input_val=false){
            
		$field_name = oqp_get_form_field_input_name();
                
                if($this->multiple_values) {
                    $field_name.='[]';
                }
            
		$html.="\t".'<input type="text" name="'.$field_name.'" value="'.$input_val.'"/>'."\n";
		return $html;
        }
        
        function validate_single_meta($meta){
            $value = trim($this->get_form_value());
            return $value;
        }
        
        function validate_postdata($metas){

            if($this->multiple_values) {
                foreach($metas as $meta){
                    $datas[]=self::validate_single_meta($meta);
                }
                $datas = array_filter($datas);
            }else{
                $datas=self::validate_single_meta($metas);
            }
            
            return $datas;

        }
	
	function save_postdata($metas) {

                //DELETE ALL
                delete_post_meta(get_the_ID(),$this->meta_key);

		//SAVE CUSTOM FIELDS
		if ($metas){
                    
                    $errors=false;
                    
                    if($this->multiple_values) {
                        foreach($metas as $meta){
                            if (!update_post_meta(get_the_ID(),$this->meta_key,$meta,false)) $errors = true;
                        }
                    }else{
                        if (!update_post_meta(get_the_ID(),$this->meta_key,$metas,true)) $errors = true;
                    }
                    
                    return (!$errors);

		}else{//deletion
                    return true;
                }

	}
	
	function populate_postdata() {
		$this->value=get_post_meta(get_the_ID(),$this->meta_key,!$multiple_values);
	}

}
class OQP_Form_Step_Field_Upload extends OQP_Form_Step_Field {
	var $max_files=5;
	var $type='image';
        var $mimes;

	function OQP_Form_Step_Field_Upload(){
		$this->slug='upload';
		$this->name=__('File upload','oqp');
		$this->cap_edit='upload_files';
                
                $mimes = get_allowed_mime_types();

                foreach ($mimes as $mime) {
                        $split=explode('/',$mime);
                        $mimetype = $split[0];
                        $mimeext = $split[1];
                        $this->mimes[$mimetype][]=$mimeext;
                }
                
                
		
	}
	
	function extra_admin_options() {
		?>
                <p>
                    <label><?php _e('Max. files','oqp');?></label>
                    <input<?php echo $atts;?> name="<?php echo oqp_get_form_field_input_name();?>[max_files]" type="text" size="1" value="<?php echo $this->max_files;?>"/>
		
                </p>
                
                <p>
                    <label><?php _e('Mime Type','oqp');?></label>
                    <?php



                    foreach ($this->mimes as $mimetype=>$ext) {
                            $atts=false;
                            if ($this->type) {
                                    if ($this->type==$mimetype) $atts.=" CHECKED";
                            }elseif($mimetype=='image') {
                                    $atts.=" CHECKED";
                            }


                            echo'<input type="radio" name="'.oqp_get_form_field_input_name().'[type]" value="'.$mimetype.'"'.$atts.'/>'.$mimetype.' ';
                    }

                    $message = sprintf(__('Get more informations about %s.','oqp'),'<a href="http://fr.wikipedia.org/wiki/Type_MIME" target="_blank">'.__('Mime Types','oqp').'</a>');

                    oqp_form_balloon_info($message);


                    if ((!current_theme_supports( 'post-thumbnails' )) && ($this->type=='image')) {
                            $warning = sprintf(__("Your current theme currently do not supports the 'post-thumbnails' feature.  Please refer to %s.","oqp"),'<a href="http://codex.wordpress.org/Post_Thumbnails" target="_blank">The Codex</a>');
                            oqp_form_balloon_warning($warning);
                    }

                    ?>
                </p>
                <?php

	}
	
	function extra_admin_options_save($options) {
                //TO FIX TO CHECK
		//if (!$options['type'])return false;
		//if (!$options['max_files'])return false;
		return $options;
	}
	
	///FRONTEND
	function get_edit_field() {
		global $oqp_form;

		$html.='<input type="hidden" name="'.oqp_get_form_field_input_name().'">';
		
		$files_slots = $this->max_files;
		
		$files_count = oqp_upload_get_total_files_count(get_the_ID(),$this->type);
		
		if ($files_count) {
			//display already uploaded files
			$html.=$this->get_field();
		}
		
		$files_slots_free = $files_slots-$files_count;
                
                $allowed_extensions = $this->mimes[$this->type];
                $allowed_extensions_str = sprintf(__('Allowed extensions: %s','oqp'),'<em>*.'.implode(' ,*.',$allowed_extensions).'</em>');
                
                $html.='<p>'.$allowed_extensions_str.'</p>';
		
		for ($i=1; $i<=$files_slots_free; $i++) {
			$html.='<input type="file" name="'.oqp_get_form_field_input_name().'[]"><br/>';

		}
		return $html;

	}
	function get_field() {
		$filesblock = oqp_get_files_block(get_the_ID(),$this->type);
		return $filesblock;
	}
	
	function populate_postdata(){
		$this->value = oqp_upload_get_files();
	}
	
	function get_form_value(){
		global $oqp_form;

		$files = new UploadedFiles($_FILES); //re-order files array
		$_FILES=array();//reset old array
                
		foreach($files[oqp_get_form_input_name()]['steps'][$oqp_form->current_step]['fields'][$oqp_form->step->current_field] as $file) {
			if (!$file['name'])continue;
			$_FILES[]=$file;
		}
		return $_FILES;
		
	}
        
        function validate_postdata($files){

            foreach ($files as $file_id=>$file) {
                
                //check type
                $file_type_arr=explode('/',$file['type']);
                $file_type=$file_type_arr[0];
                if($file_type!=$this->type){
                        $this->add_field_notice('message','upload_type_error',$file['name']);
                        unset($files[$file_id]);
                }
            }
            
            return $files;
            
            
        }
	
	function save_postdata($files) {
		global $oqp_form;

		if (!$files) {
                    return true; //TO FIX WE SHOULD DELETE EXISTING ONES ?
		}else {

                    if (($this->type=='image') && (current_theme_supports( 'post-thumbnails' ))) {

                            //TO FIX we shouldn't verify this, but it broke in BP
                            if (function_exists('has_post_thumbnail')) {
                                    $has_thumb = has_post_thumbnail(get_the_ID());
                            }

                            if (!$has_thumb)
                                    $setthumb=true;
                    }


                    foreach ($files as $file_id=>$file) {
                            if (empty($file['name'])) continue;

                            if (!$setthumb)
                                    $setthumb=false;

                            $newupload = oqp_insert_attachment($file_id,get_the_ID(),$this->type,$setthumb);


                            if (($newupload->errors) || (!$newupload)) {
                                    $errors=true;
                            }
                    }

                    if ($errors) {
                            //TO FIX BETTER ERRORS HANDLING
                            $this->add_field_notice('error','upload_error');
                            return false;
                    }
                    return true;
		}

	}
	
	
}




//FIELD TYPES
oqp_register_field_type( 'OQP_Form_Step_Field_Title' );
oqp_register_field_type( 'OQP_Form_Step_Field_Section' );
oqp_register_field_type( 'OQP_Form_Step_Field_Excerpt' );
oqp_register_field_type( 'OQP_Form_Step_Field_Taxonomy' );
oqp_register_field_type( 'OQP_Form_Step_Field_Custom' );
oqp_register_field_type( 'OQP_Form_Step_Field_Upload' );





?>
