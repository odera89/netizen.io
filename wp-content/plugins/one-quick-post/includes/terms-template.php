<?php

class Oqp_Walker_Category extends Walker {
    
    
    var $tree_type = 'category';
    var $db_fields = array ('parent' => 'parent', 'id' => 'term_id');
    function start_lvl(&$output, $depth, $args) {

        
        if ( 'list' != $args['style'] )
            return;

        $indent = str_repeat("\t", $depth);
        $output .= "$indent<ul class='children'>\n";
    }
    function end_lvl(&$output, $depth, $args) {
        if ( 'list' != $args['style'] )
            return;

        $indent = str_repeat("\t", $depth);
        $output .= "$indent</ul>\n";
    }
    function start_el(&$output, $category, $depth, $args) {
        
        $default_args=array(
            'link'=>true,
            'input_type'=>false,
            'input_name'=>$args['taxonomy'],
            'input_field_value'=>'term_id' //term_id,slug,name
        );
        
        
        $args = wp_parse_args( $args,$default_args);

        $selected=array();
        extract($args);


        $cat_name = esc_attr( $category->name );
        $cat_name = apply_filters( 'list_cats', $cat_name, $category );
        
        $id = $taxonomy.'-' . $category->term_id;


        if($input_type){
            $form_value = $category->$input_field_value;
            $input_name.='[]'; 
      
            
            $form_selected = in_array( $category->$input_field_value, $selected );
            if($input_type=='checkbox'){
                $before.= '<input id="'.$id.'" value="' . $form_value . '" type="checkbox" name="'.$input_name.'" ' . checked($form_selected, true, false ) . ' />';
            }elseif($input_type=='radio'){
                $before.= '<input id="'.$id.'" value="' . $form_value . '" type="radio" name="'.$input_name.'" ' . checked($form_selected, true, false ) . ' />';
            }
            
                $before.=' <label style="font-size:1em" for="'.$id.'" class="select_lbl_class">';
                $after.='</label>';
        }
        
        if($link){
            $cat_name='<a href="">'.$cat_name.'</a>';
        }
        
        $link=$before.$cat_name.$after;
        

        

        $classes[]= $id;
        $classes[]= $taxonomy;
        if ( in_array( $category->slug, $selected ) )$classes[]=" selected";
        
        if($classes)$class_str=  ' class="' . implode(' ',$classes) . '"';
        
        if ( 'list' == $args['style'] ) 
        {
            $output .= "\t<li";

            $output .=  $class_str;
            $output .= ">$link\n";
        } 
        else 
        {
            $output .= "\t<span".$class_str.">".$link."</span>\n";
        }
    }
    function end_el(&$output, $page, $depth, $args) {
        
        if ( 'list' != $args['style'] )
            return;

        $output .= "</li>\n";
    }

}


?>