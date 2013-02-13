<?php
function oqp_search_menu_keywords(){
    do_action('oqp_search_menu_before_keywords');
    $s = get_search_query();
    if($s)$classes[]="selected";
    if($classes)$classes_str=' class="'.implode(' ',$classes).'"';
    ?>
    <div id="yclads_keywords">
        <label class="assistive-text" for="oqp_search_input"><?php _e('Keywords','oqp');?></label>
        <input<?php echo $classes_str;?> type="text" placeholder="<?php _e('Enter some keywords','oqp');?>" name="s" id="oqp_search_input" value="<?php echo $s;?>">   
    </div>
    <?php
    do_action('oqp_search_menu_after_keywords');
}
function oqp_search_menu_submit(){
    ?>
    <div id="oqp_search_button">
            <input type="submit" value="<?php _e('Search');?>"/>
    </div>
    <?php
}
add_action('oqp_search_menu_simple','oqp_search_menu_keywords');
add_action('oqp_search_menu_simple','oqp_search_menu_submit',20);
?>

<div id="oqp-search-menu">

    <form action="<?php oqp_form_page_get_link();?>" id="yclads_search" method="post" role="search">
        <?php do_action('oqp_before_search_form');?>
        <div id="simple-search">
            <?php do_action('oqp_search_menu_simple');?>
        </div>

        <div id="advanced-search">
            <?php do_action('oqp_search_menu_advanced');?>
        </div>
            <?php do_action('oqp_after_search_form');?>
    </form>

</div>

