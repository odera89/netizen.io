<?php
/**
 * OQP Archive Template
 *
 * The archive template is basically a placeholder for archives that don't have a template file. 
 * Ideally, all archives would be handled by a more appropriate template according to the current
 * page context.
 *
 * @package Hybrid
 * @subpackage Template
 */

get_header(); // Loads the header.php template. ?>
<div id="primary">
    <div id="content" class="hfeed content">

            <?php get_template_part( 'loop-meta' ); // Loads the loop-meta.php template. ?>

            <?php do_action( 'oqp_before_content' );?>

            <?php oqp_get_template_part('directory-menu');?>
            <?php oqp_get_template_part('oqp-loop');?>

            <?php do_action( 'oqp_after_content' );?>


    </div><!-- .content .hfeed -->
</div>
<?php get_sidebar(); ?>
<?php get_footer(); // Loads the footer.php template. ?>