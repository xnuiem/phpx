<?php
/*
Plugin Name: ThemeX
Plugin URI: http://www.thisrand.com/scripts/themex
Description:  A lightweight plugin that will allow the automatic rotation of a pair of themes based on the time of day.
Version: 0.5
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
 * A lightweight plugin that will allow the automatic rotation of a pair of themes based on the time of day.
 * @package WordPress
 * @since 2.6
 */


$pluginBase = 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'themex';
require_once(ABSPATH . $pluginBase . DIRECTORY_SEPARATOR . 'themex_functions.php');
require_once(ABSPATH . $pluginBase . DIRECTORY_SEPARATOR . 'themex_admin.php');

$tObj = new themex_functions();
$aObj = new themex_admin();

$tObj->debug = false;

register_activation_hook(__FILE__, array($aObj, 'themex_install'));
register_deactivation_hook(__FILE__, array($aObj, 'themex_uninstall'));

add_action('admin_menu', array($aObj, 'themex_admin_menu'));
add_action('init', array($tObj, 'checkTheme'));





?>
