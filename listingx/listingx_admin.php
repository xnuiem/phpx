<?php

class listingx_admin {

    function __construct(){
    	$this->getMessage();
        global $wpdb;

        $this->wpdb = $wpdb;

    }


    function listingx_install(){
	    /**
	    * Installs the plugin by creating the page and options
	    */

		if (!get_option('listingx_options')){
	        $options				= array();
	        $page                   = array();
        	$page['post_type']      = 'page';
        	$page['post_title']     = 'Projects';
        	$page['post_name']      = 'listingx';
        	$page['post_status']    = 'publish';
        	$page['comment_status'] = 'closed';
        	$page['post_content']   = 'This is your ListingX Top level page.  All projects will be sub pages underneath this page.';
        	$page_id = wp_insert_post($page);
        	$options['page_id'] = $page_id;

    	    update_option('listingx_options', $options);
    	    //default options
    	    //default categories
        }

    }

    function listingx_uninstall(){
    	/**
    	* Uninstalls the plugin by deleting the options and page
    	*/

    	delete_option('listingx_options');
    }

    function listingx_admin_menu(){
    	/**
    	* The hook for the admin menu
    	*/
        add_menu_page('ListingX', 'ListingX', 5, __FILE__, array($this, 'listingx_admin_page'));
        add_submenu_page(__FILE__, 'ListingX Settings', 'Settings', 5, 'lx_settings', array($this, 'listingx_settings'));
        add_submenu_page(__FILE__, 'ListingX Project Admin', 'Projects', 5, 'lx_projects', array($this, 'listingx_projects'));
        add_submenu_page(__FILE__, 'ListingX Category Admin', 'Categories', 5, 'lx_categories', array($this, 'listingx_categories'));
    }

    function listingx_projects(){
    	$pluginBase = 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'listingx';
    	require_once(ABSPATH . $pluginBase . DIRECTORY_SEPARATOR . 'listingx_projects.php');
    	$this->projects = new listingx_projects($this);

    }

    function listingx_categories(){
    	$pluginBase = 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'listingx';
    	require_once(ABSPATH . $pluginBase . DIRECTORY_SEPARATOR . 'listingx_categories.php');
    	$this->categories = new listingx_categories($this);
    }

    function stroke($text){
    	$body = "<script type='text/javascript' src='../wp-content/plugins/listingx/listingx.js'></script>";

    	$body .= $text;
    	print($body);

    }

    function listingx_settings(){
        clearstatcache();

        $options = get_option('listingx_options');
        if ($_POST['action'] == "update"){
        	$options["newReleaseText"] = $_POST["newReleaseText"];
        	$options["newProjectPageText"] = $_POST["newProjectPageText"];
        	$options["newProjectPostText"] = $_POST["newProjectPostText"];
        	update_option('listingx_options', $options);
        	$this->getMessage("sc");
        }

        $text .= "<div class=\"wrap\">";
        $text .= "<h2>ListingX - Settings</h2>";
        $text .= $this->message;

		$text .= "<div id=\"poststuff\" class=\"metabox-holder\">";
		$text .= "<div id=\"post-body\" class=\"has-sidebar\">";
		$text .= "<div id=\"post-body-content\" class=\"has-sidebar-content\">";
        $text .= "<div class=\"postbox\">";
        $text .= "<h3><label>ListingX Settings</label></h3>";
		$text .= "<div class=\"inside\">";
        $text .= "<form method=\"post\" action=\"\">";
        $text .= "<input type=\"hidden\" name=\"_wpnonce\" value=\"" . wp_create_nonce() . "\" />";
        $text .= "<input type=\"hidden\" name=\"action\" value=\"update\" />";

        $text .= "<table class=\"form-table\">";
        $text .= "<tr><td colspan=\"2\"><strong>Template Labels</strong><br />";
        $text .= "::NAME::";
        $text .= ", ::DESC::";
        $text .= ", ::OWNER::";
        $text .= ", ::USERS::";
        $text .= ", ::CATEGORIES::";
        $text .= ", ::ADDED::";
        $text .= ", ::MODIFIED::";
        $text .= ", ::URL::";
        $text .= ", ::DONATE::";
        $text .= ", ::RELEASES::";
        $text .= ", ::FILES::";
        $text .= ", ::DATE::";
        $text .= ", ::VERSION::";
        $text .= ", ::NOTES::";
        $text .= ", ::LOG::";
        $text .= ", ::PROJECTPAGE::";
        $text .= "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td valign=\"top\"><strong>Default Project Page:</strong>";
        $text .= "</td>";
        $text .= "<td><textarea name=\"newProjectPageText\">" . stripslashes($options["newProjectPageText"]) . "</textarea>";
        $text .= "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td valign=\"top\"><strong>New Project Post:</strong>";
        $text .= "</td>";
        $text .= "<td><textarea name=\"newProjectPostText\">" . stripslashes($options["newProjectPostText"]) . "</textarea>";
        $text .= "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td valign=\"top\"><strong>New Release:</strong>";
        $text .= "</td>";
        $text .= "<td><textarea name=\"newReleaseText\">" . stripslashes($options["newReleaseText"]) . "</textarea>";
        $text .= "</td></tr>";


        $text .= "</table>";
        $text .= "<p class=\"submit\"><input type=\"submit\" name=\"Submit\" value=\"Save Changes\" />";
        $text .= "</p></form>";
		$text .= "</div></div></div></div>";
		$text .= "</div></div>";
		$this->stroke($text);
    }

    function listingx_admin_page(){
    	/**
    	* Creates the Admin page
    	*/
        $dateFormat = get_option("date_format") . ", " . get_option("time_format");
        $pluginBase = 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'listingx';
    	require_once(ABSPATH . $pluginBase . DIRECTORY_SEPARATOR . 'listingx_list.php');
    	require_once(ABSPATH . $pluginBase . DIRECTORY_SEPARATOR . 'listingx_projects.php');
    	global $filter;

		$projectObj = new listingx_projects($this, false);

    	$nonce = wp_create_nonce();
    	$list            = new listingx_list();
    	$list->search    = false;
    	$list->orderForm = false;
    	$list->filters   = false;
    	$list->omit      = array("cb");

        $text .= "<div class=\"wrap\">";
        $text .= "<h2>ListingX</h2>";
		$text .= $this->parent->message;

		$headers["p.lx_project_name"]    = "Project Name";
		$headers["u.user_login"]         = "Added By";
		$headers["r.lx_release_version"] = "Approve";
		$headers["r.lx_release_date"]    = "Date";
		$headers["r.lx_release_notes"]   = "Notes";
		$headers["r.lx_release_log"]     = "Change Log";

		$order = "p.lx_project_name";
		$sort  = "asc";

     	$query  = "select p.lx_project_id as project_id, ";
     	$query .= "p.lx_project_name as project, ";
     	$query .= "u.user_login as username, ";
     	$query .= "r.lx_release_id as release_id, ";
     	$query .= "r.lx_release_date as releaseDate, ";
     	$query .= "r.lx_release_version as version, ";
     	$query .= "r.lx_release_notes as notes, ";
     	$query .= "r.lx_release_log as log ";
 		$query .= "from (" . $this->wpdb->prefix . "lx_release r,";
 		$query .= $this->wpdb->prefix . "lx_project p) ";
 		$query .= "left join " . $this->wpdb->prefix . "users u on u.ID = r.user_id ";
 		$query .= "where r.lx_release_approved = 0 and r.lx_project_id = p.lx_project_id ";
 		$query .= "and r.lx_release_public = 1 ";
 		$query .= "order by $order $sort";

   		$result = $this->wpdb->get_results($query);


     	foreach($result as $row){
       		$approved = "<a href=\"admin.php?page=lx_projects&action=release&releaseAction=approve&_wpnonce=$nonce&id=" . $row->release_id . "\">Approve</a>";
			$date = date($dateFormat, $row->releaseDate);
        	$rows[$row->project_id] = array($row->project, $row->username, $approved, $date, $row->version, $row->notes, $row->log);
     	}
        $url = "admin.php?page=lx_projects&action=view&id=";
        $list->startList($headers, $url, $order, $sort, $rows, array("page" => "lx_projects"));



    	$list1            = new listingx_list();
    	$list1->search    = false;
    	$list1->orderForm = false;
    	$list1->omit      = array("cb");

		$headers = array();
		$headers["p.lx_project_name"]     = "Project Name";
		$headers["u.user_login"]          = "Owner";
		$headers["c.lx_project_cat_name"] = "Categories";
		$headers["p.lx_project_approved"] = "Approved";

		$order = "p.lx_project_name";
		$sort  = "asc";

     	$query  = "select p.lx_project_id, p.lx_project_name, u.user_login, p.lx_project_approved from ";
     	$query .= $this->wpdb->prefix . "lx_project p left join " . $this->wpdb->prefix . "users u on u.ID = p.user_id ";
     	$query .= "where p.lx_project_approved = 0 order by $order $sort";

     	$result = $this->wpdb->get_results($query);
     	foreach($result as $row){
        	$approved = "<a href=\"admin.php?page=lx_projects&action=approve&_wpnonce=$nonce&id=" . $row->lx_project_id . "\">Approve</a>";
           	$categories = $projectObj->catForm("list", $row->lx_project_id);
        	$rows[$row->lx_project_id] = array($row->lx_project_name, $row->user_login, $categories, $approved);
     	}
        $url = "admin.php?page=lx_projects&action=view&id=";
        $list1->startList($headers, $url, $order, $sort, $rows, array("page" => "lx_projects"));

        $text .= $list->text . "<br /><br />" . $list1->text . "</div>";
        $this->stroke($text);

    }

    function getMessage($code=''){
		if ($_GET["code"]){ $code = $_GET["code"]; }
		if ($code != ''){
		    $codeArray["a"]   = "Project Added";
		    $codeArray["ap"]  = "Project Approved";
		    $codeArray["m"]   = "Project Modified";
		    $codeArray["d"]   = "Project Deleted";
		    $codeArray["sc"]  = "Settings Saved";
		    $codeArray["ca"]  = "Category Added";
		    $codeArray["cap"] = "Category Approved";
		    $codeArray["cm"]  = "Category Modified";
		    $codeArray["cd"]  = "Category Deleted";
		    $codeArray["ra"]  = "Release Added";
		   	$codeArray["rm"]  = "Release Modified";
		   	$codeArray["rd"]  = "Release Deleted";
		   	$codeArray["rap"] = "Release Approved";

		   	$this->message = "<br /><b><span style=\"color:#FF0000;\">" . $codeArray[$code] . "</span></b>";
		}
    }

    function pageDirect($url){
    	$text = "<script language=\"javascript\"> window.location = '$url'; </script>";
    	print($text);

    }


}

?>
