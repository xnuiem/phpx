=== MultiX ===
Contributors: Xnuiem
Donate link: http://www.thisrand.com/scripts/multix
Tags: automatic, integration, plugin, admin
Requires at least: 2.6
Tested up to: 3.2.1
Stable tag: 0.5

A lightweight script to allow for the seemless administration of multiple Wordpress websites that can reside on different servers and databases.

== Description ==

This plugin is a way for those of us that need to administer multiple Wordpress websites but don't have the luxury of having all those sites on the same server or database.  

== Installation ==

Note: This plugin, as of version 0.5, requires PHPX to be installed as well.

1. Upload the multix directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. In order to create a trust relationship between sites, MultiX must be installed on each instance of Wordpress.
4. Once MultiX is installed on each instance of Wordpress you wish to create a relationship with, click the "Add Site" link at the top of the MultiX admin screen (Tools->MultiX)
5. If you have cURL installed, you will see a form, "Automatically Add Site".  Use that form to add another site.  
6. If you do not have cURL installed, you will have to create the trust relationship manually be exchanging site keys between each site.  

== Frequently Asked Questions ==
<b>When I try to use MultiX to login to another site, I see the login screen on that site</b><br />
This usually means some part of the information is wrong.  Make sure the URI is an exact match.  The username must also match on each site.  Passwords, roles, settings, etc...can be different.  Just usernames must match.

== Change Log ==

<b>0.1</b><br />
Initial Version
<br />

<b>0.2</b><br />
Added Dashboard Widget<br />
Added Auto Add Site<br />
Started Migrating to the SuiteX Framework Model<br />
<br />

<b>0.3</b><br />
Fixed a bug with the confirmation window on "Generate New Key"<br />
<br />

<b>0.4</b><br />
Fixed a bug in the CSS scope<br />

<b>0.5</b><br />
Added Support for the PHPX Framework<br />




== Screenshots==

== Demos ==

== To Do ==



