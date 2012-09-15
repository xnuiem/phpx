<?php
/*
Plugin Name: svnX
Plugin URI: http://www.thisrand.com/scripts/svnx
Description: A plugin to show the contents of Subversion Repositories within your WP website.  Uses websvn as a base.
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

define(SVNX_DIR, WP_PLUGIN_DIR . '/svnx/');  
define(SVNX_URL, WP_PLUGIN_URL . '/svnx/'); 


$options = get_option('svnx_options');      
 
require_once(SVNX_DIR . 'includes/svnx_functions.php');
 
 if (substr_count($_SERVER["REQUEST_URI"], "wp-admin") != 0){           
    require_once(SVNX_DIR . 'includes/svnx_admin.php');
    $adminObj               = new svnx_admin();
    $adminObj->options      = $options;    
    add_action('admin_menu', array($adminObj, 'svnx_adminMenu')); 
    register_activation_hook(__FILE__, array($adminObj, 'svnx_install'));
    register_deactivation_hook(__FILE__, array($adminObj, 'svnx_uninstall'));
}

$obj = new svnx_functions();
$obj->options = $options;


add_action('wp', array($obj, 'svnx_init'));



?>