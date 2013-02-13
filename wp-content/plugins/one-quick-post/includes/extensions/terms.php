<?php
class Oqp_Extension_Terms extends Oqp_Extension{
	var $page_id;
        var $meta_key;

        
        function init(){
            if (!$this->page_id)return false;
            
            $this->page_id = $this->get_options('page_id');
            $this->meta_key = 'oqp_terms_accepted';

            //notices
            add_filter('oqp_notices_messages',array(&$this,'notices_messages'));

            //FORM
            //field
            add_action('oqp_form_after_fields',array(&$this,'terms_form_field'));
            //validation CHECK
            add_action('step_validation',array(&$this,'terms_validation'));

            do_action('oqp_extension_terms_init');
            
            return true;
        }
        
        function admin_form_options(){
            //function called by oqp_admin_form_extensions_section to load the extension settings.
            ?>
            <p>
                <label for="<?php echo $this->admin_field_basename();?>[page_id]"><?php _e('Page ID','oqp');?></label>
                <?php $this->admin_field_page_id();?>
            </p>
            <?php
        }
        
	function admin_field_page_id(){
                global $oqp_form;
		//TO FIX display capabilities enabled for guest
		?>
		<input size="2" type="text" name="<?php echo $this->admin_field_basename();?>[page_id]" value="<?php echo $this->page_id;?>"/>

		<?php
		$messages[]=__('ID of the Terms & Conditions page.','oqp');
		oqp_form_balloon_info($messages);    
	}
        
        function admin_form_options_validate($options){
            if (!$options['page_id'])return $options;
            
            $page = get_page( $options['page_id'] );

            if (!$page->ID) {
                    $message=sprintf(__('The page #%d do not exists', 'oqp' ),$options['page_id']);
                    self::admin_add_notice('page_id',$message);
                    unset($options['page_id']);
            }

            return $options;
        }
        
	//////////////////////////
        
        function notices_messages($messages){
            $messages['terms_ignored']=__("Please accept our Terms & Conditions","oqp");
            
            return $messages;
        }
        
	function terms_form_field(){
		global $oqp_form,$post;
		
		$accepted = (bool)get_post_meta($post->ID,$this->meta_key,true);//db value
                
                $classes[]='terms-conditions';
                if($accepted)$classes[]='accepted';

                $link = get_permalink( $this->page_id );
                $html_link='<a target="_blank" href="'.$link.'">'.__('Terms & Conditions','oqp').'</a>';?>
                <p class="<?php echo implode(' ',$classes);?>">
                        <input type="checkbox" name="<?php echo $this->meta_key;?>" value="1" <?php checked($accepted);?>/><?php printf(__('I agree with the %s','oqp'),$html_link);?>

                </p>
		<?php

	}
	
	
	function terms_validation(){
		global $oqp_form,$post;

                if (!$_POST[$this->meta_key]){//form value
                        $oqp_form->notices->error('oqp_step','terms_ignored');
                        $accepted=false;
                        echo"validation error no terms";
                }else{
                    $accepted=true;
                }
                
                update_post_meta($post->ID,$this->meta_key,$accepted);
	}
	
}

?>