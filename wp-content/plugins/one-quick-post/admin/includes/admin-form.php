<?php

function oqp_admin_load_form(){
    global $oqp_form;

    $oqp_form_id=oqp_admin_get_edit_form();
    $oqp_form=new Oqp_Form($oqp_form_id);
    
    
    //populate steps, extensions, ...
    do_action('oqp_init');
    
    if(!$oqp_form->steps){
        unset($oqp_form);
        return $query;
    }

    do_action('oqp_has_init');

}

function oqp_admin_form_settings(){
    global $oqp_form;

    oqp_admin_load_form();

    if ($oqp_form)$page = get_post($oqp_form->page_id);
    

    
    if ( isset( $_POST['oqp-edit-form'] ) ) {
            check_admin_referer('oqp-edit-form-'.$oqp_form->page_id);
            
            $form_options=$_POST['oqp_form'];
            
            //DELETE FORM

            if($form_options['delete_form']){
                if (!$oqp_form->delete_options($options)){
                        $message=sprintf(__('There was an error while deleting your form.', 'oqp' ),$page_id);
                        add_settings_error('oqp_edit_form','page_id',$message,'error');
                }else{
                    $url= admin_url(  'options-general.php?page='.OQP_SLUG);
                    $args['settings-updated']=true;
                    $redirect_url= admin_url('admin.php?page=oqp-settings');
                    
                    wp_redirect( $redirect_url );die();
                }
                return false;
            }
            
            //RESET FORM

            if($form_options['reset_form']){
                $form_options = Oqp_Form::get_default_form_settings($oqp_form->post_type);
                if (!$oqp_form->save_options($form_options)){
                        $message=sprintf(__('There was an error while resetting your form options.', 'oqp' ),$page_id);
                        add_settings_error('oqp_edit_form','page_id',$message,'error');
                        return false;
                }else{
                    $args['settings-updated']=true;
                    $redirect_url= admin_url('admin.php?page=oqp-form-'.$oqp_form->page_id);
                    wp_redirect( $redirect_url );die();
                }
            }
            
            
            //SAVE FORM
            
            $success = oqp_admin_form_validate($form_options);
            
            
            //RELOAD FORM
            oqp_admin_load_form();
                

    }
    
    ////MESSAGES///
    $notices = get_settings_errors('oqp_edit_form');
    if(!$notices){
        ?>
    <div id="message" class="updated">
        <p><strong><?php _e('Form Settings updated.','oqp') ?></strong></p>
    </div>
        <?php
    }
    
    settings_errors( 'oqp_edit_form' );

    ?>
    <div class="wrap">
        <div id="main">
            <div id="oqp-admin-header"><?php oqp_admin_header();?></div>
            <h2><?php echo $page->post_title;?><a class="add-new-h2" target="_blank" href="<?php echo get_permalink($page->ID);?>"><?php _e("View page","oqp");?></a></h2>
            <form method="post" action="<?php echo admin_url("admin.php?page=oqp-form-".$oqp_form->page_id);?>#oqp-form-fields" id="oqp-admin-form">
                <div id="oqp-admin-tabs">
                        <ul>
                                <li><a href="#oqp-form-base"><?php _e('Form base','oqp');?></a></li>
                                <li><a href="#oqp-form-fields"><?php _e('Form fields','oqp');?></a></li>
                                <?php do_action('oqp_admin_form_sections_tabs');?>
                        </ul>
                        <div id="oqp-form-base">
                            <h3><?php _e("Form base","oqp");?></h3>
                            <p>
                                <label><?php _e("Page ID",'oqp');?></label>
                                <?php oqp_admin_form_page_id_field();?>

                            </p>
                            <p>
                                <label><?php _e("Post type",'oqp');?></label>
                                <?php oqp_admin_form_field_post_type();?>
                            </p>
                            <p>
                                <label><?php _e("Query",'oqp');?></label>
                                <?php oqp_admin_form_field_query();?>
                            </p>
                            <p>
                                <label><?php _e("Templates",'oqp');?></label>
                                <?php oqp_admin_form_templates();?>
                            </p>
                        </div>
                        <div id="oqp-form-fields">
                            <h3><?php _e("Form fields","oqp");?></h3>




                                <div id="poststuff" class="metabox-holder">
                                    <?php
                                    
                                    $oqp_form->rewind_steps();

                                    for ($i = 0; $i < count($oqp_form->steps); $i++) {
                                        $oqp_form->the_step();
                                        $step=$oqp_form->step;
                                        
            

                                        $step_idunique='oqp-step-'.$i;
                                        
                                        $stepname=$step->get_step_name();
                                        if(!$stepname) $stepname=sprintf(__("Step #%d","oqp"),$i+1);
                                        
                                        if($step->is_required())
                                            $stepname.='*';
                                        $step_title='<em>'.$stepname.'</em> <code>'.$step->slug.'</code>';
                                        add_meta_box($step_idunique,$step_title,'oqp_admin_form_step_box', 'oqp', 'steps', 'core',$oqp_form->current_step);

                                    }

                                    do_meta_boxes( 'oqp','steps',$step);

                                    
                                    ?>
                                </div>
                                <div>
                                    <h4><?php _e('Add a new field','oqp');?></h4>
                                    <?php oqp_admin_form_new_field();?>
                                </div>

                        </div>
                        <?php do_action('oqp_admin_form_sections');?>
                </div>
                <div class="actions-link">
                    <?php wp_nonce_field('oqp-edit-form-'.$oqp_form->page_id); ?>
                    <p>
                        <label><?php _e("Delete Form",'oqp');?></label>
                        <input type="checkbox" name="<?php echo oqp_get_form_input_name();?>[delete_form]"/>
                    </p>
                    <p>
                        <label><?php _e("Reset Form",'oqp');?></label>
                        <input type="checkbox" name="<?php echo oqp_get_form_input_name();?>[reset_form]"/>
                    </p>
                    <input type="submit" value="Save Changes" name="oqp-edit-form">
                    <input type="hidden" value="<?php echo $oqp_form->page_id;?>" name="<?php echo oqp_get_form_input_name();?>[page_id]">
                    <input type="hidden" value="<?php echo $oqp_form->post_type;?>" name="<?php echo oqp_get_form_input_name();?>[post_type]">
                </div>
            </form>
        </div>
    </div>
   <?php

}


function oqp_admin_form_page_id_field(){
    global $oqp_form;

    ?>
    <input name="<?php echo oqp_get_form_input_name();?>[new_page_id]" size="2" type="text" value="<?php echo $oqp_form->page_id;?>"<?php disabled(!empty($oqp_form->page_id));?>/>
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

function oqp_admin_form_field_post_type(){
        global $oqp_form;

        $value = $oqp_form->post_type;

        if (!$value) $value='post';

        $post_types = oqp_admin_get_valid_post_types();
        ?>
        <select <?php disabled(!empty($value));?> name='<?php echo oqp_get_form_input_name();?>[post_type]'>
            <?php
            foreach ($post_types as $slug=>$post_type) {
            
                    echo '<option value="'.$slug.'"'.selected($value,$slug,false).'>'.$post_type->labels->singular_name.'</option>';
            }
            ?>
        </select>
        <input name="<?php echo oqp_get_form_input_name();?>[post_type]" type="hidden" value=""/>
        <?php
}

function oqp_admin_form_field_query(){
        global $oqp_form;

        $value=$oqp_form->query_args;
        
        if(is_array($value))$value=http_build_query($value);//TO FIX URGENT SHOULD BE http_build_str

        ?>
        <code>post_type=<?php echo $oqp_form->post_type;?>&</code>
        <input name="<?php echo oqp_get_form_input_name();?>[query_args]" size="50" type="text" value="<?php echo $value;?>"/>
        <?php
        printf(__("See the %s for more information about query parameters.","oqp"),'<a target="_blank" href="http://codex.wordpress.org/Class_Reference/WP_Query#Parameters">Wordpress Codex</a>');
}

function oqp_admin_form_templates(){
        global $oqp_form;
        ?>
        <input type="checkbox" <?php checked(isset($oqp_form->templates['archives']));?> name="<?php echo oqp_get_form_input_name();?>[templates][archives]"/>
        <?php _e("Load archives template when this query is detected","oqp");?>
        <input type="checkbox" <?php checked(isset($oqp_form->templates['singular']));?> name="<?php echo oqp_get_form_input_name();?>[templates][singular]"/>
        <?php _e("Load single template when a post matches this query","oqp");?>
        <?php
}

function oqp_admin_form_validate($options) {
        global $oqp_form;
        
    
        //new-field
        if($options['new-field']['model']){
            
            $new_field['new']=true;
            $new_field['model']=$options['new-field']['model'];
            
            
            $new_field_step_id=$options['new-field']['step_id'];
            $new_field_step=$options['steps'][$new_field_step_id];
            $new_field_id=count($new_field_step['fields']);

            $options['steps'][$new_field_step_id]['fields'][$new_field_id]=$new_field;
        }
        unset($options['new-field']);


        //TO FIX TO MOVE
	//hook for extensions & stuff
        $options = apply_filters("oqp_admin_form_validate",$options);


        if (!$oqp_form->save_options($options)){

                $message=sprintf(__('There was an error while saving your form options.', 'oqp' ),$oqp_form->page_id);
                add_settings_error('oqp_edit_form','page_id',$message,'error');
                return false;
        }
        

        
        return true;

}




function oqp_admin_form_step_box($post_id,$box_infos){
    global $oqp_form;
    
    $step_id=$box_infos['args'];
    $step = $oqp_form->the_step($step_id);
    $stepname = $step->get_step_name();
    if(!$stepname) $stepname=sprintf(__("Step #%d","oqp"),$step_id+1);
    ?>
    <div class="step-settings">
        <div class="infos">
            <p>
                <label for="<?php echo oqp_get_form_step_input_name();?>[name]"><?php _e('Name','oqp');?></label>
                <input type="text" value="<?php echo $stepname;?>" name="<?php echo oqp_get_form_step_input_name();?>[name]"/>
            </p>
        </div>
        <div>
        <?php

        if ( $step->have_fields() ) : while ( $step->have_fields() ) : $step->the_field();
            $field = $step->field;
            ///oqp_admin_form_field();
            $step_idunique='oqp-step-'.$step->slug;
            $field_idunique=$step_idunique.'-field-'.$step->current_field;

            $fieldname=$field->get_label();
            if($field->is_required())
                $fieldname.='*';
            
            $field_title='<em>'.$fieldname.'</em>';
            add_meta_box($field_idunique,$field_title,'oqp_admin_form_field', 'oqp',$step_idunique, 'core',array('field_id'=>$step->current_field,'step_id'=>$oqp_form->current_step));

        endwhile;endif;

        do_meta_boxes( 'oqp',$step_idunique,$field);
        ?>
        </div>
    </div>
<?php
}



function oqp_admin_form_field($post_id,$box_infos){
    global $oqp_form;
    
    $field_id=$box_infos['args']['field_id'];
    $step_id=$box_infos['args']['step_id'];

    $step=$oqp_form->the_step($step_id);
    $field=$step->the_field($field_id);
    
    ?>
    <div class="field-settings">
        <div>
            <p>
                <label for="<?php echo oqp_get_form_field_input_name();?>[name]"><?php _e('Name','oqp');?></label>
                <input type="text" value="<?php echo $field->get_label();?>" name="<?php echo oqp_get_form_field_input_name();?>[name]"/>
                <input type="hidden" name="<?php echo oqp_get_form_field_input_name();?>[model]" value="<?php echo $field->model;?>"/>
            </p>
            <p>
                <label for="<?php echo oqp_get_form_field_input_name();?>[required]"><?php _e('Required','oqp');?></label>
                <input type="checkbox" <?php checked(($field->is_required()));disabled(($field->model=='title'));?> name="<?php echo oqp_get_form_field_input_name();?>[required]"/>
            </p>
            <p>
                <label><?php _e('Capability required','oqp');?></label>
                <span><?php _e('Edit','oqp');?><code><?php echo $field->get_cap_edit();?></code></span> 
                <span><?php _e('View','oqp');?><code><?php echo $field->get_cap_view();?></code></span>
            </p>
        </div>
        <?php if (method_exists($field,'extra_admin_options')){
            ?>
            <div class="advanced">
                <?php
                    $field->extra_admin_options();
                ?>
            </div>
            <?php
        }
          ?>
            <p>
                <label for="<?php echo oqp_get_form_field_input_name();?>[delete]"><?php _e('Delete','oqp');?></label>
                <input type="checkbox" name="<?php echo oqp_get_form_field_input_name();?>[delete-field]"/>
                
            </p>
    </div>
    <?php

}


?>
