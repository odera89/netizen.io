<?php
/**
 * The loop that displays posts
 *
 * The loop displays the posts and the post content.  See
 * http://codex.wordpress.org/The_Loop to understand it and
 * http://codex.wordpress.org/Template_Tags to understand
 * the tags used in it.
 *
 * @package WordPress
 * @subpackage Twenty Ten
 * @since 3.0.0
 */


?>
<div id="oqp-dir-list" class="item-list">
    <?php do_action( 'oqp_before_loop' ); ?>
    <?php if ( have_posts() ) : ?>
            <?php /* Display navigation to next/previous pages when applicable  */ ?>
            <?php while ( have_posts() ) : the_post(); ?>
            <?php /* How to display all other posts  */ ?>

                            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                                    <?php do_action( 'oqp_before_loop_item' ); ?>
                                    <header class="entry-header">
                                            <?php do_action( 'oqp_before_loop_item_header' ); ?>
                                        <?php do_action( 'oqp_after_loop_item_header' ); ?>
                                    </header>
                                    <?php if (post_type_supports(get_post_type(),'thumbnail')){?>
                                        <div class="entry-thumbnail item-avatar">
                                                <?php if (oqp_has_post_thumbnail()) {?>
                                                        <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'twentyten' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php oqp_post_the_thumbnail('oqp_thumb'); ?></a>
                                                <?php } ?>
                                        </div>
                                    <?php }?>
                                    <div class="entry-content">
                                            <?php do_action( 'oqp_before_loop_item_content' ); ?>
                                            <?php echo get_the_excerpt(); ?>
                                            <?php do_action( 'oqp_after_loop_item_content' ); ?>
                                    </div>
                                    
                                    <footer class="entry-meta">
                                         <?php do_action( 'oqp_before_loop_item_footer' ); ?>
                                        <span class="author entry-info">
                                                <a class="url fn n" href="<?php oqp_user_posts_link( get_the_author_meta( 'ID' ) ); ?>" title="<?php printf( esc_attr__( 'View all %s by %s', 'oqp' ),strtolower(oqp_get_post_type_label('name')),get_the_author() ); ?>"><?php the_author(); ?></a>
                                                <div class="author-avatar alignleft item-meta">
                                                        <?php echo get_avatar( get_the_author_meta( 'user_email' ), apply_filters( 'oqp_loop_avatar_size', 20 ) ); ?>
                                                </div><!-- .author-avatar 	-->				
                                        </span>
                                        <?php do_action( 'oqp_after_loop_item_footer' ); ?>
                                    </footer>
                                <?php do_action( 'oqp_after_loop_item' ); ?>
                            </article>

            <?php endwhile; ?>


    <?php else : ?>
            <?php oqp_get_template_part('no-results', 'index' );?>
    <?php endif; ?>
    <?php do_action( 'oqp_after_loop' ); ?>
</div>