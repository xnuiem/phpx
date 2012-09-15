<?php
class adBarX {
    /**
    * The functions for AdBarX
    * @global   array   $options
    */
    
    function __construct(){
        global $wpdb;
        $this->wpdb = $wpdb;
        
        $this->options = get_option('adbarx_options');

    }

    function adbarx_install(){
        update_option('adbarx_options', array('remember' => 1, 'cookie' => 'abx_234alk3469ohaelf34986hol', 'content' => ''));
                
        if (!is_plugin_active('phpx/phpx.php')){
            die('AdBarX requires the PHPX Framework.  Please install PHPX and then reinstall AdBarX.');
        }
    }
    
    function adbarx_uninstall(){
        delete_option('adbarx_options');
    }
    
    function adbarx_showEditor() {
    
        wp_enqueue_script( 'common' );
        wp_enqueue_script( 'jquery-color' );
        wp_print_scripts('editor');
        if (function_exists('add_thickbox')) add_thickbox();
        wp_print_scripts('media-upload');
        if (function_exists('wp_tiny_mce')) wp_tiny_mce();
        wp_admin_css();
        wp_enqueue_script('utils');
        do_action("admin_print_styles-post-php");
        do_action('admin_print_styles');
    }    
    
    function adbarx_admin(){
        if ($_POST['nonce']){
            if (!wp_verify_nonce($_POST['nonce'], 'adbarx_admin')){
                die('Invalid Security Token');
            }
            $this->options['remember'] = ($_POST['showOnce'] == 'on') ? 1 : 0;
            $this->options['content'] = $_POST['content'];
            $this->options['title'] = $_POST['title'];
            
            if ($_POST['resetViews'] == 'on'){
                $this->options['cookie'] = 'adx_' . substr(md5(microtime()), 5, 20);
            }
            update_option('adbarx_options', $this->options);
        }
        
        add_filter('admin_head', array($this, 'adbarx_showEditor'));
        require_once(PHPX_DIR . 'phpx_form.php');
        $form = new phpx_form();
        $form->instantReturn = true;
        
        $text = '<div class="wrap"><h2>Ad Bar X</h2>';
        $text .= $form->startForm('themes.php?page=adbarx/includes/adbarx_functions.php', 'adbarxForm');        
        $text .= $form->hidden('nonce', wp_create_nonce('adbarx_admin'));
        print($text);
        
        the_editor(stripslashes($this->options['content']), 'content');
        $text = '<br /><br />';
        $text .= $form->textField('Bar Title', 'title', $this->options['title']);
        $text .= $form->checkBox('Show Adbar Once', 'showOnce', 1);
        $text .= $form->checkBox('Reset All Views', 'resetViews', 0);
        $text .= $form->endForm();
        
        
        
        $text .= '</div>';
        print($text);
    }   
    
    function adbarx_adminMenu(){
        add_theme_page('AdBarX', 'AdBarX', 2, __FILE__, array($this, 'adbarx_admin')); 
    }
    
    

    
    function adbarx_addContent(){
        $content = do_shortcode(stripslashes($this->options['content']));
        $text = '<div id="adbarxTitle"><p>' . $this->options['title'] . '</p></div>';
        $text .= '<div id="adbarxContent"><p>' . $content . '</p></div>';
        if ($this->showAd == true){
            $text .= '<script>jQuery(function(){
            
                jQuery("#adbarxContent").slideDown();
            });</script>';
        }
        print($text);  
        
    }
    
    function adbarx_addCSS(){
        print("<link rel='stylesheet' href='" . ADBARX_URL . "css/adbarx.css' type='text/css' media='all' />");      
    }  
    
    function adbarx_addCookie(){
        if (!$_COOKIE[$this->options['cookie']]){
            $this->showAd = true;
            setcookie($this->options['cookie'], 'k4kak4alkjhargl3o0a', time() + (86400*365), COOKIEPATH, COOKIE_DOMAIN);
        }          
    }
    
    
    
    
}
        
        

?>
