<?php
/*
Plugin Name: WineX
Plugin URI: http://www.thisrand.com/scripts/winex
Description: A lightwieght script used to display the contents of your CellarTracker cellar on your website.
Version: 1.1
Author: Xnuiem
Author URI: http://www.thisrand.com

*/

/*  Copyright 2008 Xnuiem  (email : scripts @T thisrand D07 com)

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
 * A lightwieght script used to display the contents of your CellarTracker cellar on your website.
 * @package WordPress
 * @since 2.6
 */

ini_set('allow_url_fopen', 1);

$pluginBase = 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'winex';
require_once(ABSPATH . $pluginBase . DIRECTORY_SEPARATOR . 'winex_functions.php');

$wObj = new wineX();

add_filter('the_content', array($wObj, 'winex_showWineList'));

register_activation_hook(__FILE__, array($wObj, 'winex_install'));
register_deactivation_hook(__FILE__, array($wObj, 'winex_uninstall'));

add_action('admin_menu', array($wObj, 'winex_admin_menu'));





?>
