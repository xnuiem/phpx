<?php

class listingx_releases {
	/**
    * The front-end methods for listingX.
    * @package WordPress
    */
    var $parent;
 	var $wpdb;

    function __construct($parent){
    	$this->wpdb   = $parent->wpdb;
        $this->parent = $parent;
        $this->options = get_option('listingx_options');

    }

    function run(){

 	    switch($_GET["releaseAction"]){
	   	    case "form":
    	       	$this->releaseForm();
                break;

            case "add":
            case "modify":
            case "approve":
            case "delete":
                $this->submitForm();
                break;

    	}
        $this->parent->text = $this->text;
    }

    function listReleases($project_id){
    	$pluginBase = 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'listingx';
    	require_once(ABSPATH . $pluginBase . DIRECTORY_SEPARATOR . 'listingx_list.php');
    	global $filter;

    	$list            = new listingx_list();
    	$list->search    = false;
    	$list->orderForm = false;
    	$list->omit      = array("cb");
    	$list->fold      = true;

    	$rows = array();

        $headers["r.lx_release_version"]  = "Version";
        $headers["u.user_login"]          = "Owner";
        $headers["r.lx_release_notes"]    = "Notes";
        $headers["r.lx_release_log"]      = "Log";
        $headers["r.lx_release_public"]   = "Public";
        $headers["r.lx_release_approved"] = "Approved";

        $query = "select r.lx_release_version as version, ";
        $query .= "r.lx_release_id as id, ";
        $query .= "u.user_login as owner, ";
        $query .= "r.lx_release_notes as notes, ";
        $query .= "r.lx_release_log as log, ";
        $query .= "r.lx_release_public as public, ";
        $query .= "r.lx_release_approved as approved ";
        $query .= "from " . $this->wpdb->prefix . "lx_release r ";
        $query .= "left join " . $this->wpdb->prefix . "users u on u.ID = r.user_id ";
        $query .= "where r.lx_project_id = '$project_id' order by r.lx_release_date asc";

    	$result = $this->wpdb->get_results($query);

    	foreach($result as $row){
            $approved = $filter[$row->approved];
            $public   = $filter[$row->public];
            $rows[$row->id] = array($row->version, $row->owner, $row->notes, $row->log, $public, $approved);

            $query = "select lx_file_id as id, lx_file_name as name, lx_file_size as size, ";
            $query .= "lx_file_type as type, lx_file_download as download from " . $this->wpdb->prefix . "lx_file where ";
            $query .= "lx_release_id = '" . $row->id . "'";
            $result1 = $this->wpdb->get_results($query);

            $s = array();
            foreach($result1 as $r){
            	$s[] = array("id" => $r->id, "name" => $r->name, "size" => $r->size, "type" => $r->type, "download" => $r->download);
            }
            $rows[$row->id]["sub"] = $s;
    	}
    	$url = "admin.php?page=lx_projects&action=release&releaseAction=form&id=";
    	$list->startList($headers, $url, '', '', $rows, array("page" => "lx_projects"));
    	$text .= $list->text . "</div>";

        return $text;

    }

    function submitForm(){
        if ($_POST["_wpnonce"]){ $nonce = $_POST["_wpnonce"]; }
	        else if ($_GET["_wpnonce"]){ $nonce = $_GET["_wpnonce"]; }

	    if (!wp_verify_nonce($nonce)){ die('Security check'); }

	    if ($_GET["releaseAction"] == "approve"){
	        $q = "select p.lx_project_id as project_id, ";
        	$q .= "p.lx_project_page_id as page_id, ";
        	$q .= "p.lx_project_desc as project_desc, ";
        	$q .= "p.lx_project_name as name, ";
        	$q .= "r.lx_release_version as version, ";
        	$q .= "r.lx_release_public as public, ";
        	$q .= "r.lx_release_log as log, ";
        	$q .= "r.user_id as user ";
        	$q .= "from  " . $this->wpdb->prefix . "lx_project p, " . $this->wpdb->prefix . "lx_release r where r.lx_project_id = p.lx_project_id and r.lx_release_id = %d";
        	$row = $this->wpdb->get_row($this->wpdb->prepare($q, $_GET["id"]));

        	$link = $this->wpdb->get_var("select guid from " . $this->wpdb->prefix . "posts where ID = '" . $row->page_id . "' limit 1");
        	$link = "<a href=\"" . $link . "\">Project Homepage</a>";

            $q = "update " . $this->wpdb->prefix . "lx_release set lx_release_approved = 1 where lx_release_id = %d limit 1";
            $this->wpdb->query($this->wpdb->prepare($q, $_GET["id"]));

            if ($row->public == 1){
            	$body = $this->options["newReleaseText"];
                $body = str_replace("::PROJECTPAGE::", $link, $body);
                $body = str_replace("::DESC::", $row->project_desc, $body);
                $body = str_replace("::LOG::", $row->log, $body);
                $body = str_replace("::CATEGORIES::", $this->parent->catForm("list", $row->project_id));

	            $cat_id = $this->wpdb->get_var("select term_id from " . $this->wpdb->prefix . "terms where slug = 'new-release' limit 1");

	            $name = $row->name . " " . $row->version;

                $page = array();
                $page['post_type']      = 'post';
                $page['post_title']     = $name;
                $page['post_name']      = $name;
                $page['post_status']    = 'publish';
				$page['comment_status'] = 'open';
                $page['post_content']   = $body;
                $page['post_excerpt']   = $row->log;
                $page['post_category']  = array($cat_id);
                $page['post_author']    = $row->user;
                $page_id = wp_insert_post($page);

                wp_publish_post($page_id);

	        }
            $url = "admin.php?page=lx_projects&code=rap&action=release&releaseAction=modify&id=" . $_GET["id"];
		}

        else if ($_GET["releaseAction"] == "delete"){
            $q = "select lx_project_id from " . $this->wpdb->prefix . "lx_release where lx_release_id = %d limit 1";
            $project_id = $this->wpdb->get_var($this->wpdb->prepare($q, $_GET["id"]));
            $q = "delete from " . $this->wpdb->prefix . "lx_release where lx_release_id = %d limit 1";
            $q2 = "delete from " . $this->wpdb->prefix . "lx_file where lx_release_id = %d";
            $this->wpdb->query($this->wpdb->prepare($q, $_GET["id"]));
            $this->wpdb->query($this->wpdb->prepare($q2, $_GET["id"]));
            $url = "admin.php?page=lx_projects&action=view&id=$project_id&code=rd";

        }

        else if ($_GET["releaseAction"] == "add"){
	        global $user_ID;
            $version = strip_tags(htmlentities($_POST["version"]));
            $q = "select count(*) from " . $this->wpdb->prefix . "lx_release where ";
            $q .= "lx_release_version = %s and lx_project_id = %d limit 1";
            $dupe = $this->wpdb->get_var($this->wpdb->prepare($q, $version, $_POST["project_id"]));
            if ($dupe != 0){
	            $this->releaseForm(true);
	        }
	        else {

                $q = "select p.lx_project_id as project_id, ";
                $q .= "p.lx_project_page_id as page_id, ";
                $q .= "p.lx_project_desc as project_desc, ";
                $q .= "p.lx_project_name as name ";
                $q .= "from " . $this->wpdb->prefix . "lx_project p where lx_project_id = '" . $_POST["project_id"] . "' limit 1";
                $row = $this->wpdb->get_row($q);


    	        $link = $this->wpdb->get_var("select guid from " . $this->wpdb->prefix . "posts where ID = '" . $row->page_id . "' limit 1");
            	$link = "<a href=\"" . $link . "\">Project Homepage</a>";

            	$log = str_replace("\r\n", "<br />", strip_tags(htmlentities($_POST["log"])));
            	$notes = str_replace("\r\n", "<br />", strip_tags(htmlentities($_POST["notes"])));

            	$name = $row->name . " " . $version;

    	        $q = "insert into " . $this->wpdb->prefix . "lx_release ";
            	$q .= "(lx_project_id, user_id, lx_release_date, lx_release_version, lx_release_public, lx_release_approved, lx_release_notes, lx_release_log) ";
            	$q .= "values (%d, %d, %d, %s, %d, %d, %s, %s)";
            	$this->wpdb->query($this->wpdb->prepare($q, $_POST["project_id"], $user_ID, time(), $version, $_POST["public"], 1, $notes, $log));
            	$release_id = $this->wpdb->insert_id;

            	if ($_FILES){
                	for($i=1;$i<5;$i++){
                    	$arrayName = "file" . $i;
                    	if ($_FILES[$arrayName]["name"] != ''){
    	                    $fileName = $_FILES[$arrayName]["name"];
    		                $fileType = $_FILES[$arrayName]["type"];
            	            $fileSize = $_FILES[$arrayName]["size"];
                        	$fp       = fopen($_FILES[$arrayName]["tmp_name"], 'r');
                        	$fileData = fread($fp, filesize($_FILES[$arrayName]["tmp_name"]));
                        	$fileData = addslashes($fileData);
                        	fclose($fp);

    	                    $q = "insert into " . $this->wpdb->prefix . "lx_file";
                        	$q .= "(lx_release_id, user_id, lx_file_name, lx_file_type, lx_file_size, lx_file_data, lx_file_date_added, lx_file_date_updated, lx_file_download) ";
                        	$q .= "values ($release_id, $user_ID, '$fileName', '$fileType', $fileSize, '$fileData', " . time() . ", " . time() . ", 0)";
                        	$this->wpdb->query($q);
                        	//$this->wpdb->query($this->wpdb->prepare($q, , , , , , , time(), time(), 0));
                    	}
                	}
            	}

                if ($_POST["public"] == 1){
        	        $body = $this->options["newReleaseText"];
                    $body = str_replace("::PROJECTPAGE::", $link, $body);
                    $body = str_replace("::DESC::", $row->project_desc, $body);
                    $body = str_replace("::LOG::", $log, $body);
                    $body = str_replace("::CATEGORIES::", $this->parent->catForm("list", $row->project_id), $body);

    	            $cat_id = $this->wpdb->get_var("select term_id from " . $this->wpdb->prefix . "terms where slug = 'new-release' limit 1");

                    $page = array();
                    $page['post_type']      = 'post';
                    $page['post_title']     = $name;
                    $page['post_name']      = $name;
                    $page['post_status']    = 'publish';
    	            $page['comment_status'] = 'open';
    	            $page['post_content']   = $body;
    	            $page['post_excerpt']   = $log;
                    $page['post_category']  = array($cat_id);
                    $page['post_author']    = $user_ID;
                    $page_id = wp_insert_post($page);
                    //wp_publish_post($page_id);
    			}
                //die();
            	$url = "admin.php?page=lx_projects&action=view&id=" . $_POST["project_id"] . "&code=ra";
            }

		}

        else if ($_GET["releaseAction"] == "modify"){
            $version = strip_tags(htmlentities($_POST["version"]));
            $q = "select count(*) from " . $this->wpdb->prefix . "lx_release where ";
            $q .= "lx_release_version = %s and lx_project_id = %d and lx_release_id != %d limit 1";
            $dupe = $this->wpdb->get_var($this->wpdb->prepare($q, $version, $_POST["project_id"], $_POST["id"]));
            if ($dupe != 0){
	            $this->releaseForm(true);
	        }
	        else {



        		$log = str_replace("\r\n", "<br />", strip_tags(htmlentities($_POST["log"])));
            	$notes = str_replace("\r\n", "<br />", strip_tags(htmlentities($_POST["notes"])));

            	$q = "update " . $this->wpdb->prefix . "lx_release set lx_release_version = %s, lx_release_public = %d, lx_release_notes = %s, lx_release_log = %s where lx_release_id = %d limit 1";
            	$q2 = $this->wpdb->prepare($q, $version, $_POST["public"], $notes, $log, $_POST["id"]);
            	$this->wpdb->query($q2);

            	if ($_FILES){
               		for($i=1;$i<5;$i++){
                	   	$arrayName = "file" . $i;
                	   	$check = $arrayName . "-id";

                   		if ($_FILES[$arrayName]["name"] != ''){
    	                	$fileName = $_FILES[$arrayName]["name"];
    			        	$fileType = $_FILES[$arrayName]["type"];
                        	$fileSize = $_FILES[$arrayName]["size"];
                       		$fp       = fopen($_FILES[$arrayName]["tmp_name"], 'r');
                      		$fileData = fread($fp, filesize($_FILES[$arrayName]["tmp_name"]));
                       		$fileData = addslashes($fileData);
                       		fclose($fp);

                            if ($_POST[$check] != ''){
                            	$q = "update " . $this->wpdb->prefix . "lx_file set ";
                            	$q .= "lx_file_name = '$fileName', ";
                            	$q .= "lx_file_type = '$fileType', ";
                            	$q .= "lx_file_size = '$fileSize', ";
                            	$q .= "lx_file_data = '$fileData', ";
                            	$q .= "lx_file_date_updated = '" . time() . "', ";
                            	$q .= "lx_file_download = '0' ";
                            	$q .= "where lx_file_id = '" . $_POST[$check] . "' limit 1";
                            }
                            else {
    	                		$q = "insert into " . $this->wpdb->prefix . "lx_file ";
                     			$q .= "(lx_release_id, user_id, lx_file_name, lx_file_type, lx_file_size, lx_file_data, lx_file_date_added, lx_file_date_updated, lx_file_download) ";
                       			$q .= "values ($release_id, $user_ID, '$fileName', '$fileType', $fileSize, '$fileData', " . time() . ", " . time() . ", 0)";
                            }
                            //print($q);
                            //die();
                       		$this->wpdb->query($q);

	                   	}
                	}
                }
            }
            $url = "admin.php?page=lx_projects&action=view&id=" . $_POST["project_id"] . "&code=rm";
        }

        if ($url != ''){
        	$this->parent->parent->pageDirect($url);
        }
	}

    function processFile($release_id, $fileNumber){


    }

    function releaseForm($post=''){
	    global $filter;
        $nonce = wp_create_nonce();
        if ($_GET["id"] || $_POST["id"]){




    	    $label = "Modify Release";
            $action = "modify";
         	$q = "select p.lx_project_id as project_id, ";
        	$q .= "p.lx_project_page_id as page_id, ";
        	$q .= "p.lx_project_desc as project_desc, ";
        	$q .= "p.lx_project_name as name, ";
        	$q .= "r.lx_release_version as version, ";
        	$q .= "r.lx_release_public as public, ";
        	$q .= "r.lx_release_log as log, ";
        	$q .= "r.lx_release_notes as notes, ";
        	$q .= "r.user_id as user ";
        	$q .= "from  " . $this->wpdb->prefix . "lx_project p, " . $this->wpdb->prefix . "lx_release r where r.lx_project_id = p.lx_project_id and r.lx_release_id = %d";
        	$row = $this->wpdb->get_row($this->wpdb->prepare($q, $_GET["id"]));
        	$project_id = $row->project_id;
            if ($post == true){
            	$project_id = $_POST["project_id"];
            	$row->version = $_POST["version"];
            	$row->notes = $_POST["notes"];
            	$row->log = $_POST["log"];
            	$row->public = $_POST["public"];
            }
		}
        else {
            $label = "Add Release";
            $action = "add";
            $project_id = $_GET["project_id"];
            if ($post == true){
            	$project_id = $_POST["project_id"];
            	$row->version = $_POST["version"];
            	$row->notes = $_POST["notes"];
            	$row->log = $_POST["log"];
            	$row->public = $_POST["public"];
            }
        }

        $text = "<div class=\"wrap\">";
	    $text .= "<h2>ListingX - Release</h2>";
	    if ($post == true){
	    	$text .= "<br /><b><span style=\"color:#FF0000;\">Duplicate Version Number/Name</span></b>";
	    }
    	$text .= "<br />";
        $text .= "<div id=\"poststuff\" class=\"metabox-holder\">";
        $text .= "<div id=\"post-body\" class=\"has-sidebar\">";
        $text .= "<div id=\"post-body-content\" class=\"has-sidebar-content\">";
    	$text .= "<div class=\"postbox\">";
    	$text .= "<h3><label>$label</label></h3>";
        $text .= "<div class=\"inside\">";
    	$text .= "<form enctype=\"multipart/form-data\" method=\"post\" action=\"admin.php?page=lx_projects&action=release&releaseAction=$action\">";
    	$text .= "<input type=\"hidden\" name=\"_wpnonce\" value=\"" . $nonce . "\" />";
    	$text .= "<input type=\"hidden\" name=\"action\" value=\"$action\" />";
    	$text .= "<input type=\"hidden\" name=\"project_id\" value=\"" . $project_id . "\" />";
    	if ($_GET["id"]){
            $text .= "<input type=\"hidden\" name=\"id\" value=\"" . $_GET["id"] . "\" />";
    	}
        $text .= "<table class=\"form-table\">";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Release Version:</strong></td>";
        $text .= "<td><input type=\"text\" name=\"version\" value=\"" . $row->version . "\" />";
        $text .= "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Release Notes:</strong></td>";
        $text .= "<td><textarea name=\"notes\">" . $row->notes . "</textarea>";
        $text .= "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Release ChangeLog:</strong></td>";
        $text .= "<td><textarea name=\"log\">" . $row->log . "</textarea>";
        $text .= "</td></tr>";

        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Release Public:</strong></td>";
        $text .= "<td>";
        $text .= "<select name=\"public\">";
        for($i=0;$i<2;$i++){
        	if ($row->public == $i){ $s = "selected"; }
            else { $s = ''; }
            $text .= "<option value=\"$i\" $s>" . $filter[$i] . "</option>";
        }
        $text .= "</select></td></tr>";

		if ($_GET["id"]){
		    $options = get_option('listingx_options');
		    $subLink = $this->wpdb->get_var("select guid from " . $this->wpdb->prefix . "posts where ID = '" . $options["download_page_id"] . "' limit 1");
		    $subLink .= "&file=";
		    $query = "select lx_file_id as id, lx_file_name as name, lx_file_size as size, ";
            $query .= "lx_file_type as type, lx_file_download as download from " . $this->wpdb->prefix . "lx_file where ";
            $query .= "lx_release_id = '" . $_GET["id"] . "' order by lx_file_date_added asc";
            $result1 = $this->wpdb->get_results($query);

            $s = array();
            $x=1;

            foreach($result1 as $r){
            	$varName = "file" . $x;
            	$$varName = "<a href=\"$subLink" . $r->id . "\">" . $r->name . "</a>";
            	$$varName .= "<input type=\"hidden\" name=\"$varName" . "-id\" value=\"" . $r->id . "\" />";
                $x++;
            }
        }

        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>File 1:</strong></td>";
        $text .= "<td><input type=\"file\" name=\"file1\" /><br />";
        $text .= $file1;
        $text .= "</td></tr>";

        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>File 2:</strong></td>";
        $text .= "<td><input type=\"file\" name=\"file2\" /><br />";
        $text .= $file2;
        $text .= "</td></tr>";

        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>File 3:</strong></td>";
        $text .= "<td><input type=\"file\" name=\"file3\" /><br />";
        $text .= $file3;
        $text .= "</td></tr>";

        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>File 4:</strong></td>";
        $text .= "<td><input type=\"file\" name=\"file4\" /><br />";
        $text .= $file4;
        $text .= "</td></tr>";
        $text .= "</table>";


	    $text .= "<p class=\"submit\"><input type=\"submit\" name=\"Submit\" value=\"Save Changes\" />";
	    if ($_GET["id"]){
	    	$text .= " <input type=\"button\" value=\"Delete\" onClick=\"confirmAction('Are you Sure you want to Delete this Release?', 'admin.php?page=lx_projects&_wpnonce=$nonce&action=release&releaseAction=delete&id=" . $_GET["id"] . "');\" />";
	    }

    	$text .= "</p></form>";
        $text .= "</div></div></div></div>";
        $text .= "</div></div>";
        $this->text = $text;
    }
}
?>

