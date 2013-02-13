<?php oqp_single_post_author_infos(false);?>
<?php oqp_author_last_posts();

function oqp_author_last_posts($author_id=false,$args=false){
	global $post;
	global $wp_query;
	
	if (!$author_id) {
		$author_id=$post->post_author;
	}
	$defaults['author']=$author_id;
	$defaults['post_type']=$post->post_type;
	$defaults['orderby']='date';
	$defaults['posts_per_page']=5;

	$defaults['post__not_in']=(array)$post->ID;

	$defaults['max_num_pages']=1;
	
	$args = wp_parse_args( $args, $defaults );
	
	$args = apply_filters('oqp_author_last_posts_query_args',$args);
        
        

	$temp_query=$wp_query;
        
        $wp_query = new WP_Query($args);

	?>
	<div class="oqp_last_author_posts">
		<h3><?php printf(__('Last %1s by %2s','oqp'),strtolower(oqp_get_post_type_label("name")),get_the_author());?></h3>
		<span class="generic-button" id="oqp-author-ads-button">
			<a href="<?php oqp_user_posts_link(); ?>" title="<?php printf( esc_attr__( 'View all %1s by %2s', 'oqp' ),strtolower(oqp_get_post_type_label()), get_the_author() ); ?>"><?php _e( 'View all', 'oqp' ); ?></a>
		</span><!-- #author-link	-->
		<?php oqp_get_template_part('oqp-loop');?>
	</div>
	<?php
	
	$wp_query=$temp_query;
}

?>
