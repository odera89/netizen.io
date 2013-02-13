
<?php
/**
 * OQP Single Template
 *
 * The archive template is basically a placeholder for archives that don't have a template file. 
 * Ideally, all archives would be handled by a more appropriate template according to the current
 * page context.
 *
 * @package Hybrid
 * @subpackage Template
 */

get_header(); // Loads the header.php template. 
global $oqp_form;

?>

<div id="content" class="hfeed content">
    

    <?php get_template_part( 'loop-meta' ); // Loads the loop-meta.php template. ?>
    
    <?php do_action( 'oqp_before_content' ); // hybrid_before_content ?>

    
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

            <div id="oqp_post_<?php the_ID();?>" <?php post_class(); ?> rel="<?php the_ID();?>">
                <?php oqp_post_review();?>

                <?php if (oqp_form_is_several_steps()){;?>

                        <div id="oqp-post-tabs" class="item-list-tabs oqp-tabs" role="navigation">
                                <ul>
                                        <?php oqp_post_tabs();?>
                                </ul>
                        </div>
                <?php };?>
                <div class="clear"></div>
                <?php do_action( 'oqp_before_step');?>
                <?php
                    ?>
                    <div class="oqp-step-content">
                        <?php 

                        setup_requested_step();
                        
                        
                        $step_custom_template=oqp_get_step_custom_template();
                        if($step_custom_template){
                            load_template($oqp_form->step->template);
                        }else{

                            if ( $oqp_form->step->have_fields() ) {
                                while ( $oqp_form->step->have_fields() ) {

                                    $field = $oqp_form->step->the_field();
                                    $field->populate_postdata();
                                    
                                    $field_content = apply_filters('oqp_get_field',$field->get_field());

                                    //no content
                                    if($field->edit) {
                                        $field_edit = apply_filters('oqp_get_edit_field',$field->get_edit_field());
                                        if(!$field_edit)continue;
                                    }else{
                                        if(!$field_content)continue;
                                    }

                                    /////////////////////////////////////

                                    $fieldname = oqp_get_form_field_input_name();

                                    //classes
                                    $field_classes=$field->field_classes();
                                    $field_classes_str=' class="'.implode(" ",$field_classes).'"';


                                    ?>
                                    <div<?php echo $field_classes_str;?>>

                                        <?php do_action( 'oqp_before_field');?>                 
                                        <?php    
                                        if($field->edit) {
                                            ?>
                                            <div class="preview">
                                                <?php echo $field_content;?>
                                            </div>
                                            <div class="form">
                                                <?php echo $field_edit;?>
                                            </div>
                                        <?php
                                        }else{
                                            ?>
                                            <?php echo $field_content;?>
                                            <?php
                                        }
                                        ?>
                                        <?php do_action( 'oqp_after_field');?>   
                                    </div>
                                    <?php
                                    
                                }
                            }



                                    

                         
                        }

                        ?>
                    <?php do_action( 'oqp_after_step');?>
                    </div>
            </div>
    <?php endwhile; ?>

    <?php else : ?>
            <?php oqp_get_template_part('no-results', 'index' );?>
    <?php endif; ?>
    <?php do_action( 'oqp_after_content' ); // hybrid_after_content ?>
    </div><!-- .content .hfeed -->

<?php get_footer(); // Loads the footer.php template. ?>
