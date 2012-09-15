<?php
/*
Plugin Name: SpreadX
Plugin URI: http://www.thisrand.com/scripts/spreadx
Description: A very easy way to get your site onto Digg, Stumble, Del.icio.us, Slashdot, Twitter, Mixx, Dzone, Sphinn, Google, and Technorati.
Version: 1.1
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
 * A very easy way to get your site onto Digg, Stumble, Del.icio.us, Slashdot, Twitter, Mixx, Dzone, Sphinn, Google, and Technorati.
 * @package WordPress
 * @since 2.6
 */

if (!defined( 'WP_CONTENT_URL'))
      define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if (!defined( 'WP_CONTENT_DIR'))
      define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if (!defined( 'WP_PLUGIN_URL'))
      define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if (!defined( 'WP_PLUGIN_DIR'))
      define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

define(SPREADX_DIR, WP_PLUGIN_DIR . '/spreadx/');  
define(SPREADX_URL, WP_PLUGIN_URL . '/spreadx/');  
 
 

if (substr_count($_SERVER["REQUEST_URI"], "wp-admin") != 0){   
    $pages["digg"] = array("Digg", "http://digg.com/", "http://digg.com/submit?phase=2&url=::URL::");
    $pages["facebook"] = array("Facebook", "http://www.facebook.com", "http://www.facebook.com/share.php?u=::URL::");
    $pages["stumble"] = array("StumbleUpon", "http://www.stumbleupon.com", "http://www.stumbleupon.com/submit?url=::URL::&title=::TITLE::");
    $pages["technorati"] = array("Technorati", "http://www.technorati.com", "http://technorati.com/faves?add=::URL::");
    $pages["delicious"] = array("Deli.cio.us", "http://www.delicious.com", "http://del.icio.us/post?url=::URL::&title=::TITLE::"); 
    $pages["slashdot"] = array("Slashdot", "http://www.slashdot.org", "http://slashdot.org/submit.pl?url=::URL::");
    $pages["twitter"] = array("Twitter", "http://www.twitter.com", "http://www.twitter.com/home?status=::URL::");
    $pages["sphinn"] = array("Sphinn", "http://www.sphinn.com", "http://www.sphinn.com/submit.php?url=::URL::");
    $pages["mixx"] = array("Mixx", "http://www.mixx.com", "http://www.mixx.com/submit?page_url=::URL::");
    $pages["google"] = array("Google", "http://www.google.com", "http://www.google.com/bookmarks/mark?op=edit&bkmk=::URL::&title=::TITLE::");
    $pages["dzone"] = array("DZone", "http://www.dzone.com", "http://www.dzone.com/links/add.html?url=::URL::&title=::TITLE::");
    
    require_once(SPREADX_DIR . '/spreadx_functions.php');
    $obj = new spreadX();
    $obj->pages = $pages;
    $obj->pluginBase = $pluginBase;

    register_activation_hook(__FILE__, array($obj, 'spreadx_install'));
    register_deactivation_hook(__FILE__, array($obj, 'spreadx_uninstall'));

    add_action('admin_menu', array($obj, 'spreadx_admin_menu'));
}
else{
    require_once(SPREADX_DIR . '/spreadx_front.php');  
    $obj = new spreadX_front();
    add_filter('the_content', array($obj, 'spreadx_insert_buttons'));
}



?>
