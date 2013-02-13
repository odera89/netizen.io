<?php get_header();?>

	<div id="content">
		<div class="padder">

			<h3><?php _e( 'One Quick Post', 'oqp' ) ?>
			<?php oqp_creation_button();?>
			<?php if(is_user_logged_in()) {?>
				 &nbsp;<a class="button" href="<?php echo bp_get_root_domain() . '/' . OQP_SLUG; ?>"><?php _e( 'List my OQP Posts', 'oqp' ) ?></a>
			<?php } ?>
			</h3>
				<?php do_action( 'bp_before_directory_groups_content' ) ?>
			
			<?php if (!oqp_get_action_var()) { //POSTS LIST?>

				<div class="item-list-tabs no-ajax">
					<ul>
						<li class="selected" id="oqp-posts-all"><a href="<?php echo bp_get_root_domain() . '/' . OQP_SLUG ?>"><?php printf( __( 'Publish (%s)', 'oqp' ), oqp_get_posts_count_for_user() ) ?></a></li>
						<li id="oqp-posts-pending"><a href="<?php echo bp_get_root_domain() . '/' . OQP_SLUG . '/list/pending'  ?>"><?php printf( __( 'Pending (%s)', 'oqp' ), oqp_get_posts_count_for_user(false,$post_status='pending') ) ?></a></li>
						<li id="oqp-posts-drafts"><a href="<?php echo bp_get_root_domain() . '/' . OQP_SLUG . '/list/draft' ?>"><?php printf( __( 'Drafts (%s)', 'oqp' ), oqp_get_posts_count_for_user(false,$post_status='draft') ) ?></a></li>
						<li id="oqp-posts-trash"><a href="<?php echo bp_get_root_domain() . '/' . OQP_SLUG . '/list/trash' ?>"><?php printf( __( 'Trash (%s)', 'oqp' ), oqp_get_posts_count_for_user(false,$post_status='trash') ) ?></a></li>

						<?php do_action( 'oqp_bp_home_tabs' ) ?>

					</ul>
				</div><!-- .item-list-tabs -->
				<div id="groups-dir-list" class="groups dir-list">
					<?php oqp_locate_template( 'bp-posts-loop.php', true ) ?>
				</div><!-- #groups-dir-list -->
			<?php }else { //OQP FORM?>
				<?php echo 
				die("oqp_block");
				Oqp_Post::oqp_block();?>
			<?php }?>
			<?php do_action( 'bp_template_content' ) ?>

			<?php wp_nonce_field( 'directory_groups', '_wpnonce-groups-filter' ) ?>

		<?php do_action( 'bp_after_directory_groups_content' ) ?>

		</div><!-- .padder -->
	</div><!-- #content -->

	<?php locate_template( array( 'sidebar.php' ), true ) ?>

<?php get_footer() ?>