<?php

/**
* The administration functions for CrowdX
* 
* @param    object  $wpdb
* @param    array   $options
* @param    string  $baseURL
* @param    string  $pluginURL
* @param    string  $numberPerPage
* @param    array   $bookArray
* @param    string  $status
* @param    array   $filter
*/
  
class crowdx_admin {
    
    var $version        = "0.1";
    var $wpdb;

    var $baseURL        = "tools.php?page=crowdx/includes/crowdx_admin.php";

    
    /**
    * The contstruct function.  Does nothing other than set up variables.
    *
    * @global object wpdb
    */

    function __construct(){
        global $wpdb;
        $this->wpdb    = $wpdb;
    }
    
    function crowdx_upgrade(){

        if ($this->var->options["version"] != $this->version){
            require_once(CROWDX_DIR . "includes/crowdx_upgrade.php");           
            foreach($upgradeArray[$this->var->options["version"]] as $sql){
                $this->wpdb->query($sql);
            }
            update_option('crowdx_options', $this->var->options);
        }
    }

    /**
    * Installs the plugin by creating the page and options
    *
    * @param NULL
    * @return NULL
    */

    function crowdx_install(){
        if (!is_plugin_active('phpx/phpx.php')){
            die('CrowdX requires the <a href="http://www.phpx.org/" target="_new">PHPX Framework</a>.  Please install PHPX and then reinstall CrowdX.');
        }
                
        if (!get_option('crowdx_options')){
            //$sql = 'CREATE TABLE IF NOT EXISTS `' . $this->wpdb->prefix . '_cx_user` (  `wp_user_id` int(10) NOT NULL,  `cx_user_active` tinyint(1) NOT NULL DEFAULT \'0\',  UNIQUE KEY `wp_user_id` (wp_user_id`),  KEY `cx_user_active` (`cx_user_active`)) ENGINE=MyISAM DEFAULT CHARSET=latin1;';
            //$this->wpdb->query($sql);
        
            $options = array();
            $options['enable'] = false;
            $options['all_users'] = false;
            $options['server'] = 'http://yourserver';
            
            
            update_option('crowdx_options', $options);
        }
    }

    /**
    * Uninstalls the plugin by deleting the options and page
    */

    function crowdx_uninstall(){

    }
    
    function crowdx_run(){
        if ($_POST['submit']){
            
            if (!wp_verify_nonce($_POST["_wpnonce"])){ die('Security check'); }   
            $omit = array('_wpnonce', 'submit');
            
            foreach(array_keys($_POST) as $p){
                if (!in_array($p, $omit)){
                    $this->options[$p] = $_POST[$p];
                }
                
            }
            update_option('crowdx_options', $this->options);
            $status = 'Options Updated';
        }
        
        $userRoleArray = array();
        $wp_roles = new WP_Roles();
        foreach ($wp_roles->role_names as $role => $name){        
            $userRoleArray[$role] = $name;    
        }
        
        
        require_once(PHPX_DIR . 'phpx_page.php');
    	require_once(PHPX_DIR . 'phpx_form.php');
    	$page = new phpx_page();
        $form = new phpx_form();
        $form->startForm($this->baseURL, "crowdxForm");
        $form->hidden("_wpnonce", wp_create_nonce());
        $form->dropDown('Enabled', 'enable', $this->options['enable'], array('Off', 'On'));
        $form->dropDown('All Users (Internal and Crowd)', 'all_users', $this->options['all_users'], array('Off', 'On'));
        $form->dropDown('Auto Add New Users from Crowd', 'add_users', $this->options['add_users'], array('Off', 'On'));
        $form->dropDown('User Role for Added Users', 'default_user_role', $this->options['default_user_role'], $userRoleArray);
        $form->dropDown('Fallback to Internal Database', 'fallback', $this->options['fallback'], array('Off', 'On'));
        $form->textField('URL to Crowd Server', 'server', $this->options['server']);
        $form->textField('Application Name', 'app_name', $this->options['app_name'], true);
        $form->textField('Application Password', 'app_pass', $this->options['app_pass'], true);
        
        $form->endForm('Submit');

        $text = $page->startPage('CrowdX Configuration', $status) . $form->text . $page->endPage();
        
        print($text);
    }
    
    function crowdx_adminMenu(){
        add_management_page('CrowdX', 'CrowdX', 10, __FILE__, array($this, 'crowdx_run')); 
    } 
}
?>