<?php
/*
Plugin Name: AdBarX
Plugin URI: http://www.phpx.org
Description: 
Version: 0.1
Author: Xnuiem
Author URI: http://www.thisrand.com

*/

/*  Copyright 2009-2012 Xnuiem  (email : scripts @T thisrand D07 com)

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
 * 
 * @since 2.6
 */

 
 
 
if (!defined('WP_CONTENT_URL')){ define('WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' ); }
if (!defined('WP_CONTENT_DIR')){ define('WP_CONTENT_DIR', ABSPATH . 'wp-content' ); }
if (!defined('WP_PLUGIN_URL')){ define('WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' ); }
if (!defined('WP_PLUGIN_DIR')){ define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' ); }
      
define(ADBARX_DIR, WP_PLUGIN_DIR . '/adbarx/');  
define(ADBARX_URL, WP_PLUGIN_URL . '/adbarx/'); 
require_once(ADBARX_DIR . 'includes/adbarx_functions.php');



$adBarXobj = new adBarX(); 

register_activation_hook(__FILE__, array($adBarXobj, 'adbarx_install'));
register_deactivation_hook(__FILE__, array($adBarXobj, 'adbarx_uninstall'));

if (!is_admin()){
    wp_enqueue_script('adbarx_js', ADBARX_URL . 'js/adbarx.js', array(), false, true);
    add_action('wp_head', array($adBarXobj, 'adbarx_addCSS'));
    
    add_action('init', array($adBarXobj, 'adbarx_addCookie'));
    
    
}
else {
    
    add_action('admin_menu', array($adBarXobj, 'adbarx_adminMenu')); 
}


function adbarx_get_content(){
    global $adBarXobj;
    print($adBarXobj->adbarx_addContent());
}













?>
