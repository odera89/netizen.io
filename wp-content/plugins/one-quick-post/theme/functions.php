<?php



function oqp_single_post_extra_tab_author(){
    global $oqp_form;
    
    if(!oqp_is_single()) return false;

    $step_author_args=array(
            'name'=>__('Author','oqp'),
            'slug'=>_x('author','slug','oqp'),
            'template'=>oqp_locate_theme_template('single/tab-author.php')
    );

    
    $oqp_form->steps[]=new Oqp_Form_Step($step_author_args);

}

function oqp_single_post_extra_tab_comments(){
    global $oqp_form;

    if($oqp_form->action==_x('create','slug','oqp'))return false;
    if (!comments_open($oqp_form->loaded_post->ID)) return false;

    $step_comments_args=array(
            'name'=> sprintf( __( 'Comments <span class="count">%d</span>', 'oqp' ), get_comments_number($oqp_form->loaded_post->ID)),
            'slug'=>_x('comments','slug','oqp'),
            'template'=>oqp_locate_theme_template('single/tab-comments.php')
    );
    $oqp_form->steps[]=new Oqp_Form_Step($step_comments_args);

}

function oqp_visit_count() {

	if (oqp_get_post_visit_count()) {?>
		<span class="oqp-visit-count">
			<?php printf(__( '%d views', 'oqp' ),oqp_get_post_visit_count()); ?>								
		</span>
	<?php 
        
        }
}

function oqp_single_post_author_sidebar($step_html){
	global $wp_query;
        global $oqp_form;
        
        if(!oqp_is_single()) return false;
        
        setup_requested_step();

	$is_step_author=($oqp_form->step->slug==_x('author','slug','oqp'));
	if($is_step_author) return false;

	oqp_single_post_author_infos();
        return $step_html;
}

function oqp_single_post_author_infos($short=true){
	?>
	<div id="author-profile">
		<div class="author-avatar">
				<?php echo get_avatar( get_the_author_meta( 'user_email' ), 100 ); ?>
			</a>
		</div><!-- #item-header-avatar -->
		<?php do_action('oqp_single_post_author_infos_before');?>
                <p>
                    <?php the_author();?>
                </p>
		<?php 
		$bio = get_the_author_meta( 'description' );
		
		if ( $bio ) { // If a user has filled out their decscription show a bio on their entries  
			$author_link_args['oqp_step']=_x('author','slug','oqp');
			?>
			<div id="author-bio">
				<h3><?php _e( 'About ', 'twentyten' ); ?><?php the_author(); ?></h3>
				<?php 
				if ($short){
					echo oqp_trim_text($bio);
					?>
					<a href="<?php oqp_single_post_get_tab_link($author_link_args);?>"><?php _e('More','oqp');?></a>
					<?php
				}else{
					echo $bio;
				}
				
				?>
				
			</div><!-- #author-bio	-->
		<?php 
                }else{

                }
                
                ?>
		<?php do_action('oqp_single_post_author_infos_after');?>
	</div>
	<?php
}

function oqp_author_bio_get_edit_link(){
    $url = admin_url('profile.php');
    
    $url = apply_filters("oqp_author_bio_get_edit_link",$url);
    
    return $url;
}

function oqp_author_bio_warning(){
    global $oqp_form;
    
    $is_step_author=($oqp_form->step->slug==_x('author','slug','oqp'));
    if(!$is_step_author)return false;
    
    $author_id = get_the_author_meta( 'ID' );
    if($author_id!=get_current_user_id())return false;
    ?>
        <div id="author-bio">
            <?php printf(__("Users can't read any informations about you.  Do you want to %s ?","oqp"),'<a href="'.oqp_author_bio_get_edit_link().'">'.__('update your profile','oqp').'</a>');?>
        </div>
    <?php
}


function oqp_trim_text($text,$length=false){
	if(!$length){
		$length = apply_filters('oqp_excerpt_length', 25);
	}

	$text = strip_tags($text);
	$excerpt_more = apply_filters('oqp_excerpt_more', ' ' . '[...]');
	$words = preg_split("/[\n\r\t ]+/", $text, $length + 1, PREG_SPLIT_NO_EMPTY);
	if ( count($words) > $length ) {
		array_pop($words);
		$text = implode(' ', $words);
		$text = $text . $excerpt_more;
	} else {
		$text = implode(' ', $words);
	}
	return $text;	
	
}

function oqp_the_excerpt($excerpt) {

	if (!is_oqp_post()) return $excerpt;
	
	$excerpt = $post->post_excerpt;
	if(!$excerpt){
		$excerpt = $post->post_content;
	}
	
	return oqp_trim_text($excerpt);

}

function oqp_preview_remove_title(){
	global $oqp_form;
	if(!oqp_is_single()) return false;

        $step = get_requested_step();
        $step_key = get_requested_step_key();

        foreach ((array)$step->fields as $field_key=>$field){
            if($field->model!='title')continue;
            oqp_debug("oqp_preview_remove_title S".$step_key."F".$field_key);
            oqp_delete_field($field_key,$step_key);
        }
}

function oqp_delete_step($step_key=false){
	global $oqp_form;
	
	if((is_bool($step_key))&&(!$step_key))$step_key=$oqp_form->current_step;
        
        

        
        if($step_key<0) return false;
        
        oqp_debug("delete step#".$step_key." '".$oqp_form->steps[$step_key]->name."'");
	
	unset($oqp_form->steps[$step_key]);
      
}

function oqp_delete_field($field_key=false,$step_key=false){
	global $oqp_form;
        
        if(!is_int($step_key))$step_key=$oqp_form->current_step;
	if(!is_int($field_key))$field_key=$oqp_form->step->current_field;
        $field = $oqp_form->steps[$step_key]->fields[$field_key];

        

        if($field){
            oqp_debug($field->name,"DELETE field S".$step_key."F".$field_key);
            unset($oqp_form->steps[$step_key]->fields[$field_key]);
        }else{
            oqp_debug($field->name,"error while deleting field S".$step_key."F".$field_key,"error");
        }
	

}




?>