=== BookX ===
Contributors: Xnuiem
Donate link: http://www.thisrand.com
Tags: plugin, content
Requires at least: 2.6
Tested up to: 3.0.1
Stable tag: 1.7

A simple but powerful recommended book plugin.


== Description ==

BookX creates an easy way to give your site visitors a peek at your recommended books.  Using only ISBN numbers, it gets the
information from Barnes and Noble (http://www.bn.com) and stores the information locally to both speed up the response time, but
also to not bog down their servers with repetetive requests.  BookX creates a widget for your sidebar, a list view, and detail view, 
all customizable from the easy to use admin interface.

== Installation ==

1. Upload the bookx directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Under the Tools menu, go to the 'BookX' administration page 
4. Now all you need to do is start adding your book's ISBN numbers.

== Frequently Asked Questions ==
None Yet

== Change Log ==
<b>1.7</b><br />
Fixed export bugs (<a href="http://code.google.com/p/suitex/issues/detail?id=61">Issue 61</a>)<br />
Fixed require bug in suitex paging for list (<a href="http://code.google.com/p/suitex/issues/detail?id=62">Issue 62</a>)<br />

<b>1.6</b><br />
Fixed Book List admin view bug (<a href="http://code.google.com/p/suitex/issues/detail?id=58">Issue 58</a>)<br />
Fixed error adding book bug (<a href="http://code.google.com/p/suitex/issues/detail?id=59">Issue 59</a>)<br />

<b>1.5</b><br />
Fixed No Image on Details Bug (<a href="http://code.google.com/p/suitex/issues/detail?id=55">Issue 55</a>)<br />
Fixed ISBN fail roll over bug (<a href="http://code.google.com/p/suitex/issues/detail?id=57">Issue 57</a>)<br />
Fixed yet another division by 0 bug (<a href="http://code.google.com/p/suitex/issues/detail?id=56">Issue 56</a>)<br />

<b>1.4</b><br />
Fixed See Inside Image bug (<a href="http://code.google.com/p/suitex/issues/detail?id=53">Issue 53</a>)<br />
Added Open Library fetching<br />
Added the ability to fail over to the other fetch method if the default fails.<br />

<b>1.3</b><br />
Updated the fetch method for B&N's new page format (Caused problems on images) (<a href="http://code.google.com/p/suitex/issues/detail?id=52">Issue 52</a>)<br />

<b>1.2</b><br />
Fixed a bug with redirection after form submission<br />

<b>1.1</b><br />
Fixed a bug with overlapping variable scope with other SuiteX plugins.<br />

<b>1.0</b><br />
The description from BN can now be edited. (<a href="http://code.google.com/p/suitex/issues/detail?id=49">Issue 49</a>)<br />
Added a quick and dirty backup option (<a href="http://code.google.com/p/suitex/issues/detail?id=38">Issue 38</a>)<br />
Also made a few changes to the way the files were laid out.<br />

<b>0.6</b><br />
Fixed bug for missing external link (<a href="http://code.google.com/p/suitex/issues/detail?id=45">Issue #45</a>)<br />     

<b>0.5</b><br />
Fixed bug when changing page slug (<a href="http://code.google.com/p/suitex/issues/detail?id=45">Issue #45</a>)<br />

<b>0.4</b><br />

Multiple Book Add (<a href="http://code.google.com/p/suitex/issues/detail?id=39">#39</a>)<br />
Added better error handling<br />

<b>0.3</b><br />
Fixed minor bug with naming.<br />
Cleaned up path and url references<br />
Verified ready for 2.8<br />

<b>0.2</b><br />
Fixed Delete Book<br />
Added error handling for when the ISBN fails to return an object.  Thanks garron.rose<br />

<b>0.1</b><br />
Initial Release


== Screenshots==
None Yet


== Demos ==
[this.rand()](http://www.thisrand.com/booklist) <br />

