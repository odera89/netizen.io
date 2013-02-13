<?php


//available extensions (to build a list in the plugin's options)
function oqp_get_avalaible_extensions(){
    /*
    $extensions['guest'] = array(
        'name'=>__('Guest posting','oqp'),
        'description'=>__('Allows non-logged users to post using OQP','oqp'),
        'class'=>'Oqp_Extension_Guest',
        'prize'=>__("Free","oqp").'!'
        
    );
     */ 

    $extensions['geo'] = array(
        'name'=>__('Geo-location','oqp'),
        'description'=>__('Allows you to attach a location to a post and to search posts by location','oqp'),
        'class'=>'Oqp_Extension_Geo',
        'prize'=>'25$'
    );
    $extensions['freshness'] = array(
        'name'=>__('Freshness','oqp'),
        'description'=>__('Gives an extra class to new posts, using a cookie (visitors) or checking the user last visit.','oqp'),
        'class'=>'Oqp_Extension_Freshness',
        'prize'=>'5$'
    );
    $extensions['expiration'] = array(
        'name'=>__('Expiration','oqp'),
        'description'=>__('Makes posts expire after a given delay.','oqp'),
        'class'=>'Oqp_Extension_Expiration',
        'prize'=>'5$'
    );
    $extensions['terms'] = array(
        'name'=>__('Terms & Conditions','oqp'),
        'description'=>__('You can add a "term & conditions" page to your form; that the user has to check before being able to post with OQP.','oqp'),
        'class'=>'Oqp_Extension_Terms',
        'prize'=>'3$'
    );
    
    $extensions['qsubscribe'] = array(
        'name'=>__('Query Subscribe','query-subscribe'),
        'description'=>__('Allow the user to subscribe to searches, authors & individual posts, with email notifications.','oqp'),
        'class'=>'Oqp_Extension_QSubscribe',
        'prize'=>'25$'
    );

    
    return apply_filters("oqp_get_avalaible_extensions",$extensions);
}

function oqp_extension_exists($slug){
    
    //if(!is_string($slug))return false;//extension already populated

    $extensions = oqp_get_avalaible_extensions();

    $extension = $extensions[$slug];
    if(!$extension)return false;
    $filename = OQP_PLUGIN_DIR . 'includes/extensions/'.$slug.'.php';
    if(!file_exists($filename))return false;
    return $extension;
}


//load the extensions
function oqp_populate_extensions($enabled_exts){
    //populate classes
    foreach ((array)$enabled_exts as $ext) {
        $extension = oqp_extension_populate($ext);
        
        if(!$extension)continue;        

        if($extension->init()){
            $has_init[] = $ext;
            $extensions[$ext]=$extension;
            do_action('oqp_extension_'.$ext.'_init');
        }
    
    }
    
    oqp_debug($has_init,"extensions init","warn");


    $extensions = array_filter((array)$extensions);
    
    if(!$extensions) return false;
    
    oqp_debug($extensions,'oqp_populate_extensions');

    return $extensions;


}


function oqp_load_extensions(){
    global $oqp_extensions;
    
    $extensions = oqp_get_avalaible_extensions();
    
    
    foreach($extensions as $slug=>$extension){
        
        $filename = OQP_PLUGIN_DIR . 'includes/extensions/'.$slug.'.php';
        if(!file_exists($filename))continue;
        require_once($filename);
        
        $classname=$extension['class'];
        if(!class_exists($classname))continue;
        $oqp_extensions[$slug]=new $classname($slug);
        
        
        $loaded[]=$slug;
    }
    oqp_debug($loaded,'oqp_load_extensions');
}



function oqp_extension_populate($slug){
    global $oqp_extensions;

    $ext = $oqp_extensions[$slug];

    if(!$ext)return false;

    return $ext;

}



function oqp_get_form_extension_input_name($slug){
    return oqp_get_form_input_name().'[extensions]['.$slug.']';
}

function oqp_admin_list_extensions(){
    $oqp_extensions = oqp_get_avalaible_extensions();

    reset($oqp_extensions);

    foreach($oqp_extensions as $slug=>$extension){

            $is_disabled = (!oqp_extension_exists($slug));
            
            $class_str='';

            if($is_disabled)$class_str=' class="disabled"';
        ?>
                <p<?php echo $class_str;?>>
                    <label for="<?php echo oqp_get_form_extension_input_name($slug);?>[enabled]"><?php echo $extension['name'];?></label>
                    <input type="checkbox" name="<?php echo oqp_get_form_extension_input_name($slug);?>[enabled]"<?php checked(!$is_disabled);?><?php disabled(true);?>/">
                    <?php
                    echo $extension["description"];
                    echo'<span class="price">- '.$extension['prize'].'</span>';
                    ?>
                </p>
        <?php
        
        
    }
}



//base class that the plugins can use to extend
class Oqp_Extension{
        var $slug;
	var $name;
        var $description;
        var $prize;
	function __construct($slug){
            
            $this->slug=$slug;

            $extensions=oqp_get_avalaible_extensions();
            $extension=$extensions[$slug];
            
            $this->name=$extension['name'];
            $this->description=$extension['description'];
            $this->prize=$extension['prize'];
	}

        
        function init(){
            //should be overidden
            //must return bool
            

            
            return true;
        }
	
	///ADMIN PLUGIN OPTIONS///

	function get_default_settings($options){
		if(!$this->default_options) return $options;
		return array_merge($options,$this->default_options);
	}
        
        function get_options($name=false){
            global $oqp_form;
            $options = get_post_meta($oqp_form->page_id,'oqp_extension_'.$this->slug,true);

            if($name){ //single option
                if(isset($options[$name])) return $options[$name];
                return $this->default_options[$name];
            }else{
                if($options) return $options;
                return $this->default_options;
            }
        }

        function admin_form_options(){
            /*extension options. Must be overriden in the subclass*/
        }
        
        function admin_form_options_validate($options){
            /*extension options. Must be overriden in the subclass*/
            return $options;
        }
        
        function admin_add_notice($setting,$message,$type='error'){
            add_settings_error('oqp_edit_form',$setting,$message,$type);
        }

	
	function section_text() {

	}
	
	///ADMIN FORM OPTIONS///

	function admin_form_validate($options){
            return $options;
        }
        
	function validate_options_form($options){
                die("validate_options_form");
	}
        
        function admin_field_basename(){
            return oqp_get_form_input_name()."[extensions][".$this->slug."]";
        }
        
        function admin_enabled_field(){
            
            $is_checked = oqp_extension_is_enabled($this->slug);

            ?>
            <input class="extension_enabled" type="checkbox" <?php echo checked(($is_checked), true, false );?> name="oqp_form[extensions][<?php echo $this->slug;?>][enabled]">
            <?php
        }
        
        function save_options($options){
            global $oqp_form;
            
            unset($options['enabled']);

            if($options){

                return update_post_meta($oqp_form->page_id,'oqp_extension_'.$this->slug,$options);
            }else{
                return self::delete_options();
            }
        }
        
        function delete_options(){
            return delete_post_meta($oqp_form->page_id,'oqp_extension_'.$this->slug);
        }
        

}



function oqp_extensions_register_admin_form_hook(){
    do_action('oqp_admin_form_extensions');
}
function oqp_extensions_register_admin_options_hook(){
    do_action('oqp_admin_options_extensions');
}

function oqp_admin_form_validate_extensions($options){
    global $oqp_form;

    if(!$options['extensions'])return $options;

    //SAVE EXTENSION INFOS
    foreach((array)$options['extensions'] as $slug=>$ext_options){

        $extension = oqp_extension_populate($slug);

        //validate single extension

        $ext_options = $extension->admin_form_options_validate($ext_options);
        $options['extensions'][$slug]=$ext_options;

        //save single extension
        $extension->save_options($ext_options);

    }

    //add extensions slugs to form options
    foreach((array)$options['extensions'] as $slug=>$ext_options){
        if(!$ext_options['enabled'])continue;
        $ext_enabled[]=$slug;
    }
    
    $options['extensions']=$ext_enabled;

    return $options;

}



function oqp_admin_settings_extensions_section_tab(){
    ?>
    <li><a href="#oqp-extensions"><?php _e('Extensions','oqp');?></a></li>
    <?php
}

function oqp_admin_settings_extensions_section(){
    ?>
    <div id="oqp-extensions" class="oqp-settings-tab">
        <h3><?php _e('Available extensions','oqp');?></h3>
        <?php oqp_admin_list_extensions();?>
        <?php do_action("oqp_admin_settings_extensions");?>
    </div>
    <?php
}

function oqp_admin_form_extensions_section_tab(){
    ?>
    <li><a href="#oqp-form-extensions"><?php _e('Form Extensions','oqp');?></a></li>
    <?php
}

function oqp_extension_is_enabled($slug){
    global $oqp_form;
    
    if(!$oqp_form->extensions_slugs)return false;

    if(in_array($slug,$oqp_form->extensions_slugs))return true;
}





function oqp_admin_form_extensions_section(){
    global $oqp_form;
    global $oqp_extensions;
    $extensions = oqp_get_avalaible_extensions();

    
    ?>
    <div id="oqp-form-extensions">
        <h3><?php _e("Form extensions","oqp");?></h3>
        <?php

            foreach($extensions as $slug=>$ext){
                $extension = oqp_extension_populate($slug);
                if(!$extension)continue;
                ?>
                <div class="oqp-extension" id="oqp-extension-<?php echo $slug;?>">
                    <h4><?php $extension->admin_enabled_field();?><?php echo $extension->name;?><span class="desc"><?php echo $extension->description;?></span></h4>
                    <?php 
                    //extension not setup
                    if(!$oqp_extensions[$slug]->init()){
                        $message = __("This extension will not be loaded as the required informations are missing","oqp");
                        oqp_form_balloon_warning($message);
                    }
                    
                    //if(oqp_extension_is_enabled($slug)){
                        ?>
                        <div class="extension-options">
                            <?php $extension->admin_form_options();?>
                        </div>
                        <?php
                    //}
                    
                    ?>
                </div>
                <?php
            }

        ?>
    </div>
    <?php
}


//ADMIN

//add hook "oqp_admin_options_extensions" to hook form fields & sections
add_action('oqp_admin_options','oqp_extensions_register_admin_options_hook');

//add hook "oqp_admin_form_extensions" to hook form fields & sections
add_action('oqp_admin_form','oqp_extensions_register_admin_form_hook');


//add extensions section for plugin settings
add_action('oqp_admin_settings_sections_tabs','oqp_admin_settings_extensions_section_tab');
add_action('oqp_admin_settings_sections','oqp_admin_settings_extensions_section');

//add extensions section for form settings
add_action('oqp_admin_form_sections_tabs','oqp_admin_form_extensions_section_tab');
add_action('oqp_admin_form_sections','oqp_admin_form_extensions_section');

//remove extensions from main options & add hook
add_filter('oqp_admin_form_validate','oqp_admin_form_validate_extensions');

//load extensions
add_action('init','oqp_load_extensions');


?>