<?php
function oqp_settings_page(){
    if ( isset( $_POST['oqp-new-form'] ) ) {
            check_admin_referer( 'oqp-new-form' );
            $page_id=$_POST['oqp_form']['new_page_id'];
            $post_type=$_POST['oqp_form']['post_type'];
            $success = oqp_admin_create_form($page_id,$post_type);
            
            if($success){

                $redirect_url= admin_url('admin.php?page=oqp-form-'.$page_id);
                $redirect_url = add_query_arg(array("settings-updated"=>true),$redirect_url);
                wp_redirect( $redirect_url );die();
            }
            
            
    }elseif ( isset( $_POST['oqp-save-settings'] ) ) {//update extenions
        if($_POST["oqp_form"]["debug"]){
            $options["debug"]=true;
            $oqp_options['debug']=true;
        }
        if($_POST["oqp_form"]["reset_options"]){
            delete_option('oqp_options');
        }
        if($_POST["oqp_form"]["delete_forms"]){
            $page_ids=oqp_get_forms_page_ids();
            foreach($page_ids as $page_id) {
                    $oqp_form = new Oqp_Form($page_id);

                    if (!$oqp_form->delete_options()) $errors=true;
            }
            $success=(!$errors);
        }
        

        $success=update_option("oqp_options",$oqp_options);

    }
    

    ////MESSAGES///
    
    if($success){
        ?>
    <div id="message" class="updated">
        <p><strong><?php _e('Settings saved.','oqp') ?></strong></p>
    </div>
        <?php
    }else{
        settings_errors( 'oqp_settings' );
        settings_errors( 'oqp_new_form' );
    }

    ?>
    <div class="wrap">
        <div id="main">
            <h2>One Quick Post</h2>
            <div id="oqp-admin-header"><?php oqp_admin_header();?></div>
            
            <div id="oqp-admin-tabs">
                    <ul>
                            
                            <li><a href="#oqp-settings"><?php _e('Plugin settings','oqp');?></a></li>
                            <li><a href="#oqp-new-form"><?php _e('New form','oqp');?></a></li>
                            <?php do_action('oqp_admin_settings_sections_tabs');?>
                    </ul>
                    <div id="oqp-settings" class="oqp-settings-tab">
                        <h3><?php _e('One Quick Post settings','oqp');?></h3>
                        <form method="post" action="<?php echo admin_url("admin.php?page=oqp-settings");?>#oqp-settings">
                            <h4><?php _e("System","oqp");?></h4>
                                <p>
                                    <?php oqp_admin_forms_debug_field();?>
                                </p>
                            
                                <p>
                                    <?php oqp_admin_forms_reset_field();?>
                                </p>
                                
                                <p>
                                    <?php oqp_admin_forms_delete_field();?>
                                </p>
                                
                                
                                <div class="actions-link">
                                    <?php wp_nonce_field('oqp-save-settings'); ?>
                                    <input type="submit" value="Save Changes" name="oqp-save-settings"/>
                                </div>
                        </form>
                    </div>
                    <div id="oqp-new-form" class="oqp-settings-tab">
                        <h3><?php _e('Create a new form','oqp');?></h3>
                        <form method="post" action="<?php echo admin_url("admin.php?page=oqp-settings");?>#oqp-new-form">

                                <?php 
                                _e("To install a new OQP form, create a new blank page.  Then, insert the ID of the new page in the field below & select the type of post thie form will be related to.",'oqp');
                                ?>
                                <?php oqp_admin_new_form_field_page_id();?>
                                <?php oqp_admin_new_form_field_post_type();?>

                                <div class="actions-link">
                                    <?php wp_nonce_field('oqp-new-form'); ?>
                                    <input type="submit" value="Save Changes" name="oqp-new-form"/>
                                </div>
                        </form>
                    </div>
                    <?php do_action('oqp_admin_settings_sections');?>
            </div>
            

            
        </div>
    </div>
    <?php
}

function oqp_admin_forms_debug_field(){
    if (class_exists('FirePHP'))$firephp=true;
    if(!$firephp)$atts.=' disabled="disabled"';
    if(oqp_get_option("debug"))$atts.=' checked="checked"';

?>
        <label for="<?php echo oqp_get_form_input_name();?>[debug]"><?php _e("Debug Mode","oqp");?></label>
	<input<?php echo $atts;?> type="checkbox" name="<?php echo oqp_get_form_input_name();?>[debug]">
<?php
    _e('With debug mode enabled, debug information will be outputted in the firebug console.','oqp');
    if(!$firephp){
        echo"<br/>";
        printf(__("You must have firePHP enabled to enable debug mode. Maybe you could install %s !","oqp"),'<a target="_blank" href="http://http://wordpress.org/extend/plugins/simple-wp-firephp/">Simple WP FirePHP</a>');
    }
}

function oqp_admin_forms_reset_field() {
?>
        <label for="<?php echo oqp_get_form_input_name();?>[reset_options]"><?php _e("Reset plugin's options","oqp");?></label>
	<input type="checkbox" name="<?php echo oqp_get_form_input_name();?>[reset_options]">
<?php
}


function oqp_admin_forms_delete_field() {
?>
        <label for="<?php echo oqp_get_form_input_name();?>[delete_forms]"><?php _e("Delete forms","oqp");?></label>
	<input type="checkbox" name="<?php echo oqp_get_form_input_name();?>[delete_forms]">
<?php
}

function oqp_admin_create_form($page_id,$post_type) {
        //PAGE ID

        $page_id = intval($page_id);

        //must be int
        if(!is_int($page_id)){
                $message=__( 'Please enter a page ID for this form', 'oqp' );
                add_settings_error('oqp_new_form','page_id',$message,'error');
                return false;
        }


        //page do not exists
        if (!get_page( $page_id )){
                $message=sprintf(__('The page #%d do not exists', 'oqp' ),$page_id);
                add_settings_error('oqp_new_form','page_id',$message,'error');
                return false;
        }

        //page already has options
        $page_form_options = Oqp_Form::get_options($page_id);

        if ($page_form_options){
                $message=sprintf(__('The page #%d already has a form.', 'oqp' ),$page_id);
                add_settings_error('oqp_new_form','page_id',$message,'error');
                return false;
        }


        $form_options = Oqp_Form::get_default_form_settings($post_type);

        //blank form
        $oqp_form=new Oqp_Form();
        $oqp_form->page_id=$page_id;

        if (!$oqp_form->save_options($form_options)){
                $message=sprintf(__('There was an error while saving your form options.', 'oqp' ),$page_id);
                add_settings_error('oqp_new_form','page_id',$message,'error');
                return false;
        }
        
        return true;

}







function oqp_admin_new_form_field_page_id(){
    ?>
    <input size="10" name="<?php echo oqp_get_form_input_name();?>[new_page_id]" size="2" placeholder="<?php _e("Page ID","oqp");?>" type="text" value=""/>
    <input name="<?php echo oqp_get_form_input_name();?>[page_id]" size="2" type="hidden" value="new_form"/>
    <?php
}

function oqp_admin_get_valid_post_types(){
    $post_types = get_post_types(false, 'object' );
    foreach ($post_types as $slug=>$post_type) {
        if(!$post_type->publicly_queryable)unset($post_types[$slug]);
        if($post_type->name=='attachment')unset($post_types[$slug]);
    }
    
    return $post_types;
}

function oqp_admin_new_form_field_post_type(){
        global $oqp_form;

        $value = $oqp_form->post_type;

        if (!$value) $value='post';

        $post_types = oqp_admin_get_valid_post_types();
        ?>
        <select name='<?php echo oqp_get_form_input_name();?>[post_type]'>
            <?php
            foreach ($post_types as $slug=>$post_type) {
                    unset($selected);
                    if ($value==$slug) 
                            $selected=" SELECTED";
                    echo '<option'.$selected.' value="'.$slug.'">'.$post_type->labels->singular_name.'</option>';
            }
            ?>
        </select>
        <?php
}

function oqp_admin_form_validate_new_form($options) {
    
        if ($_POST['action']!='update') return false;

        extract($options);

        //PAGE ID

        $page_id = intval($new_page_id);

        //must be int
        if(!is_int($page_id)){
                $message=__( 'Please enter a page ID for this form', 'oqp' );
                add_settings_error('oqp_new_form','page_id',$message,'error');
                return false;
        }

        //page do not exists
        if (!get_page( $page_id )){
                $message=sprintf(__('The page #%d do not exists', 'oqp' ),$page_id);
                add_settings_error('oqp_new_form','page_id',$message,'error');
                return false;
        }

        //page already has options
        $page_form_options = Oqp_Form::get_options($page_id);

        if ($page_form_options){
                $message=sprintf(__('The page #%d already has a form.', 'oqp' ),$page_id);
                add_settings_error('oqp_new_form','page_id',$message,'error');
                return false;
        }

        $form_options = Oqp_Form::get_default_form_settings($post_type);

        //blank form
        $oqp_form=new Oqp_Form();
        $oqp_form->page_id=$page_id;

        if (!$oqp_form->save_options($form_options)){
                $message=sprintf(__('There was an error while saving your form options.', 'oqp' ),$page_id);
                add_settings_error('oqp_new_form','page_id',$message,'error');
                return false;
        }else{
            $url= admin_url(  'options-general.php?page='.OQP_SLUG);
            $args['form']=$page_id;
            $redirect_url = add_query_arg($args,$url);

            wp_redirect( $redirect_url );die();
        }

}

function oqp_admin_notices_action() {
    settings_errors( 'oqp_new_form' );
}
add_action( 'admin_notices', 'oqp_admin_notices_action' );


?>
