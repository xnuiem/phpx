<?php
class spreadX_front {
    
    function spreadx_insert_buttons($content){
        /**
        * inserts the buttons
        *
        * @param NULL
        * @return string $text page content
        */
        global $post;
        
        $options = get_option('spreadx_options'); 
        $type = get_post_type($post->ID);
        
        if (in_array($type, $options["scope"])){
            $trans["::TITLE::"] = urlencode(the_title('', '', false));
            $trans["::URL::"]   = get_permalink();
        
            $buttons = strtr($options["buttons"], $trans); 
        }
        else { $buttons = ''; }
        
        return $content.$buttons;    
    }    
}
?>
