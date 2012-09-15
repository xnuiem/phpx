<?php
class menuX {
    /**
    * The functions for menuX
    * @global   array   $options
    */
    
    function __construct(){
        global $wpdb;
        $this->wpdb = $wpdb;
        
        $this->options = get_option('menux_options');

    }

    function menux_install(){
        update_option('menux_options', array('pages' => array()));
                
        if (!is_plugin_active('phpx/phpx.php')){
            die('MultiX requires the PHPX Framework.  Please install PHPX and then reinstall MenuX.');
        }
    }
    
    function menux_uninstall(){
        delete_option('menux_options');
    }
    
    
    function menux_createPostType(){
        $labels = array(
            'name' => _x('MenuX', 'post type general name'),
            'singular_name' => _x('MenuX Item', 'post type singular name'),
            'add_new' => _x('Add New', 'book'),
            'add_new_item' => __('Add New MenuX Item'),
            'edit_item' => __('Edit MenuX Item'),
            'new_item' => __('New MenuX Item'),
            'all_items' => __('All MenuX Items'),
            'view_item' => __('View MenuX Item'),
            'search_items' => __('Search MenuX Items'),
            'not_found' =>  __('No MenuX items found'),
            'not_found_in_trash' => __('No MenuX items found in Trash'), 
            'parent_item_colon' => '',
            'menu_name' => 'MenuX'
        );
        
        $args = array(
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true, 
            'show_in_menu' => true, 
            'query_var' => true,
            'rewrite' => false,
            'capability_type' => 'page',
            'has_archive' => false, 
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title','editor')
        ); 
        register_post_type('menux', $args);
    }
    
    function menux_message($messages){
        $messages['menux'][0] = '';
        $messages['menux'][1] = 'MenuX Item Updated';
        $messages['menux'][2] = 'Custom Field Updated';
        $messages['menux'][3] = 'Custome Field Updated';
        $messages['menux'][4] = 'MenuX Item Updated';
        $messages['menux'][5] = 'Revision Restored';
        $messages['menux'][6] = 'MenuX Item Published';
        $messages['menux'][7] = 'MenuX Item Saved';
        $messages['menux'][8] = 'MenuX Item Submitted';
        $messages['menux'][9] = 'MenuX Item Scheduled';
        $messages['menux'][10] = 'MenuX Draft Updated';
        return $messages;
    }
    
    function menux_prepAdmin(){
        if (function_exists('add_meta_box')){
            if (function_exists('get_post_types')){        
                add_meta_box('menuxContainer', 'Menu Item', array($this, 'menux_pageMetaAdd'), 'menux', 'side'); 
            }
        }         
    }
    
    function menux_pageMetaAdd(){
        
        global $post;
        $value = '';
        if ($post->ID){
            if (in_array($post->ID, $this->options['pages'])){
                $flip = array_flip($this->options['pages']);
                $value = $flip[$post->ID];
            }    
        }
        $menu = wp_get_nav_menu_object('MenuX');
        $items = wp_get_nav_menu_items($menu->term_id);
        
        foreach((array) $items as $key => $menu_item){
            $postArray[$menu_item->ID] = $menu_item->title;            
        }
        require_once(PHPX_DIR . 'phpx_form.php');
        
        $form = new phpx_form();
        $form->fieldsOnly = true;
        $form->dropDown('Menu Item', 'menux_menu_item', $value, $postArray, true);
        print($form->text);  

    }
    
    function menux_savePost(){
        if ($_POST['menux_menu_item'] != ''){
            foreach($this->options['pages'] as $key => $value){
                if ($value == $_POST['ID']){
                    unset($this->options['pages'][$key]);
                }
            }
            $this->options['pages'][$_POST['menux_menu_item']] = $_POST['ID'];
            update_option('menux_options', $this->options);
        }
    }

    function menux_addContent(){
        
        foreach($this->options['pages'] as $key => $value){
            $info = get_post($value);
            $content = do_shortcode($info->post_content);
            $divs .= '<div id="menux-' . $key . '" class="menuxItem">' . $content . '</div>';
            $scripts .= 'jQuery(\'#menu-item-' . $key . '\').hover(function(){
                jQuery(\'#menux-' . $key . '\').show();
            });';
        }
        
        $text = '
            <script>
            jQuery(document).ready(function() {
                jQuery(\'#navigation\').html(jQuery(\'#navigation\').html() + \'<div id="menuxContainer">' .  preg_replace( '/\s+/', ' ', $divs ) . '</div>\');     
                jQuery(\'#content\').hover(function(){
                    jQuery(\'.menuxItem\').hide();
                });
                jQuery(\'.wrapper\').hover(function(){
                    jQuery(\'.menuxItem\').hide();
                });                
                jQuery(\'.menu-item\').hover(function(){
                    jQuery(\'.menuxItem\').hide();
                });
                ' . $scripts . '
                
                
                
            });</script>
        ';
        print($text);
        
/*
        $text = '<div class="menu-menux-container">
                    <ul id="menu-menux" class="menu">';
        
        $items = wp_get_nav_menu_items('MenuX');
        foreach($items as $item){
            $info = get_post($this->options['pages'][$item->ID]);
            $text .= '<li id="menu-item-' . $item->ID . '" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-' . $item->ID . '">
                <a href="#" title="' . $item->post_title . '">' . $item->post_title . '</a>
                    <ul>
                        <li>
                               ' . $info->post_content . '
                        </li>
                    </ul>
                </li>';    
        }

        $text .= '</ul></div>';
        return $text;*/        
    }
    
    function menux_addCSS(){
        print("<link rel='stylesheet' href='" . MENUX_URL . "css/menux.css' type='text/css' media='all' />");      
    }
    
    
}
        
        

?>
