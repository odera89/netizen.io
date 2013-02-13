<?php

function oqp_get_tab_content(){
	global $oqp_form;

	$form_slug=oqp_post_get_form_page_id($_POST['post_id']);
	$oqp_form=new Oqp_Display_Post($form_slug,false,$_POST['post_id']);

	$stepkey=$oqp_form->get_step_key($_POST['tab_slug']);
	$oqp_form->current_step=$stepkey;
	oqp_single_post_display_step($oqp_form->current_step);

}






add_action("wp_ajax_oqp_get_tab_content", "oqp_get_tab_content");
?>