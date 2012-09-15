<?php
/*
Plugin Name: bookX
Plugin URI: http://www.thisrand.com/scripts/bookx
Description: Creates a recommended book list for both a sidebar widget and page based solely on ISBN numbers.
Version: 1.7
Author: Xnuiem
Author URI: http://www.thisrand.com

*/

/*  Copyright 2010 Xnuiem  (email : scripts @T thisrand D07 com)

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
 * A recommended book plugin
 * @since 2.6
 */
if ( ! defined( 'WP_CONTENT_URL' ) )
      define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
      define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
      define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
      define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
      
define(BOOKX_DIR, WP_PLUGIN_DIR . '/bookx/');  
define(BOOKX_URL, WP_PLUGIN_URL . '/bookx/'); 

require_once(BOOKX_DIR . 'includes/bookx_var.php');
$var = new bookx_var();

require_once(BOOKX_DIR . 'includes/bookx_functions.php');  
$obj                    = new bookx_functions();
$obj->var               = $var;
add_action('wp', array($obj, 'bookx_init'));

if (substr_count($_SERVER["REQUEST_URI"], "wp-admin") != 0){  
    require_once(BOOKX_DIR . 'includes/bookx_admin.php');
    $adminObj               = new bookx_admin();
    $adminObj->var          = $var;
    add_action('admin_menu', array($adminObj, 'bookx_adminMenu')); 
    register_activation_hook(__FILE__, array($adminObj, 'bookx_install'));
    register_deactivation_hook(__FILE__, array($adminObj, 'bookx_uninstall'));
}

require_once(BOOKX_DIR . 'includes/bookx_widget.php');   
$widgetObj              = new bookx_widget();
$widgetObj->var         = $var;
add_action('widgets_init', array($widgetObj, 'bookx_widget_init'));   



?>
