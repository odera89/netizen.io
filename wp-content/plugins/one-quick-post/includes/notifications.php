<?php

//POST AUTHOR NOTIFICATIONS

function oqp_notify_check($new_status, $old_status, $post) {

	$form = oqp_get_form_from_post($post->ID);
	$do_author_emails = $form->email_notifications_enabled;

	oqp_notify_for_post($new_status, $old_status, $post, $do_author_emails);

}



function oqp_notify_for_post($new_status, $old_status, $post, $do_emails) {

	//guests always have email notifications
        //TO FIX TO CHECK
	//if (oqp_user_is_dummy($post->post_author)) $do_emails = true;


	//get author info
	$user_id = $post->post_author;
	$user_info = get_userdata($user_id);
	
	//abord if post status has been changed by author
	if ((get_current_user_id()==$user_id) && (!OQP_DEBUG)) return false;
	
	//get author email
	$email = get_the_author_meta('user_email',$user_id);
	
	//NOTIFICATION TYPE
	
	if (($new_status=='pending') && ($old_status=='draft')) {
		$type='pending';
	}elseif($new_status=='publish') {
		if ($old_status!='publish') {
			$type='publish';			
		}else { //update
			//TO FIX TO DO CHECK DELAY
			$type='update';
		}
	}elseif($new_status=='trash') {
		if (($old_status=='pending') || ($old_status=='publish')) {//only for pending or published posts
			$type='trash';
		}
	}
	
	if (!$type) return false;

	if ($type=='pending') {
	
		oqp_notify_author_post_pending_email($post,$email,$do_emails);
		do_action('oqp_notify_post_pending',$post,$do_emails);
		
	}elseif($type=='publish') {

		oqp_notify_author_post_publish_email($post,$email,$do_emails);
		do_action('oqp_notify_post_publish',$post,$do_emails);
		
	}elseif($type=='update') {
		//oqp_notify_author_post_update_email($post,$email,$do_emails);
		do_action('oqp_notify_post_update',$post,$do_emails);
		
	}elseif($type=='trash') {
	
		oqp_notify_author_post_trash_email($post,$email,$do_emails);
		do_action('oqp_notify_post_trash',$post,$do_emails);
		
	}

}



function oqp_notify_author_post_pending_email($post,$to,$do_email) {

	if (!$do_email) return false;

	$blog_name = get_bloginfo( 'name' );
	
	//POST LABEL
	$post_label = strtolower(oqp_get_post_type_label(false,$post->post_type));

	//TO FIX CHECK BLOG NAME
	$subject = '[' . $blog_name . '] ' . sprintf( __( 'Your %1s: "%2s" is now awaiting moderation', 'oqp' ), $post_label, $post->post_title );
	
	$message[] = sprintf( __('Your %1s "%2s" has been saved on our website %3s and is now awaiting moderation.','oqp'),$post_label, $post->post_title, $blog_name)."\r\n";
	$message[] = __('Your will receive a confirmation email once it is published.','oqp')."\r\n";

	$subject = apply_filters( 'oqp_notify_author_post_pending_email_subject', $subject, &$post );
	$message = apply_filters( 'oqp_notify_author_post_pending_email_message', $message, &$post);
	
	$message_str=implode("\r\n",$message);

	wp_mail( $to, $subject, $message_str );

}


function oqp_notify_author_post_publish_email($post,$to,$do_email) {

	if (!$do_email) return false;
	
	$blog_name = get_bloginfo( 'name' );
	$post_link = get_permalink($post->ID);
	
	//POST LABEL
	$post_label = strtolower(oqp_get_post_type_label(false,$post->post_type));

	//TO FIX CHECK BLOG NAME
	$subject = '[' . $blog_name . '] ' . sprintf( __( 'Your %1s: "%2s" has been published', 'oqp' ), $post_label, $post->post_title );
	
	$message[] = sprintf( __('Your %1s "%2s" has been approved and is now published on our website %3s.','oqp'),$post_label,$post->post_title, $blog_name);
	$message[] = sprintf( __('To view the %1s visit: %2s.','oqp'),$post_label,$post_link)."\r\n";

	$subject = apply_filters( 'oqp_notify_author_post_publish_email_subject', $subject, &$post );
	$message = apply_filters( 'oqp_notify_author_post_publish_email_message', $message, &$post);
	
	$message_str=implode("\r\n",$message);

	wp_mail( $to, $subject, $message_str );

}

function oqp_notify_author_post_trash_email($post,$to,$do_email) {

	if (!$do_email) return false;

	$blog_name = get_bloginfo( 'name' );
	
	//POST LABEL
	$post_label = strtolower(oqp_get_post_type_label(false,$post->post_type));

	//TO FIX CHECK BLOG NAME
	$subject = '[' . $blog_name . '] ' . sprintf( __( 'Your %1s: "%2s" has been deleted', 'oqp' ), $post_label, $post->post_title );

	$deleted_message =  get_post_meta($post->ID,'oqp_mod_message', true);
	if ($deleted_message) {
		$message[] = sprintf( __("Your %1s \"%2s\" has been deleted from our website %3s."),$post_label, $post->post_title, $blog_name)."\r\n";
		$message[] = "\t\"".$deleted_message."\"";
	}else {
		$message[] = sprintf( __("Your %1s \"%2s\" has been deleted from our website %3s."),$post_label, $post->post_title, $blog_name);
	}


	$subject = apply_filters( 'oqp_notify_author_post_trash_email_subject', $subject, &$post );
	$message = apply_filters( 'oqp_notify_author_post_trash_email_message', $message, &$post);
	
	$message_str=implode("\r\n",$message);

	wp_mail( $to, $subject, $message_str );

}


//////////////////////


function oqp_notification_edit_message($message,$post) {

	$valid_post_status = array('draft','pending','publish');

	//POST LABEL
	$post_label = strtolower(oqp_get_post_type_label(false,$post->post_type));
	
	if (!in_array($post->post_status,$valid_post_status)) return false;

	if (
		(($post->post_status=='publish') && (oqp_user_can_for_ptype('edit_published_posts',$post->post_type,$post->post_author))) ||
		(($post->post_status!='publish') && (oqp_user_can_for_ptype('edit_posts',$post->post_type,$post->post_author)))
		) {	
			$message['edit_link'] = sprintf(__('To edit the %1s visit: %2s.','oqp'),$post_label, oqp_get_edition_link(false,$post->ID));
	}
	
	if ((!oqp_user_can_for_ptype('edit_published_posts',$post->post_type,$post->post_author)) && ($post->post_status!='publish')){

			$message['edit_link'].='  '.__('You will not be able to edit it once published.','oqp');
	}

	return $message;
}

//add edit links to mails
add_filter('oqp_notify_author_post_pending_email_message','oqp_notification_edit_message',10,2);
add_filter('oqp_notify_author_post_publish_email_message','oqp_notification_edit_message',10,2);

?>