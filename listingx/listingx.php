<?php
/*
Plugin Name: listingx
Plugin URI: http://www.thisrand.com/scripts/listingx
Description:
Version: 0.1
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


$pluginBase = 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'listingx';
require_once(ABSPATH . $pluginBase . DIRECTORY_SEPARATOR . 'listingx_functions.php');
require_once(ABSPATH . $pluginBase . DIRECTORY_SEPARATOR . 'listingx_admin.php');
require_once(ABSPATH . $pluginBase . DIRECTORY_SEPARATOR . 'listingx_front.php');

$aObj = new listingx_admin();
$fObj = new listingx_front();

register_activation_hook(__FILE__, array($aObj, 'listingx_install'));
register_deactivation_hook(__FILE__, array($aObj, 'listingx_uninstall'));

add_action('admin_menu', array($aObj, 'listingx_admin_menu'));


//add_action('wp', array($fObj, 'listingx_getFile'));
add_action('wp', array($fObj, 'listingx_run'));






/* Vars */
$filter = array("No", "Yes");









?>
