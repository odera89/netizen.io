<?php

/**
 * A class that takes the pain out of the $_FILES array
 * @author Christiaan Baartse <christiaan@baartse.nl>
 */
class UploadedFiles extends ArrayObject
{
    public function current() {
        return $this->_normalize(parent::current());
    }

    public function offsetGet($offset) {
        return $this->_normalize(parent::offsetGet($offset));
    }

    protected function _normalize($entry) {
        if(isset($entry['name']) && is_array($entry['name'])) {
            $files = array();
            foreach($entry['name'] as $k => $name) {
                $files[$k] = array(
                    'name' => $name,
                    'tmp_name' => $entry['tmp_name'][$k],
                    'size' => $entry['size'][$k],
                    'type' => $entry['type'][$k],
                    'error' => $entry['error'][$k]
                );
            }
            return new self($files);
        }
        return $entry;
    }
}

function oqp_set_featured($post_id,$attach_id){
    return update_post_meta($post_id,'_thumbnail_id',$attach_id);
}


function oqp_insert_attachment($file_id,$post_id,$type,$setthumb='false') {
	//IDEA : http://goldenapplesdesign.com/2010/07/03/front-end-file-uploads-in-wordpress/
	require_once(ABSPATH . "wp-admin" . '/includes/image.php');
	require_once(ABSPATH . "wp-admin" . '/includes/file.php');
	require_once(ABSPATH . "wp-admin" . '/includes/media.php');

	$attach_id = media_handle_upload( $file_id, $post_id );
	
	if (!is_int($attach_id)) return false;

	if (($setthumb) && ($type=='image')) {
		oqp_set_featured($post_id,$attach_id);
	}
	return $attach_id;
}

function oqp_get_files_block($oqp_form_id,$type,$atts=false){
	global $bp;
	global $post;

	if ((!$oqp_form_id) || (!is_numeric($oqp_form_id)))return false;

	if ($type=='image') {

		$default=array(
			'link'=>'file',
			'size'=>'thumbnail'
		);
		
		$args = wp_parse_args( $atts, $default);
		$block = do_shortcode('[gallery id="'.$oqp_form_id.'" link="'.$args['link'].'" size="'.$args['size'].'"]');
		
	}else {
		$attachments = oqp_upload_get_files($oqp_form_id,$type);

		
		if (!$attachments) return false;
		
		$block='<ul class="oqp_attachments">';
		
		
		foreach ($attachments as $attachment) {
			$name = $attachment->post_title;
			$link = get_attachment_link($attachment->ID);
			$file_icons = wp_get_attachment_image_src( $attachment->ID, false, true );

			$image = '<img width="20" height="20" src="'.$file_icons[0].'"/>';

			$block.='<li>'.$image.'<a href="'.$link.'">'.$name.'</a></li>';

		}
		
		$block.="</ul>";

	}

	return $block;
}

function oqp_upload_get_total_files_count($post_id=false,$type='image') {
	global $post;
	
	if (!$post_id)
		$post_id=$post->ID;
		
	if (!$post_id) return false;
	
	$attachments = oqp_upload_get_files($post_id,$type);

	return count($attachments);

}

function oqp_upload_get_files($post_id=false,$type='image') {
	global $post;
	
	if (!$post_id)
		$post_id=$post->ID;

	return get_children( array('post_parent' => $post_id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => $type) );
}







function oqp_has_post_thumbnail( $post_id = NULL ) {
	if (!current_theme_supports( 'post-thumbnails' )) return false;
	
	//TO FIX we shouldn't verify this, but it broke in BP
	if (!function_exists('get_post_thumbnail_id')) return false;
	
	global $id;
	$post_id = ( NULL === $post_id ) ? $id : $post_id;
	return apply_filters('oqp_has_post_thumbnail',get_post_thumbnail_id( $post_id ));
}

function oqp_field_gallery_remove_single_pic_galleries($enabled,$field) {
	global $oqp_form;
	
	if($oqp_form->type!='single') return $enabled;
	if ($field->slug!='upload') return $enabled;
	if ($field->type!='image') return $enabled;
	
	return true;
	
	if (oqp_upload_get_total_files_count()<=1) return false;
	return $enabled;
}


function oqp_post_the_thumbnail($size,$attr='') {
	echo oqp_post_get_thumbnail($size, $attr);
}
	function oqp_post_get_thumbnail($size,$attr='') {
		return get_the_post_thumbnail( NULL, $size, $attr );
	}
function oqp_post_the_thumbnail_link() {
	echo oqp_post_get_thumbnail_link();
}
function oqp_post_get_thumbnail_link() {
	$image_id = get_post_thumbnail_id();
	$image_url = get_attachment_link($image_id);
	return $image_url;
}

function oqp_post_entry_icons_images() {
	global $post;
	$pictures = oqp_upload_get_total_files_count($post->ID);
	if ($pictures<=1) return false;

	$image = oqp_get_theme_file_url('images.png','_inc/images');
	?>
	<img src="<?php echo $image;?>"/>
	<?php 
}

//copy of function gallery_shortcode($attr)
function oqp_gallery_shortcode( $output, $attr) {
    global $post, $wp_locale;

    static $instance = 0;
    $instance++;

    // We're trusting author input, so let's at least make sure it looks like a valid orderby statement
    if ( isset( $attr['orderby'] ) ) {
        $attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
        if ( !$attr['orderby'] )
            unset( $attr['orderby'] );
    }

    extract(shortcode_atts(array(
        'order'      => 'ASC',
        'orderby'    => 'menu_order ID',
        'id'         => $post->ID,
        'itemtag'    => 'dl',
        'icontag'    => 'dt',
        'captiontag' => 'dd',
        'columns'    => 3,
        'size'       => 'thumbnail',
        'include'    => '',
        'exclude'    => ''
    ), $attr));

    $id = intval($id);
    if ( 'RAND' == $order )
        $orderby = 'none';

    if ( !empty($include) ) {
        $include = preg_replace( '/[^0-9,]+/', '', $include );
        $_attachments = get_posts( array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );

        $attachments = array();
        foreach ( $_attachments as $key => $val ) {
            $attachments[$val->ID] = $_attachments[$key];
        }
    } elseif ( !empty($exclude) ) {
        $exclude = preg_replace( '/[^0-9,]+/', '', $exclude );
        $attachments = get_children( array('post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
    } else {
        $attachments = get_children( array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
    }

    if ( empty($attachments) )
        return '';

    if ( is_feed() ) {
        $output = "\n";
        foreach ( $attachments as $att_id => $attachment )
            $output .= wp_get_attachment_link($att_id, $size, true) . "\n";
        return $output;
    }

    $itemtag = tag_escape($itemtag);
    $captiontag = tag_escape($captiontag);
    $columns = intval($columns);
    $itemwidth = $columns > 0 ? floor(100/$columns) : 100;
    $float = is_rtl() ? 'right' : 'left';

    $selector = "gallery-{$instance}";

    $output = apply_filters('gallery_style', "
        <style type='text/css'>
            #{$selector} {
                margin: auto;
            }
            #{$selector} .gallery-item {
                float: {$float};
                margin-top: 10px;
                text-align: center;
                width: {$itemwidth}%;           }
            #{$selector} img {
                border: 2px solid #cfcfcf;
            }
            #{$selector} .gallery-caption {
                margin-left: 0;
            }
        </style>
        <!-- see gallery_shortcode() in wp-includes/media.php -->
        <div id='$selector' class='gallery galleryid-{$id}'>");

    $i = 0;
    foreach ( $attachments as $id => $attachment ) {
        $link = isset($attr['link']) && 'file' == $attr['link'] ? wp_get_attachment_link($id, $size, false, false) : wp_get_attachment_link($id, $size, true, false);

        $output .= "<{$itemtag} class='gallery-item'>";
            
        $output .= "<{$icontag} class='gallery-icon'>".$link."</{$icontag}>";
        
        $output.= apply_filters("oqp_gallery_item_html",false,$attachment);
        
            
            if ( $captiontag && trim($attachment->post_excerpt) ) {
                $output .= "
                    <{$captiontag} class='gallery-caption'>
                    " . wptexturize($attachment->post_excerpt) . "
                    </{$captiontag}>";
            }
        $output .= "</{$itemtag}>";
        if ( $columns > 0 && ++$i % $columns == 0 )
            $output .= '<br style="clear: both" />';
    }

    $output .= "
            <br style='clear: both;' />
        </div>\n";

    return $output;
}

function oqp_gallery_item_default_thumb_link($html,$attachment){
    
    global $post,$oqp_form;
    
    if(!is_oqp_form())return $html;
    
    $can_post_edit = current_user_can( 'edit_post', $post->ID );
    if(!$can_post_edit) return $html;
    
    //is already default image
    $default_attach = oqp_has_post_thumbnail();
    if($default_attach==$attachment->ID) return false;
    
    
    $action = 'set_default';

    
    $url_args['step_key']=$oqp_form->current_step;
    $url_args['oqp_action']=$oqp_form->action;
    $url_args['attach_id']=$attachment->ID;
    $url_args['attach_action']=$action;
    $url_args['attach_nonce']=oqp_gallery_admin_action_nonce($action,$attachment->ID);

    $link =oqp_get_base_link($url_args);
    
    $title=__('Set as post thumb','oqp');
    $block='<a title="'.$title.'" class="oqp-gallery-item-action" href="'.$link.'">'.$title.'</a>';
    
    
    return $html.$block;
}

function oqp_gallery_item_delete_item_link($html,$attachment){
    
    global $post,$oqp_form;
    
    if(!is_oqp_form())return $html;

    $can_post_edit = current_user_can( 'edit_post', $post->ID );
    if(!$can_post_edit) return $html;
    
    $action = 'delete';
    
    $url_args['step_key']=$oqp_form->current_step;
    $url_args['oqp_action']=$oqp_form->action;
    $url_args['attach_id']=$attachment->ID;
    $url_args['attach_action']=$action;
    $url_args['attach_nonce']=oqp_gallery_admin_action_nonce($action,$attachment->ID);
    

    $link =oqp_get_base_link($url_args);
    
    $title=__('Delete','oqp');
    $block='<a title="'.$title.'" class="oqp-gallery-item-action" href="'.$link.'">'.$title.'</a>';
    
    return $html.$block;
    
}

function oqp_gallery_admin_action_nonce($action,$id){
    return wp_create_nonce($action.'-'.$id);
}


function oqp_gallery_admin_actions(){
    global $oqp_form,$post;
    
    $attach_id = $_REQUEST['attach_id'];
    $attach_action = $_REQUEST['attach_action'];
    $attach_nonce = $_REQUEST['attach_nonce'];

    if(!$attach_id)return false;
    if(!$attach_action)return false;
    
    //CHECK NONCE
    if (!wp_verify_nonce($attach_nonce,$attach_action.'-'.$attach_id)){
        $oqp_form->notices->error('oqp_step','attachment_nonce');
        return false;
    }

    switch ($attach_action) {
        case 'delete':
            
            //check is default
            $default_attach = oqp_has_post_thumbnail();
            
            if (wp_delete_attachment($attach_id)){
                $oqp_form->notices->message('oqp_step','attachment_deleted',$attach_id);
                
                //was featured attachment; replace it
                if($default_attach==$attach_id){

                    //get post images
                    $attachments = oqp_upload_get_files();
                    $first_attach = array_shift(array_values($attachments));

                    if ($first_attach) oqp_set_featured($post->ID,$first_attach->ID);

                }

            }else{
                $oqp_form->notices->error('oqp_step','attachment_deleted_error',$attach_id);
            }
            
            break;
        case 'set_default':
            if (oqp_set_featured($post->ID,$attach_id)){
                $oqp_form->notices->message('oqp_step','attachment_set_default',$attach_id);
            }else{
                $oqp_form->notices->error('oqp_step','attachment_set_default_error',$attach_id);
            }
                
            break;
    }
    
    do_action('oqp_gallery_admin_actions');
    
    
}


//add "set thumb as default" link
add_filter('oqp_gallery_item_html','oqp_gallery_item_default_thumb_link',10,2);

//add "set thumb as default" link
add_filter('oqp_gallery_item_html','oqp_gallery_item_delete_item_link',10,2);

//handle attachment actions
add_action('oqp_creation_post_actions','oqp_gallery_admin_actions');


?>