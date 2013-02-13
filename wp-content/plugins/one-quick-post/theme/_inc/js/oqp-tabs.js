jQuery(document).ready(function($) {
	/*AJAX FOR SINGLE POST TABS*/
	/*
	$('.single-oqp #object-nav li').click(function(){
		
		var this_tab=$(this);
		var post_el=this_tab.parents('.oqp_post');
		var tabs=post_el.find('.oqp_step_tab');
		var post_id=post_el.attr('rel');

		var tab_el_id=this_tab.attr('id');
		var tab_idsplit=tab_el_id.split('oqp-tab-');
		var tab_slug=tab_idsplit[1];
		
		
		tabs.removeClass("selected loading");
		this_tab.addClass("selected");
		this_tab.addClass("loading");
		

		var data = {
			action: 'oqp_get_tab_content',
			tab_slug:tab_slug,
			post_id:post_id
		};
		$.post( ajaxurl, data, function(response) {
			this_tab.removeClass("loading");
			$('.oqp-step').html(response);
		});
			return false;
	});
	*/
});
