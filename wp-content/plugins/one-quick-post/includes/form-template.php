<?php

function oqp_form_page_link(){
	echo oqp_form_page_get_link();
}

	function oqp_form_page_get_link($page_id=false){
		global $oqp_form;

                if(!$page_id){
                    return $oqp_form->get_permalink();
                }else{
                    $link = get_permalink($page_id);
                    return apply_filters('oqp_form_page_get_link',$link,$page_id);
                }
	}
  
function oqp_creation_link($args){
    echo oqp_get_creation_link($args);
}

function oqp_get_creation_link($args=false,$post_id=false) {
        $args['oqp_action']=_x('create','slug','oqp');
	return oqp_get_base_link($args,$post_id);
}

function oqp_get_edition_link($args=false,$post_id=false) {
        $args['oqp_action']=_x('admin','slug','oqp');
	return oqp_get_base_link($args,$post_id);
}


function oqp_get_delete_link($args=false,$post_id=false) {
        $args['oqp_step']=false;
        $args['oqp_action']=_x('delete','slug','oqp');
	return oqp_get_base_link($args,$post_id);
}

function oqp_get_base_link($args=false,$post_id=false) {
        global $post,$oqp_form;
        

        
        if(!isset($args['post_id'])){
            if(!$post_id)$post_id=$post->ID;
        }else{
            $post_id=$args['post_id'];
        }

        if($post_id){
            $url = get_permalink($post_id);
            if(!$oqp_form->templates['singular']) $args['oqp']=true;
            
        }else{
            $url = oqp_form_page_get_link();

        }

        if($args['oqp_action']){
            $args['_wpnonce'] = wp_create_nonce('oqp-' . $args['oqp_action'].'-'.$post_id);
        }
        
	if(isset($args['step_key'])){
		$args['oqp_step']=$oqp_form->get_step_slug($args['step_key']);
		unset($args['step_key']);
	}
        
        $link = oqp_build_url_args($url,$args);

	return apply_filters('oqp_get_base_link',$link,$args);
}

function oqp_get_user_posts_link($user_id=false) {
	global $post;
	
	if (!$user_id) {
		$user_id=$post->post_author;
	}
        
        $args['author']=$user_id;
        $args['post_id']=false;

	$link = oqp_get_base_link($args);

	return $link;
	
}
function oqp_user_posts_link($user_id=false) {
	echo oqp_get_user_posts_link($user_id);
}




function oqp_single_post_tab_link($args=false,$post_id=false){
	echo oqp_single_post_get_tab_link($args,$post_id);
}

function oqp_single_post_get_tab_link($args=false,$post_id=false){
	if(!$post_id){
		global $post;
		$post_id=$post->ID;
	}
	$url=get_permalink($post_id);
	
	
	
	$url=oqp_build_url_args($url,$args);
	
	return $url;

}
	


function oqp_creation_button($post_type=false) {

	//if (!oqp_user_can_for_ptype('edit_posts')) return false;

	$post_label = strtolower(oqp_get_post_type_label(false,$post_type));
	
        $link_args['post_id']=false;
	$link = oqp_get_creation_link($link_args);
	?>
	<span id="oqp_creation_button" class="generic-button <?php echo $post_type;?>"><a href="<?php echo $link;?>"><?php printf(__('Create new %s','oqp'),$post_label);?></a></span>
	<?php
}


    
function oqp_get_step_slug($id=false){
    global $oqp_form;
    if(!$id) $id = $oqp_form->current_step;
    return $oqp_form->steps[$id]->slug;
}


    

function oqp_directory_button(){

	global $oqp_form;
        
        $post_type = $oqp_form->post_type;

        //TO FIX URGENT
//	if (!current_user_can('read')) return false;
        
	$post_label = strtolower(oqp_get_post_type_label('name'));

	$link = oqp_form_page_get_link();
	?>
	<span id="oqp_directory_button" class="generic-button <?php echo $post_type;?>"><a href="<?php echo $link;?>"><?php printf(__('Browse %s','oqp'),$post_label);?></a></span>
	<?php
}


function oqp_form_is_several_steps() {
	global $oqp_form;

	$steps = count($oqp_form->steps);


	if ($steps>1) return true;
	return false;
}

function oqp_get_steps() {
	global $oqp_form;
	return $oqp_form->args['steps'];
}

function oqp_get_action_var() {
	global $oqp_form;
	return $oqp_form->action;
}

function oqp_step_name($step_key){
	echo oqp_get_step_name($step_key);
}

function oqp_get_step_name($step_key){
	global $oqp_form;
	
	$step=$oqp_form->steps[$step_key];
	
	$stepname=$step->name;
	
	if (($oqp_form->action)&&($step->required)) {
		$classes[]="required";
		if ($oqp_form->type=='form') {
			$stepname.="*";
		}
	}
	
	return apply_filters('oqp_get_step_name',$stepname,$step_key);
	
	
	

}

function oqp_get_step_order($key){
    global $oqp_form;
    $count=0;
    foreach ($oqp_form->steps as $step_key=>$step){
        if($key==$step_key)return $count;
        $count++;
    }
}

function oqp_get_post_tab_name($step_key){
    global $oqp_form;

    $name=oqp_get_step_name($step_key);
    $step=$oqp_form->steps[$step_key];

    $step_count = oqp_get_step_order($step_key);
    $step_count+=1;
    
    if($oqp_form->type=='form'){
        $name=$step_count.'. '.$name;
    }

    return apply_filters('oqp_get_post_tab_name',$name,$step);
}

function oqp_get_step_custom_template(){
    global $oqp_form;
    return $oqp_form->step->template;
}

function oqp_post_tabs() {
	global $oqp_form;
        global $post;
        
        $requested_step_key = $oqp_form->current_step;
        
        $oqp_form->rewind_steps();
        
        if (!$oqp_form->have_steps() ) return false;

	if (!oqp_form_is_several_steps()) return false;

        while ( $oqp_form->have_steps() ) {
            $oqp_form->the_step();

            unset($classes);
            unset($class_html);

            if($oqp_form->current_step==$requested_step_key){
                $is_enabled = true;
                $classes[]='current';
            }else{
                $is_enabled = in_array($oqp_form->current_step,(array)$oqp_form->enabled_steps);
            }

            $classes = apply_filters('oqp_tabs_classes',$classes);

            if ($classes)
                    $class_html=' class="'.implode(" ",$classes).'"';

            $url_args['step_key']=$oqp_form->current_step;
            $url_args['oqp_action']=$oqp_form->action;

            $link =oqp_get_base_link($url_args);

            $tab_name = oqp_get_post_tab_name($oqp_form->current_step);

            ?>
            <li<?php echo $class_html; ?>><?php if ( $is_enabled ) : ?><a href="<?php echo $link ?>"><?php else: ?><span><?php endif; ?><?php echo $tab_name;?><?php if ( $is_enabled ) : ?></a><?php else: ?></span><?php endif ?></li><?php

        }


	unset( $is_enabled );
        
        do_action( 'oqp_post_tabs' );

}

function oqp_form_buttons() {
	echo oqp_get_form_buttons();
}
	function oqp_get_form_buttons() {
		global $oqp_form;
                global $post;
                
                

		$post_label = strtolower(oqp_get_post_type_label());
		
		$main_button_text = sprintf(__('Update %s', 'oqp'),$post_label);

		if ($oqp_form->is_last_step()) { //finish
			//TO FIX or pending
			$status = $post->post_status;
			if ($status=='draft') {
				$main_button_text = __('Publish*','oqp');
				if (oqp_user_can_for_ptype('publish_posts')) {
					$main_button_text = __('Publish');
				}
			}
		}
                
                

		if ($oqp_form->action==_x('admin','slug','oqp')) {
			$main_button_text = sprintf(__('Update %s', 'oqp'),$post_label);
		}elseif ($oqp_form->action==_x('create','slug','oqp')) {

                    if ( $oqp_form->is_first_step() ) { //create & continue
				$main_button_text = sprintf(__('Create %s and continue', 'oqp'),$post_label)." &rarr;";
                    }
			
                    if (oqp_form_is_several_steps()) {
                            if (!$oqp_form->is_first_step()) { //finish
                                    $previous_bt=true;
                            }
                            if (!$oqp_form->is_last_step()) {
                                    $main_button_text = __('Next Step', 'oqp').' &rarr;';
                            }

                    }
		}
		
		if ($previous_bt) {
                        $prev_url_args['step_key']=$oqp_form->current_step-1;
                        $prev_url_args['oqp_action']=$oqp_form->action;
                        $previous_location=oqp_get_base_link($prev_url_args);
			$buttons[]='<input class="generic-button" type="button" value="&larr; '.__('Previous Step', 'oqp').'" id="oqp-creation-previous" name="previous" onclick="location.href=\''.$previous_location.'\'"/>';
		}
		
		

		$buttons[] = '<input class="generic-button" type="submit" value="'.$main_button_text.'" id="oqp-creation-save" name="save"/>';
		return implode("\n",$buttons);
	}


	
function oqp_atts_str($atts) {
	echo oqp_get_atts_str($atts);
}


	
function oqp_get_atts_str($atts) { //key=attribute, value=enabled
	if (!$atts) return false;
	
	foreach ($atts as $att=>$enabled) {
		if (!$enabled) continue;
		$atts_names[]=$att;
	}
	if ($atts_names)
	return ' '.implode(" ",$atts_names);
}

function oqp_post_less_than_hours($post=false,$hours=24){
	if (!$post) {
		global $post;
	}
	
	$current_time =  current_time('timestamp');
	$seconds = get_the_time('U',$post->ID);
	$diff = (int) abs($current_time - $seconds);
	$limit = round($diff /($hours/24)/86400);
	
	if ($limit<1) return true;
	
	return false;
	
}

function oqp_post_date($post=false) {
	echo oqp_post_get_date($post);
}

function oqp_post_get_date($post=false) {
	if (!$post) {
		global $post;
	}
	
	$seconds = get_the_time('U',$post->ID);
	
	$classes=array();
	$classes[]='timestamp';
	$classes[]='activity';

	if (oqp_post_less_than_hours($post)) {
		
		$date = sprintf(__('%s ago'),human_time_diff($seconds,$current_time));
		//$date = get_the_time(get_option('date_format'), $post->ID);
	}else {
		$date = get_the_time(get_option('date_format'), $post->ID);
	}
	$timestamp = get_the_time('c',$post->ID); //timestamp ISO 8601
	
	if ($classes){
		$classes_html='class="'.implode(' ',$classes).'"';
	}
	$stat.='<span '.$classes_html.' title="'.$timestamp.'">';
	$stat.=$date;

	$stat.='</span>';
	
	return apply_filters('oqp_post_get_date',$stat,$classes);
}


function oqp_post_status($post=false) {
	if (!$post) {
		global $post;
	}
	$status = $post->post_status;
        
	switch ( $status ) {
		case 'pending' :
			$stats['pending'] = __('Pending Review');
			break;
		case 'draft' :
			$stats['draft'] = __('Draft');
			break;
		case 'auto-draft' :
			$stats['auto-draft'] = __('Draft');
			break;
		case 'publish' :
			$stats['publish'] = __('Published');
			break;
	}
	
	$stats=apply_filters('oqp_post_status',$stats);

        ?>
        <span class="oqp-status">
            <?php
            foreach ((array)$stats as $class=>$stat){
                    echo' <span class="activity '.$class.'">';
                    echo $stat;
                    echo'</span>';
            }?>
        </span>
        <?php


}

///
//STATS|START//
//Stats tool based upon : Wordpress.com Stats Helper by Author: Vlad Bailescu



// Checks to see if wordpress.com stats plugin is installed and the api key is defined
function oqp_stats_is_setup() {
	// Check if worpress.com stats plugin is installed
	if (!function_exists('stats_get_api_key') || !function_exists('stats_get_option')) {
		return false;
	}
	// Check if the API key is defined
	$api_key = stats_get_api_key();
	if (empty($api_key)) {
		return false;
	}
	return $api_key;
}

function oqp_the_ad_visit_count() {
	echo oqp_get_post_visit_count();
}

function oqp_get_post_visit_count($post_id=false) {

	if (!$post_id) {
		global $post;
		$post_id=$post->ID;
	}

	// Check cache
	//TO FIX check how cache is used ?
	$cache_key = 'oqp_visits_count_total';
	$cache_key .= '_'.$post_id;

	$rows = oqp_visits_count_cache_get($cache_key);

	if (!$rows) {
		if (!$api_key = oqp_stats_is_setup()) { return false; }
		// Fetch total visits
		$snoopy = new Snoopy();
		if (@$snoopy->fetch('http://stats.wordpress.com/csv.php?'.
				'api_key='.$api_key.
				'&blog_id='.stats_get_option('blog_id').
				($post_id?('&table=postviews&post_id='.$post_id):'').
				'&days=0'.
				'&summarize=true')) {

			$results = trim(str_replace("\r\n", "\n", $snoopy->results));
			$rows = explode("\n", $results);
			// Cache the results rows
			oqp_visits_count_cache_set($cache_key, $rows);
		}
	}
	
	$count = apply_filters('oqp_get_post_visit_count',$rows[1],$post_id);

	return (int)$count;
}

// Simple cache based on wp_cache and doubled by options

// Cache getter
function oqp_visits_count_cache_get($key) {
	// Check wp_cache
	$result = wp_cache_get($key);
	if ($result) {
		return $result;
	}
	// Check option-based cache
	$opt_cache = get_option('oqp_visits_count_cache_set');
	if ($opt_cache) {
		$entry = $opt_cache[$key];
		if ($entry && $entry['expt'] && $entry['expt'] > time() ) {
			wp_cache_set($key, $entry['value'], '', $entry['expt'] - time());
			return $entry['value'];
		}
	}
	return false;
}

// Cache setter
function oqp_visits_count_cache_set($key, $value, $expire = 500) {
	// Set wp_cache
	wp_cache_set($key, $value, '', $expire);
	// Set option-based cache
	$opt_cache = get_option('opt_cache');
	if (!$opt_cache) { $opt_cache = array(); }
	$opt_cache[$key] = array('value' => $value, 'expt' => time() + $expire);
	if (is_array($opt_cache)) {
		foreach($opt_cache as $key => $val) {
			if ($val['expt'] < time()) {
				unset($opt_cache[$key]);
			}
		}
	}
	update_option('oqp_visits_count_cache_set', $opt_cache);
}

//STATS|END//



function oqp_posts_pagination_links() {
	echo oqp_get_posts_pagination_links();
}
	function oqp_get_posts_pagination_links() {
		global $wp_query;
                global $oqp_form;
                
                $pag_links = paginate_links( array(
				//'base'      => add_query_arg( array( 'grpage' => '%#%', 'num' => $pag_num, 's' => $search_terms, 'sortby' => $sort_by, 'order' => $order ) ),
				'format'    => add_query_arg(array('paged'=>'%#%'),oqp_form_page_get_link()),
				'total' => $wp_query->max_num_pages,
				'current' => max( 1, get_query_var('paged') ),
				'prev_text' => _x( '&larr;', 'Group pagination previous text', 'buddypress' ),
				'next_text' => _x( '&rarr;', 'Group pagination next text', 'buddypress' ),
				'mid_size'  => 1
			) );

		return apply_filters( 'oqp_get_pagination_links', $pag_links );
	}

function oqp_posts_pagination_count() {
	echo oqp_get_posts_pagination_count();
}
	function oqp_get_posts_pagination_count() {
                global $wp_query;
                
                $posts_per_page = get_query_var('posts_per_page');
                $current_page = max( 1, get_query_var('paged') );

		$start_num = ($current_page-1)*$posts_per_page;
		$from_num = number_format( $start_num+1 );
		
                $to_num = $start_num+$posts_per_page;
                
		$total = number_format( $wp_query->found_posts );
                
                if($to_num>$total)$to_num=$total;

		return apply_filters( 'oqp_get_pagination_links', sprintf( __( 'Viewing %1s %2$s to %3$s (of %4$s %5$s)', 'oqp' ),strtolower(oqp_get_post_type_label()),$from_num, $to_num, $total,strtolower(oqp_get_post_type_label('name'))) );
	}

function oqp_loop_pagination(){
    
    global $wp_query;
    
    if(!$wp_query->found_posts)return false;
    
    ?>
    <div class="pagination">

            <div class="pag-count" id="group-dir-count-top">

                    <?php oqp_posts_pagination_links(); ?>

            </div>

            <div class="pagination-links" id="group-dir-pag-top">

                    <?php oqp_posts_pagination_count(); ?>

            </div>

    </div>
    <?php

}   

function oqp_single_post_permalink($link){
    global $oqp_form;
    
    if($oqp_form->templates['singular']) return $link;
    
    //we do not load singular OQP template each time an oqp_post is called.
    //then, add a var to tell him to load it.
    
    if(!is_oqp_post())return $link;
    
    $link = add_query_arg(array('oqp'=>true),$link);
    
    return $link;
    
}

function oqp_loop_item_title(){
    ?>
    <h2 class="entry-title">
            <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'twentyten' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a>
    </h2>
    <?php
}

function oqp_loop_item_metas(){
    ?>
    <div class="entry-meta">
            <?php do_action( 'oqp_before_loop_item_metas' ); ?>
            <?php do_action( 'oqp_after_loop_item_metas' ); ?>
    </div>
    <?php
}

    function oqp_loop_item_icons(){
        ?>
        <span class="entry-icons">
                <?php do_action('oqp_post_entry_icons');?>
        </span>
        <?php 
    }

        function oqp_loop_item_icon_comments(){
            
            ?>
            <span class="comments-link">
                    <?php comments_popup_link('',1,'<img src="'.oqp_get_theme_file_url('comments.png','_inc/images').'">');?>
            </span>
            <?php
        }



        




?>