<?php

class My_Autopost_Walker_Category extends Walker
{
    public $tree_type = 'category';
    public $db_fields = array('parent' => 'parent', 'id' => 'term_id');

    function start_lvl(&$output, $depth = 0, $args = array())
    {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent<ul class='children'>\n";
    }

    function end_lvl(&$output, $depth = 0, $args = array())
    {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent</ul>\n";
    }

    function start_el(&$output, $category, $depth = 0, $args = array(), $id = 0)
    {
        extract($args);
        if (empty($taxonomy))
            $taxonomy = 'category';

        if ($taxonomy == 'category')
            $name = 'post_category';
        else
            $name = 'post_category';

        $class = in_array( $category->term_id, $popular_cats ) ? ' class="popular-category"' : '';
        $output .= "\n<li id='{$taxonomy}-{$category->term_id}'$class>" . '<label class="selectit"><input value="' . $category->term_id . '" type="checkbox" name="'.$name.'[]" id="in-'.$taxonomy.'-' . $category->term_id . '"' . checked( in_array( $category->term_id, $selected_cats ), true, false ) . disabled( empty( $args['disabled'] ), false, false ) . ' /> ' . esc_html( apply_filters('the_category', $category->name )) . '</label>';
    }

    function end_el(&$output, $category, $depth = 0, $args = array())
    {
        $output .= "</li>\n";
    }
}