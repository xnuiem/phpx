<?php
/*
Plugin Name: MonitorX
Plugin URI: http://www.thisrand.com/scripts/monitorx
Description: A plugin to monitor and manage mulitple Wordpress installations.  Includes uptime monitoring, plugin updates, version updates, cross logins (login once across all sites).
Version: 0.1
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
 * A SVN display plugin
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

define(MONITORX_DIR, WP_PLUGIN_DIR . '/monitorx/');  
define(MONITORX_URL, WP_PLUGIN_URL . '/monitorx/'); 


$options = get_option('monitorx_options');      
 
require_once(MONITORX_DIR . 'includes/monitorx_functions.php');

$obj = new monitorx_functions();
$obj->options = $options;
 
 if (substr_count($_SERVER["REQUEST_URI"], "wp-admin") != 0){           
    require_once(MONITORX_DIR . 'includes/monitorx_admin.php');
    $adminObj               = new monitorx_admin();
    $adminObj->options      = $options;    
    $adminObj->obj          = $obj;
    add_action('admin_menu', array($adminObj, 'monitorx_adminMenu')); 
    register_activation_hook(__FILE__, array($adminObj, 'monitorx_install'));
    register_deactivation_hook(__FILE__, array($adminObj, 'monitorx_uninstall'));
}




add_action('wp', array($obj, 'monitorx_init'));
  

//Check Versions
//Check Plugins
//MulitX Login
//Check Uptime

//Cron or Manual

?>