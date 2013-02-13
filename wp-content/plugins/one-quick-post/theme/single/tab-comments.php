<?php 

$withcomments = true; //I don't know why, but without this line, the comments template do not load


?>

    <h3 class="oqp-step-title"><?php _e('Comments');?></h3>
    <?php 
    
    $comments_template_file = false; //default
    $comments_template_file = apply_filters('oqp_comments_template_file',$comments_template_file);
    
    comments_template($comments_template_file);
    
    
    ?>

<?php

?>