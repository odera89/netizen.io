<?php
/********************************************************************************
 * Activity & Notification Functions
 *
 * These functions handle the recording, deleting and formatting of activity and
 * notifications for the user and for this specific component.
 */
 
/***EMAIL NOTIFICATIONS OPTIONS***/

function oqp_bp_screen_notification_settings() {
	//check if at least one form has notifications enabled
	$forms_slugs = oqp_get_forms_page_ids();
	foreach ($forms_slugs as $form_slug) {
		$form = oqp_get_form_options($form_slug);
		if (!$form[email_notifications_enabled]) continue;
		$forms_notifications=true;
	}
	if (!$forms_notifications) return false;
	
	
	?>
	<table class="notification-settings zebra" id="oqp-notification-settings">
		<thead>
			<tr>
				<th class="icon"></th>
				<th class="title"><?php _e( 'One Quick Post', 'oqp' ) ?></th>
				<th class="yes"><?php _e( 'Yes', 'buddypress' ) ?></th>
				<th class="no"><?php _e( 'No', 'buddypress' )?></th>
			</tr>
		</thead>

		<tbody>
			<tr>
				<td></td>
				<td><?php _e( 'One of your post is awaiting moderation', 'oqp' ) ?></td>
				<td class="yes"><input type="radio" name="notifications[notification_oqp_pending_post]" value="yes" <?php if ( !get_user_meta( get_current_user_id(), 'notification_oqp_pending_post', true ) || 'yes' == get_user_meta( get_current_user_id(), 'notification_oqp_pending_post', true ) ) { ?>checked="checked" <?php } ?>/></td>
				<td class="no"><input type="radio" name="notifications[notification_oqp_pending_post]" value="no" <?php if ( 'no' == get_user_meta( get_current_user_id(), 'notification_oqp_pending_post', true ) ) { ?>checked="checked" <?php } ?>/></td>
			</tr>
			<tr>
				<td></td>
				<td><?php _e( 'One of your post is published', 'oqp' ) ?></td>
				<td class="yes"><input type="radio" name="notifications[notification_oqp_approved_post]" value="yes" <?php if ( !get_user_meta( get_current_user_id(), 'notification_oqp_approved_post', true ) || 'yes' == get_user_meta( get_current_user_id(), 'notification_oqp_approved_post', true ) ) { ?>checked="checked" <?php } ?>/></td>
				<td class="no"><input type="radio" name="notifications[notification_oqp_approved_post]" value="no" <?php if ( 'no' == get_user_meta( get_current_user_id(), 'notification_oqp_approved_post', true ) ) { ?>checked="checked" <?php } ?>/></td>
			</tr>
			<tr>
				<td></td>
				<td><?php _e( 'One of your post has been deleted', 'oqp' ) ?></td>
				<td class="yes"><input type="radio" name="notifications[notification_oqp_deleted_post]" value="yes" <?php if ( !get_user_meta( get_current_user_id(), 'notification_oqp_deleted_post', true ) || 'yes' == get_user_meta( get_current_user_id(), 'notification_oqp_deleted_post', true ) ) { ?>checked="checked" <?php } ?>/></td>
				<td class="no"><input type="radio" name="notifications[notification_oqp_deleted_post]" value="no" <?php if ( 'no' == get_user_meta( get_current_user_id(), 'notification_oqp_deleted_post', true ) ) { ?>checked="checked" <?php } ?>/></td>
			</tr>
			<?php do_action( 'oqp_bp_screen_notification_settings' ) ?>
		</tbody>
	</table>
<?php
}



function oqp_bp_email_notifications_settings_message($message,$post){
	$settings_link = bp_core_get_user_domain( $post->post_author ) .  BP_SETTINGS_SLUG . '/notifications/';
	$message[]= sprintf( __( 'To disable these notifications please log in and go to: %s', 'buddypress' ), $settings_link );
	return $message;
}


/***NOTIFICATIONS***/

function oqp_bp_format_notifications( $action, $item_id, $secondary_item_id, $total_items ) {
	global $bp;

	switch ( $action ) {
		case 'post_pending':
			if ( (int)$total_items > 1 ) {
				return apply_filters( 'oqp_bp_multiple_post_pending_notification', '<a href="' . $post_link . '/admin/membership-requests/?n=1" title="' . __( 'Posts pending', 'oqp' ) . '">' . sprintf( __( '%d posts you created are now pending', 'oqp' ), (int)$total_items) . '</a>', $total_items);
			} else {
				$post_id = $item_id;
				$post = get_post($post_id);
				$post_author = $post->post_author;
				$author_fullname = bp_core_get_user_displayname( $post_author );
				$post_title=$post->post_title;
				$post_link = get_permalink($post->ID);
				
				return apply_filters( 'oqp_bp_single_post_pending_notification', '<a href="' . $post_link .'/?n=1" title="' . $post_title . '">' . sprintf( __( 'the post "%1s" is now pending', 'oqp' ), $post_title ) . '</a>', $post_link, $post_title );
			}
		break;

		case 'post_approved':

			if ( (int)$total_items > 1 ){
				return apply_filters( 'oqp_bp_multiple_post_approved_notification', '<a href="' . $bp->loggedin_user->domain . $bp->groups->slug . '/?n=1" title="' . __( 'Some of your posts have been published', 'oqp' ) . '">' . sprintf( __( '%d of your post have been published', 'oqp' ), (int)$total_items) . '</a>', $total_items);
			}else{
				$post_id = $item_id;
				$post = get_post($post_id);
				$post_author = $post->post_author;
				$author_fullname = bp_core_get_user_displayname( $post_author );
				$post_title=$post->post_title;
				$post_link = get_permalink($post->ID);

				return apply_filters( 'oqp_bp_single_post_approved_notification', '<a href="' . $post_link . '?n=1">' . sprintf( __( 'Your post "%s" has been published', 'oqp' ), $post_title ) . '</a>', $post );
			}

		break;
		
		case 'post_deleted':
			if ( (int)$total_items > 1 ) {
				return apply_filters( 'oqp_bp_multiple_post_deleted_notification', '<a href="' . $bp->loggedin_user->domain . $bp->groups->slug . '/?n=1" title="' . __( 'Several of your posts have been deleted', 'oqp' ) . '">' . sprintf( __( '%d of your posts have been deleted', 'oqp' ), (int)$total_items) . '</a>', $total_items );
			}else{
				$post_id = $item_id;
				$post = get_post($post_id);
				
				$deleted_message =  get_post_meta($post_id,'oqp_mod_message', true);
				if($deleted_message)
					$deleted_message =' : "'.$deleted_message.'"';
					
				$post_link = get_permalink($post->ID);
				return apply_filters( 'oqp_bp_single_post_deleted_notification', '<a href="' . $post_link . '?n=1">' . sprintf( __( 'Your post "%s" has been deleted', 'oqp' ), $post->post_title) . $deleted_message . '</a>', $post, $deleted_message );
			}

		break;


	}

	do_action( 'oqp_bp_format_notifications', $action, $item_id, $secondary_item_id, $total_items );

	return false;
}

function oqp_bp_notification_post_publish($post) {
	global $bp;

	//status changed by current logged user, abord notification
	if ((get_current_user_id()==$post->post_author) && (!OQP_DEBUG)) return false;
	
	bp_core_add_notification( $post->ID, $post->post_author, $bp->oqp->id, 'post_approved');
}
function oqp_bp_notification_post_trash($post) {
	global $bp;

	//status changed by current logged user, abord notification
	if ((get_current_user_id()==$post->post_author) && (!OQP_DEBUG)) return false;
	
	// Add the on screen notification
	bp_core_add_notification( $post->ID, $post->post_author, $bp->oqp->id, 'post_deleted');
}



//add settings link to disable notifications
add_filter('oqp_notify_author_post_pending_email_message','oqp_bp_email_notifications_settings_message',10,2);
add_filter('oqp_notify_author_post_publish_email_message','oqp_bp_email_notifications_settings_message',10,2);
add_filter('oqp_notify_author_post_trash_email_message','oqp_bp_email_notifications_settings_message',10,2);


//add notifications options to settings page
add_action( 'bp_notification_settings', 'oqp_bp_screen_notification_settings');

//BUDDYPRESS NOTIFICATIONS
//post published
add_action('oqp_notify_post_publish','oqp_bp_notification_post_publish');

//post deleted
add_action('oqp_notify_post_trash','oqp_bp_notification_post_trash');

//new post published for subscribed author
add_action('oqp_notify_subscriber_new_author_post','oqp_bp_notification_author_subscriber',10,2);

//subscribed ad updated
add_action('oqp_notify_subscriber_single_post_updated','oqp_bp_notification_single_post_subscriber',10,2);

?>