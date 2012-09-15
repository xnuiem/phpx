<?php
/*
Plugin Name: CrowdX
Plugin URI: http://www.thisrand.com/scripts/crowdx
Description: A plugin to connect Wordpress to Atlassian Crowd
Version: 0.1
Author: Xnuiem
Author URI: http://www.thisrand.com

*/

/*  Copyright 2011 Xnuiem  (email : scripts @T thisrand D07 com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * A plugin to connect Wordpress to Atlassian Crowd
 * @since 2.6
 */
if (!defined('WP_CONTENT_URL')){ define('WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' ); }
if (!defined('WP_CONTENT_DIR')){ define('WP_CONTENT_DIR', ABSPATH . 'wp-content' ); }
if (!defined('WP_PLUGIN_URL')){  define('WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' ); }
if (!defined('WP_PLUGIN_DIR')){  define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' ); }
      
define(CROWDX_DIR, WP_PLUGIN_DIR . '/crowdx/');  
define(CROWDX_URL, WP_PLUGIN_URL . '/crowdx/'); 

$crowdxOptions   = get_option('crowdx_options');   


require_once(CROWDX_DIR . 'includes/crowdx_functions.php');  
$obj                    = new crowdx_functions();
$obj->options           = $crowdxOptions;




if ($crowdxOptions['enable'] == 1){
	add_action('wp_authenticate', array($obj, 'crowdx_login'));	
}

if (substr_count($_SERVER["REQUEST_URI"], "wp-admin") != 0){  
    require_once(CROWDX_DIR . 'includes/crowdx_admin.php');
    $adminObj               = new crowdx_admin();
    $adminObj->options           = $crowdxOptions;
    add_action('admin_menu', array($adminObj, 'crowdx_adminMenu')); 
    register_activation_hook(__FILE__, array($adminObj, 'crowdx_install'));
    register_deactivation_hook(__FILE__, array($adminObj, 'crowdx_uninstall'));
}

?>
