<?php
/*
Plugin Name: MenuX
Plugin URI: http://www.phpx.org
Description: 
Version: 0.1
Author: Xnuiem
Author URI: http://www.thisrand.com

*/

/*  Copyright 2009-2011 Xnuiem  (email : scripts @T thisrand D07 com)

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
      
define(MENUX_DIR, WP_PLUGIN_DIR . '/menux/');  
define(MENUX_URL, WP_PLUGIN_URL . '/menux/'); 
require_once(MENUX_DIR . 'includes/menux_functions.php');

$menuXobj = new menuX(); 

register_activation_hook(__FILE__, array($menuXobj, 'menux_install'));
register_deactivation_hook(__FILE__, array($menuXobj, 'menux_uninstall'));

if (!is_admin()){
    wp_enqueue_script('menux_js', MENUX_URL . 'js/menux.js', array(), false, true);
    //add_action('wp_head', array($menuXobj, 'menux_addCSS'));
    add_action('wp_footer', array($menuXobj, 'menux_addContent'));
    
    
}
else {
    add_action('init', array($menuXobj, 'menux_createPostType'));
    add_action('admin_menu', array($menuXobj, 'menux_prepAdmin'));
    add_filter('post_updated_messages', array($menuXobj, 'menux_message'));
    add_action('save_post', array($menuXobj, 'menux_savePost'));    
}


function menux_get_content(){
    global $menuXobj;
    print($menuXobj->menux_addContent());
}











?>
