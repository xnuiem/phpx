<?php
/*
Plugin Name: USecureX
Plugin URI: http://www.thisrand.com/scripts/usecurex
Description: An easy to use script that allows you to create user groups and then manage their acess to specific pages within your website.     
Version: 0.2
Author: Xnuiem
Author URI: http://www.thisrand.com

*/

/*  Copyright 2009 Xnuiem  (email : scripts @T thisrand D07 com)

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
 * @package WordPress
 * @since 2.6
 */
$pluginBase = 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'usecurex';

if (substr_count($_SERVER["REQUEST_URI"], "wp-admin") != 0){

    require_once(ABSPATH . $pluginBase . DIRECTORY_SEPARATOR . 'usecurex_functions.php');

    $obj = new usecurex();
    $obj->baseURL = "tools.php?page=usecurex/usecurex_functions.php";
    $obj->pluginBase = $pluginBase;

    register_activation_hook(__FILE__, array($obj, 'usecurex_install'));
    register_deactivation_hook(__FILE__, array($obj, 'usecurex_uninstall'));

    add_action('admin_menu', array($obj, 'usecurex_admin_menu'));
}
else {
    require_once(ABSPATH . $pluginBase . DIRECTORY_SEPARATOR . 'usecurex_front.php');
    $obj = new usecurex_front();
    add_action('wp', array($obj, 'usecurex_front_init'));
}





?>
