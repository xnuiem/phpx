<?php

class listingx_front {
	/**
	* Front End
 	* @package WordPress
 	*/

	function listingx_run(){

        global $wpdb;
		global $post;

        $this->options = get_option('listingx_options');
    	$this->wpdb = $wpdb;

    	if ($_GET["action"]){
    		$action = $_GET["action"];
    	}
    	else if ($_POST["action"]){
    		$action = $_POST["action"];
    	}

    	if ($post->ID == $this->options["download_page_id"]){
        	$action = "getFile";
    	}
		else if ($this->options["page_id"] != $post->ID){

			$query = "select count(*) from " . $this->wpdb->prefix . "lx_project where lx_project_page_id = '" . $post->ID . "' limit 1";
            $count = $this->wpdb->get_var($query);
			//print_r($row);
			if ($count != 0){
				$action = "view";
			}
		}
		/*else {
        	$this->text = $this->wpdb->get_var("select post_content from " . $this->wpdb->prefix . "posts where ID = '$id' limit 1");
        	return $this->text;
		}*/

        $this->projectPage = $this->wpdb->get_var("select guid from " . $this->wpdb->prefix . "posts where ID = '" . $this->options["page_id"] . "' limit 1");

    	$this->pluginBase = 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'listingx';
    	require_once(ABSPATH . $this->pluginBase . DIRECTORY_SEPARATOR . 'listingx_list_front.php');

       	switch($action){
       		case "addProject":
       			$this->listingx_addProject();
       	        break;

       	    case "modifyProject":
       	    	$this->listingx_modifyProject();
       	    	break;

       	    case "addRelease":
       	    	$this->listingx_addRelease();
       	    	break;

       	    case "modifyRelease":
       	    	$this->listingx_modifyRelease();
       	    	break;

       	    case "profile":
       	    	$this->listingx_profile();
       	    	break;

       	    case "users":
       	    	$this->listingx_users();
       	    	break;

       	    case "projectUser":
       	    	$this->listingx_projectUser();
       	    	break;

       	    case "search":
       	    	$this->listingx_searchProjects();
       	    	break;

       	    case "view":
       	    	$this->listingx_viewProject($post->ID);
       	    	break;

       	    case "getFile":
       	    	$this->listingx_getFile();
       	    	break;

       	    case "toggleUser":
       	    	$this->listingx_toggleUser();
       	    	break;

       	    case "adminUser":
       	    	$this->listingx_adminUser();
       	    	break;

    	}
    	if ($this->text){
		   	add_filter('the_content', array($this, 'stroke'));
		}

	}

	function listingx_addProject(){
		if ($_POST["action"]){
        	global $user_ID;

        	$q = "insert into " . $this->wpdb->prefix . "lx_project ";
        	$q .= "(user_id, lx_project_name, lx_project_desc, lx_project_url, lx_project_donate_url, lx_project_date_added, lx_project_date_updated, lx_project_approved)";
        	$q .= " values (%d, %s, %s, %s, %s, %d, %d, %d)";

        	$this->wpdb->query($this->wpdb->prepare($q, $user_ID, $_POST["name"], $_POST["desc"], $_POST["url"], $_POST["donate"], time(), time(), 0));
        	$id = $this->wpdb->insert_id;
            foreach($_POST["cat"] as $c){
            	$q = "insert into " . $this->wpdb->prefix . "lx_project_cat_link (lx_project_id, lx_project_cat_id) ";
            	$q .= "values (%d, %d)";
            	$this->wpdb->query($this->wpdb->prepare($q, $id, $c));
            }

            $q = "insert into " . $this->wpdb->prefix . "lx_user (user_id, lx_project_id, lx_user_perm) values (%d, %d, %d)";
            $this->wpdb->query($this->wpdb->prepare($q, $user_ID, $id, 1));

			$dateFormat = get_option("date_format") . ", " . get_option("time_format");

        	$page['post_type']      = 'page';
        	$page['post_title']     = $_POST["name"];
        	$page['post_name']      = $_POST["name"];
        	$page['post_status']    = 'publish';
        	$page['comment_status'] = 'open';
        	//$page['post_content']   = $this->options["newProjectPageText"];
        	$page['post_content']   = '';
        	$page['post_parent']    = $this->options["page_id"];
        	$page['post_author']    = $user_ID;
			$page_id = wp_insert_post($page);

			$this->wpdb->query("update " . $this->wpdb->prefix . "lx_project set lx_project_page_id = '$page_id' where lx_project_id = '$id' limit 1");
            $page = $this->wpdb->get_var("select guid from " . $this->wpdb->prefix . "posts where ID = '$page_id' limit 1");

            $exclude = get_option('exclude_pages');
            $exclude[] = $page_id;
            update_option('exclude_pages', $exclude);

			$this->sendEmail("addProject");

            header("Location: $page");
            exit();


		}
		$this->projectForm();

	}

	function sendEmail($action){
		$headers = "From: " . get_option("admin_email") . " <" . get_option("admin_email") . ">\r\n";
        $headers .= "X-Sender: <" . get_option("admin_email") . ">\r\n";
        $headers .= "X-Mailer: PHP\r\n";
        $headers .= "X-Priority: 3\r\n";
        $headers .= "Reply-To: " . get_option("admin_email") . "\r\n";

		switch($action){
			case "addProject":
				$subject = "New Project Pending Approval";
				$message = "A new project was submitted\r\n\r\n" . get_option("siteurl") . "/wp-admin/";
				break;

			case "addRelease":
				$subject = "New Public Release Pending Approval";
				$message = "A new release was submitted\r\n\r\n" . get_option("siteurl") . "/wp-admin/";
				break;
		}


        mail(get_option("admin_email"), $subject, $message, $headers);



	}

	function listingx_modifyProject(){
        if ($_POST["action"]){
        	global $user_ID;
        	$q = "select lx_project_page_id from " . $this->wpdb->prefix . "lx_project where lx_project_id = %d limit 1";
        	$page_id = $this->wpdb->get_var($this->wpdb->prepare($q, $_POST["id"]));

        	$q = "select user_id from " . $this->wpdb->prefix . "lx_project where lx_project_id = %d limit 1";
        	$user_id = $this->wpdb->get_var($this->wpdb->prepare($q, $_POST["id"]));

			if ($user_ID != $user_id){
				$this->text = "Invalid User Permissions";
				return;
			}

        	$q = "update " . $this->wpdb->prefix . "lx_project set ";
        	$q .= "lx_project_name = %s, lx_project_desc = %s, lx_project_url = %s, ";
        	$q .= "lx_project_donate_url = %s, lx_project_date_updated = %d ";
        	$q .= "where lx_project_id = %d limit 1";

        	$q1 = "delete from " . $this->wpdb->prefix . "lx_project_cat_link where lx_project_id = '%d'";
            $this->wpdb->query($this->wpdb->prepare($q, $_POST["name"], $_POST["desc"], $_POST["url"], $_POST["donate"], time(), $_POST["id"]));
            $this->wpdb->query($this->wpdb->prepare($q1, $_POST["id"]));

            foreach($_POST["cat"] as $c){
            	$q = "insert into " . $this->wpdb->prefix . "lx_project_cat_link (lx_project_id, lx_project_cat_id) ";
            	$q .= "values (%d, %d)";
            	$this->wpdb->query($this->wpdb->prepare($q, $_POST["id"], $c));
            }

            $page['post_title'] = $_POST["name"];
            $page['ID'] = $page_id;
            wp_update_post($page);
            $page = $this->wpdb->get_var("select guid from " . $this->wpdb->prefix . "posts where ID = '$page_id' limit 1");
            header("Location: $page");
            exit();

        }
		$this->projectForm();
	}

	function releaseForm(){
	    global $filter;
        $nonce = wp_create_nonce();
        if ($_GET["id"] || $_POST["id"]){

    	    $label = "Modify Release";
            $action = "modifyRelease";
         	$q = "select p.lx_project_id as project_id, ";
        	$q .= "p.lx_project_page_id as page_id, ";
        	$q .= "p.lx_project_desc as project_desc, ";
        	$q .= "p.lx_project_name as name, ";
        	$q .= "r.lx_release_version as version, ";
        	//$q .= "r.lx_release_public as public, ";
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
            $action = "addRelease";
            $project_id = $_GET["project_id"];
            if ($post == true){
            	$project_id = $_POST["project_id"];
            	$row->version = $_POST["version"];
            	$row->notes = $_POST["notes"];
            	$row->log = $_POST["log"];
            	$row->public = $_POST["public"];
            }
        }

	    if ($post == true){
	    	$text .= "<b><span style=\"color:#FF0000;\">Duplicate Version Number/Name</span></b><br />";
	    }
        $text .= "<div id=\"poststuff\" class=\"metabox-holder\">";
        $text .= "<div id=\"post-body\" class=\"has-sidebar\">";
        $text .= "<div id=\"post-body-content\" class=\"has-sidebar-content\">";
    	$text .= "<div class=\"postbox\">";
    	$text .= "<h3><label>$label</label></h3>";
        $text .= "<div class=\"inside\">";
    	$text .= "<form enctype=\"multipart/form-data\" method=\"post\" action=\"" . $this->projectPage . "\">";
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
       	if ($action == "addRelease"){
        	$text .= "<tr class=\"form-field\">";
        	$text .= "<td><strong>Announce Release:</strong></td>";
        	$text .= "<td>";
        	$text .= "<select name=\"public\">";
        	for($i=0;$i<2;$i++){
	        	if ($row->public == $i){ $s = "selected"; }
            	else { $s = ''; }
            	$text .= "<option value=\"$i\" $s>" . $filter[$i] . "</option>";
        	}
        	$text .= "</select></td></tr>";
        }

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
    	$text .= "</p></form>";
        $text .= "</div></div></div></div>";
        $text .= "</div>";
        $this->text = $text;
    }

	function listingx_addRelease(){
		if ($_POST["action"]){
            global $user_ID;

            $count = $this->wpdb->get_var("select count(*) from " . $this->wpdb->prefix . "lx_user where lx_project_id = '" . $_POST["project_id"] . "' and user_id = '$user_ID'");
            if ($count == 0){
            	$this->text = "Invalid Permissions";
            	return;
            }

            $version = strip_tags(htmlentities($_POST["version"]));
            $q = "select count(*) from " . $this->wpdb->prefix . "lx_release where ";
            $q .= "lx_release_version = %s and lx_project_id = %d limit 1";
            $dupe = $this->wpdb->get_var($this->wpdb->prepare($q, $version, $_POST["project_id"]));
            if ($dupe != 0){
	            $this->releaseForm(true);
	            return;
	        }
            $q = "select p.lx_project_id as project_id, ";
            $q .= "p.lx_project_page_id as page_id, ";
            $q .= "p.lx_project_desc as project_desc, ";
            $q .= "p.lx_project_name as name ";
            $q .= "from " . $this->wpdb->prefix . "lx_project p where lx_project_id = '" . $_POST["project_id"] . "' limit 1";
            $row = $this->wpdb->get_row($q);


	        $link1 = $this->wpdb->get_var("select guid from " . $this->wpdb->prefix . "posts where ID = '" . $row->page_id . "' limit 1");
        	$link = "<a href=\"" . $link1 . "\">Project Homepage</a>";

        	$log = str_replace("\r\n", "<br />", strip_tags(htmlentities($_POST["log"])));
        	$notes = str_replace("\r\n", "<br />", strip_tags(htmlentities($_POST["notes"])));

        	$name = $row->name . " " . $version;

	        $q = "insert into " . $this->wpdb->prefix . "lx_release ";
        	$q .= "(lx_project_id, user_id, lx_release_date, lx_release_version, lx_release_public, lx_release_approved, lx_release_notes, lx_release_log) ";
        	$q .= "values (%d, %d, %d, %s, %d, %d, %s, %s)";
        	$this->wpdb->query($this->wpdb->prepare($q, $_POST["project_id"], $user_ID, time(), $version, $_POST["public"], 0, $notes, $log));
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

                if ($_POST["public"] == 1){
					$this->sendEmail("addRelease");
    			}
    			header("Location: $link1");
    			exit();
    		}
    	}
    	else {
    		$this->releaseForm();
    	}
	}

	function listingx_modifyRelease(){
		if ($_POST["action"]){
            global $user_ID;

            $count = $this->wpdb->get_var("select count(*) from " . $this->wpdb->prefix . "lx_user where lx_project_id = '" . $_POST["project_id"] . "' and user_id = '$user_ID'");
            if ($count == 0){
            	$this->text = "Invalid Permissions";
            	return;
            }
            $q = "select ";
            $q .= "p.lx_project_page_id as page_id ";

            $q .= "from " . $this->wpdb->prefix . "lx_project p where lx_project_id = '" . $_POST["project_id"] . "' limit 1";
            $row = $this->wpdb->get_row($q);

	        $link = $this->wpdb->get_var("select guid from " . $this->wpdb->prefix . "posts where ID = '" . $row->page_id . "' limit 1");

            $version = strip_tags(htmlentities($_POST["version"]));
            $q = "select count(*) from " . $this->wpdb->prefix . "lx_release where ";
            $q .= "lx_release_version = %s and lx_project_id = %d and lx_release_id != %d limit 1";
            $dupe = $this->wpdb->get_var($this->wpdb->prepare($q, $version, $_POST["project_id"], $_POST["id"]));
            if ($dupe != 0){
	            $this->releaseForm(true);
	            return;
	        }
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
               			$this->wpdb->query($q);
               		}
				}
			}
    		header("Location: $link");
    		exit();
		}
		else {
			$this->releaseForm();
		}
	}

	function projectForm(){
    	require_once(ABSPATH . $this->pluginBase . DIRECTORY_SEPARATOR . 'listingx_projects.php');
    	$this->project = new listingx_projects($this, false);
    	$this->project->frontEnd = true;
    	$this->project->projectPage = $this->projectPage;
    	global $filter;
    	global $user_ID;
        if ($_GET["project_id"]){
        	$query .= "select ";
        	$query .= "p.lx_project_name as name, ";
        	$query .= "p.lx_project_desc as `desc`, ";
       	 	$query .= "p.lx_project_url as url, ";
        	$query .= "p.lx_project_donate_url as donate, ";
        	$query .= "p.user_id as user_id ";
       	 	$query .= "from " . $this->wpdb->prefix . "lx_project p ";
       	 	$query .= "where p.lx_project_id = '%d' limit 1";

        	$row = $this->wpdb->get_row($this->wpdb->prepare($query, $_GET["project_id"]));

			if ($user_ID != $row->user_id){
				$this->text = "Invalid User Permissions";
				return;
			}
        	$action = "modifyProject";
        	$label = "Modify Project:" . $row->name;

        }
        else {
        	$action = "addProject";
        	$label = "Add Project";
        }

        $categories = $this->project->catForm("select", $_GET["project_id"]);


		$text .= "<div id=\"poststuff\" class=\"metabox-holder\">";
		$text .= "<div id=\"post-body\" class=\"has-sidebar\">";
		$text .= "<div id=\"post-body-content\" class=\"has-sidebar-content\">";
        $text .= "<div class=\"postbox\">";
        $text .= "<h3><label>$label</label></h3>";
		$text .= "<div class=\"inside\">";
        $text .= "<form method=\"post\" action=\"\">";
        $text .= "<input type=\"hidden\" name=\"_wpnonce\" value=\"" . wp_create_nonce() . "\" />";
        $text .= "<input type=\"hidden\" name=\"action\" value=\"$action\" />";
        if ($_GET["project_id"]){
        	$text .= "<input type=\"hidden\" name=\"id\" value=\"" . $_GET["project_id"] . "\" />";
        }
        $text .= "<table class=\"form-table\">";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Project Name:</strong></td>";
        $text .= "<td><input type=\"text\" name=\"name\" value=\"" . $row->name . "\" />";
        $text .= "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Project Description:</strong></td>";
        $text .= "<td><textarea name=\"desc\">" . $row->desc . "</textarea>";
        $text .= "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Project Categories:</strong></td>";
        $text .= "<td>" . $categories . "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Project URL:</strong></td>";
        $text .= "<td><input type=\"text\" name=\"url\" value=\"" . $row->url . "\" />";
        $text .= "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Project Donate URL:</strong></td>";
        $text .= "<td><input type=\"text\" name=\"donate\" value=\"" . $row->donate . "\" />";
        $text .= "</td></tr>";
        $text .= "</table>";
        $text .= "<p class=\"submit\"><input type=\"submit\" name=\"Submit\" value=\"Save Changes\" />";
        $text .= "</p></form>";
		$text .= "</div></div></div></div>";
		$text .= "</div>";
		$this->text = $text;

	}

	function stroke(){
		$text = "<script type=\"text/javascript\" src=\"wp-content/plugins/listingx/listingx.js\"></script>";
		$this->text = $text . $this->text;
		return $this->text;
	}

	function listingx_toggleUser(){
        if ($_POST["_wpnonce"]){ $nonce = $_POST["_wpnonce"]; }
    	else if ($_GET["_wpnonce"]){ $nonce = $_GET["_wpnonce"]; }

        if (!wp_verify_nonce($nonce)){
        	$this->text = "Invalid Security Token";
        	return;
        }
        if ($_GET["project_id"] == '' && $_GET["user_id"] == ''){
        	$this->text = "Something is missing...";
        	return;
        }
        $q = "select count(*) as cnt from " . $this->wpdb->prefix . "lx_user where user_id = %d and lx_project_id = %d limit 1";
        $count = $this->wpdb->get_var($this->wpdb->prepare($q, $_GET["user_id"], $_GET["project_id"]));
		if ($count == 0){
			$q = "insert into " . $this->wpdb->prefix . "lx_user (user_id, lx_project_id, lx_user_perm) values (%d, %d, %d)";
		}
		else {
			$q = "delete from " . $this->wpdb->prefix . "lx_user where user_id = %d and lx_project_id = %d and lx_user_perm = %d limit 1";
		}
		$this->wpdb->query($this->wpdb->prepare($q, $_GET["user_id"], $_GET["project_id"], 0));
		$url = $this->projectPage . "?&project_id=" . $_GET["project_id"] . "&action=projectUser";
		header("Location: $url");
		exit();
	}

	function listingx_adminUser(){
    	if ($_POST["_wpnonce"]){ $nonce = $_POST["_wpnonce"]; }
    	else if ($_GET["_wpnonce"]){ $nonce = $_GET["_wpnonce"]; }

        if (!wp_verify_nonce($nonce)){
        	$this->text = "Invalid Security Token";
        	return;
        }

    	if ($_GET["project_id"] == '' && $_GET["user_id"] == ''){
        	$this->text = "Something is missing...";
        	return;
     	}
        $q = "update " . $this->wpdb->prefix . "lx_user set lx_user_perm = 0 where lx_project_id = %d";
        $this->wpdb->query($this->wpdb->prepare($q, $_GET["project_id"]));

        $q = "update " . $this->wpdb->prefix . "lx_user set lx_user_perm = 1 where lx_project_id = %d and user_id = %d limit 1";
        $this->wpdb->query($this->wpdb->prepare($q, $_GET["project_id"], $_GET["user_id"]));

        $q = "update " . $this->wpdb->prefix . "lx_project set user_id = %d where lx_project_id = %d limit 1";
        $this->wpdb->query($this->wpdb->prepare($q, $_GET["user_id"], $_GET["project_id"]));

		$url = $this->projectPage . "?&project_id=" . $_GET["project_id"] . "&action=projectUser";
		header("Location: $url");
		exit();
	}

	function listingx_projectUser(){

    	global $filter;
        global $post;
		$row      = $this->wpdb->get_row("select lx_project_name, lx_project_page_id from " . $this->wpdb->prefix . "lx_project where lx_project_id = '" . $_GET["project_id"] . "' limit 1");
		$backPage = $this->wpdb->get_var("select guid from " . $this->wpdb->prefix . "posts where ID = '" . $row->lx_project_page_id . "' limit 1");

		$projectName = $row->lx_project_name;

		//$text = "<div class=\"wrap\">";
		$text .= "<h2>$projectName : Users</h2>";
		$text .= "<a href=\"$backPage\">Back to Project</a>";
		$text .= $this->parent->message;

		if ($_GET["us"]){
            $searchTerm = "%" . $_GET["us"] . "%";
            $query  = "select u.ID as id, ";
            $query .= "u.user_login as login, ";
            $query .= "u.user_nicename as name, ";
            $query .= "u.user_email as email ";
            $query .= "from " . $this->wpdb->prefix . "users u ";
     		$query .= "where (u.user_login like %s or u.user_nicename like %s or u.user_email like %s) and ";
     		$query .= "u.ID not in (select lx.user_id from " . $this->wpdb->prefix . "lx_user lx where lx.lx_project_id ";
     	   	$query .= " = %d) ";
     		$query .= "order by u.user_login asc";

     		$q = $this->wpdb->prepare($query, $searchTerm, $searchTerm, $searchTerm, $_GET["project_id"]);
     		//print($q);
     		$result = $this->wpdb->get_results($q);
     		//print_r($result);

		}
		else {
            $query  = "select u.ID as id, ";
            $query .= "u.user_login as login, ";
            $query .= "u.user_nicename as name, ";
            $query .= "u.user_email as email, ";
            $query .="p.lx_user_perm as perm from ";
     		$query .= $this->wpdb->prefix . "lx_user p left join " . $this->wpdb->prefix . "users u on u.ID = p.user_id ";
     		$query .= "where p.lx_project_id = '" . $_GET["project_id"] . "' ";
     		$query .= "order by u.user_login asc";
     		$result = $this->wpdb->get_results($query);
		}

     	$list               = new listingx_list_front();
    	$list->search       = true;
    	$list->orderForm    = false;
    	$list->searchLabel  = "Search Users";
    	$list->searchField  = "us";


		$headers["u.user_login"]    = "Username";
		$headers["u.user_nicename"] = "User Real Name";
		$headers["u.user_email"]    = "Email Address";
		$headers["p.lx_user_perm"]  = "Admin";

        $nonce = wp_create_nonce();

     	foreach($result as $row){
        	if ($row->perm == 1){ $approved = "Yes"; }
        	else if ($_GET["us"]) { $approved = "No"; }
        	else {
        		$approved = "<a href=\"" . $this->projectPage . "?&_wpnonce=$nonce&user_id=" . $row->id . "&project_id=" . $_GET["project_id"] . "&action=adminUser\">";
        		$approved .= "No</a>";
			}
        	$rows[$row->id] = array($row->login, $row->name, $row->email, $approved);
     	}

        $url = $this->projectPage . "?&_wpnonce=$nonce&action=toggleUser&project_id=" . $_GET["project_id"] . "&user_id=";
       	$list->startList($headers, $url, '', '', $rows, array("project_id" => $_GET["project_id"], "action" => "projectUser", "page_id" => $post->ID));
        $text .= $list->text;
		$this->text = $text;

	}

	function listingx_searchProjects(){
    	require_once(ABSPATH . $this->pluginBase . DIRECTORY_SEPARATOR . 'listingx_projects.php');
    	$this->project = new listingx_projects($this, false);
    	$this->project->frontEnd = true;
    	$this->project->projectPage = $this->projectPage;
    	global $filter;


//		$text = "<div class=\"wrap\">";
//		$text .= "<h2>ListingX - Projects</h2>";
//		$text .= "<a href=\"?page=lx_projects&action=form&sub=add\">Add Project</a>";
//		$text .= $this->parent->message;
        //$text = "<table>";

		$order = "p.lx_project_name";
		$sort  = "asc";

     	$query  = "select p.lx_project_desc, post.guid, p.lx_project_id, p.lx_project_name, u.user_login from ";
     	$query .= "(" . $this->wpdb->prefix . "lx_project p, " . $this->wpdb->prefix . "posts post) ";
     	$query .= "left join " . $this->wpdb->prefix . "users u on u.ID = p.user_id";
     	if ($_GET["category_id"]){
     		$query .= " left join " . $this->wpdb->prefix . "lx_project_cat_link pcl on p.lx_project_id = pcl.lx_project_id ";
     		$query .= "where pcl.lx_project_cat_id = '" . $_GET["category_id"] . "' and ";

     	}
     	else {
     		$query .= "where";
     	}
     	$query .= "  p.lx_project_page_id = post.ID and p.lx_project_approved = 1 ";
     	$query .= " order by $order $sort";
print($query);
     	$result = $this->wpdb->get_results($query);

     	foreach($result as $row){
        	if (strlen($row->lx_project_desc) > 500){
        		$desc = substr($row->lx_project_desc, 0, 500) . "...";
        	}
        	else {
        		$desc = $row->lx_project_desc;
        	}


        	$categories = $this->project->catForm("list", $row->lx_project_id);

           	$text .= "<div class=\"archive_post_block\">";
       		$text .= "<h3 class=\"archive_title\" id=\"\">";
       		$text .= "<a href=\"" . $row->guid . "\">" . $row->lx_project_name . "</a></h3>";
			$text .= "<p>$desc</p>";
			$text .= "<p><i>$categories</i></p>";
           	$text .= "</div>";
     	}




		$this->text = $text;

	}

	function listingx_viewProject($id){

    	require_once(ABSPATH . $this->pluginBase . DIRECTORY_SEPARATOR . 'listingx_projects.php');

    	$this->project = new listingx_projects($this, false);
    	$this->project->frontEnd = true;
    	$this->project->projectPage = $this->projectPage;
    	$project_id = $this->wpdb->get_var("select lx_project_id from " . $this->wpdb->prefix . "lx_project where lx_project_page_id = '$id' limit 1");
        global $user_ID;
        global $filter;


        $query = "select u.user_login, u.ID as user_id, ";
        $query .= "p.lx_project_approved as approved, ";
        $query .= "p.lx_project_name as name, ";
        $query .= "p.lx_project_desc as `desc`, ";
        $query .= "p.lx_project_url as url, ";
        $query .= "p.lx_project_donate_url as donate, ";
        $query .= "p.lx_project_date_added as `date`, ";
        $query .= "p.lx_project_date_updated as updated, ";
        $query .= "u.ID as user ";
        $query .= "from " . $this->wpdb->prefix . "lx_project p ";
        $query .= "left join " . $this->wpdb->prefix . "users u on u.ID = p.user_id ";
        $query .= "where p.lx_project_id = '" . $project_id . "' limit 1";


        $row = $this->wpdb->get_row($query);

        if ($row->approved == 0 && $row->user_id != $user_ID){
        	$this->text = "This project has not yet been approved.  Please check back";
        	return;
        }


        $categories = $this->project->catForm("list", $project_id);
        $users = $this->project->getUsers($project_id);


        $dateFormat = get_option("date_format") . ", " . get_option("time_format");

		$text = "<div class=\"wrap\">";

        //$text .= "<div id=\"download\">DOWNLOAD</div>";

        $text .= "<h3><label>View Project : " . $row->name . "</label></h3>";
        $text .= str_replace("\r\n", "<br />", $row->desc);
        $text .= "<br /><br />";
        $text .= "<a name=\"tabs\" />";

        $text .= "<ul class=\"menu\">";
        $text .= "<li id=\"tab1\" class=\"selected\" onClick=\"showTab('1');\"><a href=\"#tabs\">Details</a></li>";
        $text .= "<li id=\"tab2\" onClick=\"showTab('2');\"><a href=\"#tabs\">Releases</a></li>";
        if ($user_ID == $row->user){
        	$text .= "<li id=\"tab3\" onClick=\"showTab('3');\"><a href=\"#tabs\">Admin</a></li>";
        }
        $text .= "</ul>";


        $text .= "<div id=\"vtab1\">";
        $text .= "<br />";
        $text .= "<table class=\"form-table\">";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Project Owner:</strong></td>";
        $text .= "<td>" . $row->user_login . "</td></tr>";

        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Project Developers:</strong></td>";
        $text .= "<td>" . $users . "</td></tr>";

        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Project Added:</strong></td>";
        $text .= "<td>" . date($dateFormat, $row->date) . "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Project Updated:</strong></td>";
        $text .= "<td>" . date($dateFormat, $row->updated) . "</td></tr>";


        if ($row->url != ''){
        	$text .= "<tr class=\"form-field\">";
        	$text .= "<td colspan=\"2\">";
        	$text .= "<a href=\"" . $row->url . "\" target=\"_new\">Project Homepage</a></td></tr>";
        }
        if ($row->donate != ''){
        	$text .= "<tr class=\"form-field\">";
        	$text .= "<td colspan=\"2\">";
        	$text .= "<a href=\"" . $row->donate . "\" target=\"_new\">Donate to this Project</a></td></tr>";
        }
        if ($row->approved == 0){
       	 	$text .= "<tr class=\"form-field\">";
        	$text .= "<td><strong>Project Approved:</strong></td>";
       		$text .= "<td>No</td></tr>";
        }

        $text .= "<tr class=\"form-field\">";
        $text .= "<td colspan=\"2\">";
        $text .= "<strong>Categories:</strong><br />$categories";
        $text .= "</td></tr>";



        $text .= "</table>";



        $text .= "</div>";
        $text .= "<div id=\"vtab2\">";



       	$releaseList = $this->listReleases($project_id);

        $text .= $releaseList;
        $text .= "</div>";


        if ($user_ID == $row->user){
	        $text .= "<div id=\"vtab3\">";
			$nonce = wp_create_nonce();

	        $text .= "<p class=\"submit\"><br /><br />";
        	$text .= "<input type=\"button\" value=\"Modify Project\" onClick=\"goToURL('" . $this->projectPage . "?&project_id=" . $project_id . "&action=modifyProject');\" />";
        	$text .= "<br /><br /> <input type=\"button\" value=\"Change Users\" onClick=\"goToURL('" . $this->projectPage . "?&project_id=" . $project_id . "&action=projectUser');\" />";
			$text .= "<br /><br /> <input type=\"button\" value=\"Add Release\" onClick=\"goToURL('" . $this->projectPage . "?&project_id=" . $project_id . "&action=addRelease');\" />";
        	$text .= "</p>";
        	$text .= "</div>";

        }


		$text .= "</div>";
		$this->text = $text;
	}

	function listingx_getFile(){

        $fileTypeArray['image/gif'] = "GIF Image";
        $fileTypeArray['application/vnd.ms-excel'] = "MS Excel";
        $fileTypeArray['text/plain'] = "ASCII Text";
        $fileTypeArray['application/pdf'] = "Adobe PDF";
        $fileTypeArray['application/x-zip-compressed'] = "ZIP";
        $fileTypeArray['text/html'] = "HTML";
        $fileTypeArray['image/x-photoshop'] = "Adobe PSD";
        $fileTypeArray['video/x-mpeg'] = "MPEG Video";
        $fileTypeArray['video/mpeg'] = "MPEG Video";
        $fileTypeArray['video/msvideo'] = "AVI Video";
        $fileTypeArray['video/x-msvideo'] = "AVI Video";
        $fileTypeArray['video/quicktime'] = "QuickTime Video";
        $fileTypeArray['video/x-quicktime'] = "QuickTime Video";
        $fileTypeArray['audio/mpeg3'] = "MP3 Audio";
        $fileTypeArray['audio/x-mpeg3'] = "MP3 Audio";
        $fileTypeArray['audio/mpeg'] = "MP3 Audio";
        $fileTypeArray['audio/x-mpeg'] = "MP3 Audio";
        $fileTypeArray['audio/wav'] = "WAV Audio";
        $fileTypeArray['audio/x-wav'] = "WAV Audio";
        $fileTypeArray['image/tiff'] = "TIFF Image";
        $fileTypeArray['image/x-tiff'] = "TIFF Image";
        $fileTypeArray['image/jpeg'] = "JPEG Image";
        $fileTypeArray['image/pjpeg'] = "JPEG Image";
        $fileTypeArray['image/x-MS-bmp'] = "Bitmap Image";
        $fileTypeArray['image/x-bmp'] = "Bitmap Image";
        $fileTypeArray['image/png'] = "PNG Image";
        $fileTypeArray['application/msword'] = "MS Word";
        $fileTypeArray['application/wordperfect'] = "Word Perfect";
        $fileTypeArray['application/rtf'] = "Rich Text Format";
        $fileTypeArray['application/vnd.ms-powerpoint'] = "MS Powerpoint";
        $fileTypeArray['application/x-tar'] = "TAR";
        $fileTypeArray['application/x-gzip'] = "GZIP";
        $fileTypeArray['application/x-gzip-compressed'] = "Tarball";
        $fileTypeArray['application/x-shockwave-flash'] = "Macromedia Flash";
        $fileTypeArray['application/x-director'] = "Macromedia Director";
        $fileTypeArray['application/x-pilot'] = "Palm OS File";
        $fileTypeArray['video/vnd.rn-realvideo'] = "Real Audio";
        $fileTypeArray['application/vnd.rn-realaudio'] = "Real Audio";
        $fileTypeArray['application/msaccess'] = "MS Access";
        $fileTypeArray['image/x-png'] = "PNG Image";
        $fileTypeArray['application/octet-stream'] = "Unspecified Application";
        $fileTypeArray['application/vnd.visio'] = "Visio";
        $fileTypeArray['application/acad'] = "AutoCad";
        $fileTypeArray['application/java-archive'] = "JAVA Archive";
        $fileTypeArray['application/msproject'] = "MS Project";
        $fileTypeArray['application/vnd.ms-project'] = "MS Project";
        $fileTypeArray['application/postscript'] = "Adobe Postscript File (eps)";
        $fileTypeArray['application/x-dwf'] = "AutoCad";
        $fileTypeArray['application/x-javascript'] = "JavaScript";
        $fileTypeArray['text/xml'] = "JavaScript";

       	if (!$_GET["file"]){ die("Invalid File"); }

       	$q = "select lx_file_name, lx_file_type, lx_file_data, lx_file_size from " . $this->wpdb->prefix . "lx_file where lx_file_id = %d limit 1";
       	$row = $this->wpdb->get_row($this->wpdb->prepare($q, $_GET["file"]));

       	$q = "update " . $this->wpdb->prefix . "lx_file set lx_file_download = lx_file_download + 1 where lx_file_id = %d";
       	$this->wpdb->query($this->wpdb->prepare($q, $_GET["file"]));



   		header("Content-length: " . $row->lx_file_size);
		header("Content-type:" . $row->lx_file_type . ";name='" . $fileTypeArray[$row->lx_file_type] . "'");
		header("Content-Disposition:attachment;filename='" . $row->lx_file_name . "'");
		print($row->lx_file_data);
		die();

	}

    function listReleases($project_id){
    	global $filter;

    	$list            = new listingx_list_front();
    	$list->search    = false;
    	$list->orderForm = false;
    	$list->omit      = array("cb");
    	$list->fold      = true;

    	$rows = array();

        $headers["r.lx_release_version"]  = "Version";
        $headers["u.user_login"]          = "Owner";
        $headers["r.lx_release_notes"]    = "Notes";
        $headers["r.lx_release_log"]      = "Log";
        //$headers["r.lx_release_public"]   = "Public";
        $headers["r.lx_release_date_added"] = "Release Date";

        $query = "select r.lx_release_version as version, ";
        $query .= "r.lx_release_id as id, ";
        $query .= "u.user_login as owner, ";
        $query .= "r.lx_release_notes as notes, ";
        $query .= "r.lx_release_log as log, ";
        $query .= "r.lx_release_public as public, ";
        $query .= "r.lx_release_approved as approved, ";
        $query .= "r.lx_release_date as added ";
        $query .= "from " . $this->wpdb->prefix . "lx_release r ";
        $query .= "left join " . $this->wpdb->prefix . "users u on u.ID = r.user_id ";
        $query .= "where r.lx_project_id = '$project_id' order by r.lx_release_date desc";

    	$result = $this->wpdb->get_results($query);

        $x=1;
        $dateFormat = get_option("date_format");
    	foreach($result as $row){
            $approved = $filter[$row->approved];
            $public   = $filter[$row->public];
            $rows[$row->id] = array($row->version, $row->owner, $row->notes, $row->log, date($dateFormat, $row->added));

            $query = "select lx_file_id as id, lx_file_name as name, lx_file_size as size, ";
            $query .= "lx_file_type as type, lx_file_download as download from " . $this->wpdb->prefix . "lx_file where ";
            $query .= "lx_release_id = '" . $row->id . "'";
            $result1 = $this->wpdb->get_results($query);

            $s = array();
            foreach($result1 as $r){
            	$s[] = array("id" => $r->id, "name" => $r->name, "size" => $r->size, "type" => $r->type, "download" => $r->download);
            }
            $rows[$row->id]["sub"] = $s;
            if ($x == 1){
            	$openSub .= "openSub('release-" . $row->id . "', 'image-" . $row->id . "', 'http://localhost/wp/wp-content/plugins/listingx');";
            }

            $x++;
    	}
    	$url = $this->projectPage . "?&action=modifyRelease&id=";
    	$list->startList($headers, $url, '', '', $rows, array("page" => "lx_projects"));
    	$text .= $list->text;
    	$text .= "<script langauge=\"javascript\">$openSub</script>";


        return $text;

    }
}
?>
