<?php

class listingx_projects {
	/**
	* The front-end methods for listingX.
 	* @package WordPress
 	*/

	function __construct($parent, $autoexec=true){
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->parent = $parent;
        $this->options = get_option('listingx_options');

        if ($autoexec != false){

	        switch($_GET["action"]){
        		case "view":
	        		$this->viewProject();
        			break;

        		case "form":
	        		$this->projectForm();
        			break;

	        	case "user":
        			$this->projectUser();
        			break;

        		case "submit":
        		case "approve":
        		case "delete":
        		case "userToggle":
        		case "admin":
	        		$this->submitForm();
        			break;

	        	case "release":
    				$pluginBase = 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'listingx';
    				require_once(ABSPATH . $pluginBase . DIRECTORY_SEPARATOR . 'listingx_releases.php');
	        		$this->releaseObj = new listingx_releases($this);
	        		$this->releaseObj->run();
	        		break;

				default:
					$this->listProjects();
					break;
        	}
			$this->parent->stroke($this->text);
		}
	}

	function projectUser(){
    	$pluginBase = 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'listingx';
    	require_once(ABSPATH . $pluginBase . DIRECTORY_SEPARATOR . 'listingx_list.php');
    	global $filter;

		$projectName = $this->wpdb->get_var("select lx_project_name from " . $this->wpdb->prefix . "lx_project where lx_project_id = '" . $_GET["id"] . "' limit 1");

		$text = "<div class=\"wrap\">";
		$text .= "<h2>ListingX - $projectName : Users</h2>";
		$text .= "<a href=\"admin.php?page=lx_projects&id=" . $_GET["id"] . "&action=view\">Back to Project</a>";
		$text .= $this->parent->message;

		if ($_GET["s"]){
            $searchTerm = "%" . $_GET["s"] . "%";
            $query  = "select u.ID as id, ";
            $query .= "u.user_login as login, ";
            $query .= "u.user_nicename as name, ";
            $query .= "u.user_email as email ";
            $query .= "from " . $this->wpdb->prefix . "users u ";
     		$query .= "where (u.user_login like %s or u.user_nicename like %s or u.user_email like %s) and ";
     		$query .= "u.ID not in (select lx.user_id from " . $this->wpdb->prefix . "lx_user lx where lx.lx_project_id ";
     	   	$query .= " = %d) ";
     		$query .= "order by u.user_login asc";

     		$q = $this->wpdb->prepare($query, $searchTerm, $searchTerm, $searchTerm, $_GET["id"]);
     		$result = $this->wpdb->get_results($q);
		}
		else {
            $query  = "select u.ID as id, ";
            $query .= "u.user_login as login, ";
            $query .= "u.user_nicename as name, ";
            $query .= "u.user_email as email, ";
            $query .="p.lx_user_perm as perm from ";
     		$query .= $this->wpdb->prefix . "lx_user p left join " . $this->wpdb->prefix . "users u on u.ID = p.user_id ";
     		$query .= "where p.lx_project_id = '" . $_GET["id"] . "' ";
     		$query .= "order by u.user_login asc";
     		$result = $this->wpdb->get_results($query);
		}

     	$list              = new listingx_list();
    	$list->search      = true;
    	$list->orderForm   = false;
    	$list->omit        = array("cb");
    	$list->searchLabel = "Search Users";


		$headers["cb"]              = "<input type=\"checkbox\" />";
		$headers["u.user_login"]    = "Username";
		$headers["u.user_nicename"] = "User Real Name";
		$headers["u.user_email"]    = "Email Address";
		$headers["p.lx_user_perm"]  = "Admin";

        $nonce = wp_create_nonce();

     	foreach($result as $row){
        	if ($row->perm == 1){ $approved = "Yes"; }
        	else if ($_GET["s"]) { $approved = "No"; }
        	else {
        		$approved = "<a href=\"admin.php?_wpnonce=$nonce&page=lx_projects&user_id=" . $row->id . "&project_id=" . $_GET["id"] . "&action=admin\">";
        		$approved .= "No</a>";
			}
        	$rows[$row->id] = array($row->login, $row->name, $row->email, $approved);
     	}
        $url = "admin.php?_wpnonce=$nonce&page=lx_projects&action=userToggle&project_id=" . $_GET["id"] . "&user_id=";
        $list->startList($headers, $url, '', '', $rows, array("page" => "lx_projects", "id" => $_GET["id"], "action" => "user"));
        $text .= $list->text . "</div>";
		$this->text = $text;
	}

	function catForm($type, $current=''){
		//types == list or select
		if ($type == "list"){
			$categories = '';
        	$query2 = "select c.lx_project_cat_id, c.lx_project_cat_name from " . $this->wpdb->prefix . "lx_project_cat c left join ";
        	$query2 .= $this->wpdb->prefix . "lx_project_cat_link l on l.lx_project_cat_id = c.lx_project_cat_id ";
        	$query2 .= "where l.lx_project_id = '$current' order by c.lx_project_cat_name asc";
        	$cats = $this->wpdb->get_results($query2);
        	foreach($cats as $c){
        		if ($this->frontEnd){
        		    $categories .= "<a href=\"" . $this->projectPage . "&category_id=" . $c->lx_project_cat_id . "&action=search\">";
        		}
        		else {
        			$categories .= "<a href=\"admin.php?page=lx_categories&id=" . $c->lx_project_cat_id . "&action=form\">";
        		}

        		$categories .= $c->lx_project_cat_name . "</a>, ";
        	}
        	$categories = substr($categories, 0, -2);
		}
		else {
        	$currentArray = array();
        	$query = "select lx_project_cat_id from " . $this->wpdb->prefix . "lx_project_cat_link where lx_project_id = '$current'";
        	$curr = $this->wpdb->get_results($query);
			if ($curr){
				foreach($curr as $c){
					$currentArray[] = $c->lx_project_cat_id;
			    }
			}


        	$categories = "<select name=\"cat[]\" multiple=\"multiple\" size=\"10\" style=\"height: 160px; \">";
        	$query = "select lx_project_cat_id as `id`, lx_project_cat_name as `name` from " . $this->wpdb->prefix . "lx_project_cat where lx_project_cat_approved = '1' order by lx_project_cat_name";
        	$cats = $this->wpdb->get_results($query);
        	foreach($cats as $c){
        		if (in_array($c->id, $currentArray)){ $s = "selected"; }
        		else { $s = ''; }
        		$categories .= "<option value=\"" . $c->id . "\" $s>" . $c->name . "</option>";
        	}
        	$categories .= "</select>";
		}
		return $categories;
	}

	function getUsers($project_id){
        $query = "select u.user_login as login, lu.lx_user_perm as perm from ";
        $query .= $this->wpdb->prefix . "users u, ";
        $query .= $this->wpdb->prefix . "lx_user lu ";
        $query .= "where u.ID = lu.user_id and lu.lx_project_id = '$project_id' ";
        $query .= "order by login";

        $result = $this->wpdb->get_results($query);

        if ($result){
        	foreach($result as $r){
        		if ($r->perm == 1){	$users .= "<strong>" . $r->login . "</strong>, "; }
        		else { $users .= $r->login . ", "; }
        	}
        }
        $users = substr($users, 0, -2);
        return $users;

	}

	function viewProject($id=''){

	    if ($id == ''){ $id = $_GET["id"]; }

        global $filter;
        $query = "select u.user_login, ";
        $query .= "p.lx_project_approved as approved, ";
        $query .= "p.lx_project_name as name, ";
        $query .= "p.lx_project_desc as `desc`, ";
        $query .= "p.lx_project_url as url, ";
        $query .= "p.lx_project_donate_url as donate, ";
        $query .= "p.lx_project_date_added as `date`, ";
        $query .= "p.lx_project_date_updated as updated ";
        $query .= "from " . $this->wpdb->prefix . "lx_project p ";
        $query .= "left join " . $this->wpdb->prefix . "users u on u.ID = p.user_id ";
        $query .= "where p.lx_project_id = '" . $id . "' limit 1";


        $row = $this->wpdb->get_row($query);
        $categories = $this->catForm("list", $id);
        $users = $this->getUsers($id);


        $dateFormat = get_option("date_format") . ", " . get_option("time_format");

		$text = "<div class=\"wrap\">";
		$text .= "<h2>ListingX - Projects</h2>";
		$text .= $this->parent->message;
		$text .= "<div id=\"poststuff\" class=\"metabox-holder\">";
		$text .= "<div id=\"post-body\" class=\"has-sidebar\">";
		$text .= "<div id=\"post-body-content\" class=\"has-sidebar-content\">";


        $text .= "<div class=\"postbox\">";
        $text .= "<h3><label>View Project : " . $row->name . "</label></h3>";
		$text .= "<div class=\"inside\">";

        $text .= "<table class=\"form-table\">";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Project Name:</strong></td>";
        $text .= "<td>" . $row->name . "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Project Owner:</strong></td>";
        $text .= "<td>" . $row->user_login . "</td></tr>";

        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Project Developers:</strong></td>";
        $text .= "<td>" . $users . "</td></tr>";



        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Project Description:</strong></td>";
        $text .= "<td>" . str_replace("\r\n", "<br />", $row->desc) . "</td></tr>";

        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Project Categories:</strong></td>";
        $text .= "<td>" . $categories . "</td></tr>";

        if ($row->url != ''){
        	$text .= "<tr class=\"form-field\">";
        	$text .= "<td><strong>Project URL:</strong></td>";
        	$text .= "<td><a href=\"" . $row->url . "\" target=\"_new\">" . $row->url . "</a></td></tr>";
        }
        if ($row->donate != ''){
	        $text .= "<tr class=\"form-field\">";
    	    $text .= "<td><strong>Project Donate URL:</strong></td>";
        	$text .= "<td><a href=\"" . $row->donate . "\" target=\"_new\">" . $row->donate . "</a></td></tr>";
        }

        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Project Approved:</strong></td>";
        $text .= "<td>" . $filter[$row->approved] . "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Project Added:</strong></td>";
        $text .= "<td>" . date($dateFormat, $row->date) . "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Project Updated:</strong></td>";
        $text .= "<td>" . date($dateFormat, $row->updated) . "</td></tr>";
        $text .= "</table>";

		$nonce = wp_create_nonce();

        $text .= "<p class=\"submit\">";
        $text .= "<input type=\"button\" value=\"Modify\" onClick=\"goToURL('admin.php?page=lx_projects&id=" . $id . "&action=form');\" />";
        $text .= " <input type=\"button\" value=\"Change Users\" onClick=\"goToURL('admin.php?page=lx_projects&id=" . $id . "&action=user');\" />";
		$text .= " <input type=\"button\" value=\"Delete\" onClick=\"confirmAction('Are you sure you want to DELETE this Project?', 'admin.php?page=lx_projects&id=" . $id . "&action=delete&_wpnonce=$nonce');\" />";
		if ($row->approved == 0){
			$text .= " <input type=\"button\" value=\"Approve\" onClick=\"goToURL('admin.php?page=lx_projects&id=" . $id . "&action=approve&_wpnonce=$nonce');\" />";
		}
		$text .= " <input type=\"button\" value=\"Add Release\" onClick=\"goToURL('admin.php?page=lx_projects&project_id=" . $id . "&action=release&releaseAction=form');\" />";
        $text .= "</p>";

        $text .= "</div></div></div></div></div>";


		$text .= "<div id=\"poststuff\" class=\"metabox-holder\">";
		$text .= "<div id=\"post-body\" class=\"has-sidebar\">";
		$text .= "<div id=\"post-body-content\" class=\"has-sidebar-content\">";


        $text .= "<div class=\"postbox\">";
        $text .= "<h3><label>Project Releases </label></h3>";
		$text .= "<div class=\"inside\">";

    	$pluginBase = 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'listingx';
    	require_once(ABSPATH . $pluginBase . DIRECTORY_SEPARATOR . 'listingx_releases.php');
       	$this->releaseObj = new listingx_releases($this->parent);

        $text .= $this->releaseObj->listReleases($id);

        $text .= "</div></div></div></div></div>";

		$text .= "</div>";
		$this->text = $text;

	}

	function submitForm(){
    	if ($_POST["_wpnonce"]){ $nonce = $_POST["_wpnonce"]; }
    	else if ($_GET["_wpnonce"]){ $nonce = $_GET["_wpnonce"]; }

        if (!wp_verify_nonce($nonce)){ die('Security check'); }
    	if ($_POST["action"] == "modify"){
        	$q = "select lx_project_page_id from " . $this->wpdb->prefix . "lx_project where lx_project_id = %d limit 1";
        	$page_id = $this->wpdb->get_var($this->wpdb->prepare($q, $_GET["id"]));

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



            $url = "admin.php?page=lx_projects&action=view&id=" . $_POST["id"] . "&code=m";
    	}
    	else if ($_POST["action"] == "add"){
        	global $user_ID;

        	$q = "insert into " . $this->wpdb->prefix . "lx_project ";
        	$q .= "(user_id, lx_project_name, lx_project_desc, lx_project_url, lx_project_donate_url, lx_project_date_added, lx_project_date_updated, lx_project_approved)";
        	$q .= " values (%d, %s, %s, %s, %s, %d, %d, %d)";

        	$this->wpdb->query($this->wpdb->prepare($q, $user_ID, $_POST["name"], $_POST["desc"], $_POST["url"], $_POST["donate"], time(), time(), 1));
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
            $cat_id = $this->wpdb->get_var("select term_id from " . $this->wpdb->prefix . "terms where slug = 'new-project' limit 1");
            $link = $this->wpdb->get_var("select guid from " . $this->wpdb->prefix . "posts where ID = '$page_id' limit 1");
            $link = "<a href=\"" . $link . "\">Project Homepage</a>";

    		$body = $this->options["newProjectPostText"];
			$body = str_replace("::PROJECTPAGE::", $link, $body);
			$body = str_replace("::DESC::", $_POST["desc"], $body);

			$page = array();

        	$page['post_type']      = 'post';
        	$page['post_title']     = "New Project: " . $_POST["name"];
        	$page['post_name']      = "New Project: " . $_POST["name"];
        	$page['post_status']    = 'publish';
        	$page['comment_status'] = 'open';
        	$page['post_content']   = $body;
        	$page['post_excerpt']   = $_POST["desc"];
        	$page['post_category']  = array($cat_id);
        	$page['post_author']    = $user_ID;
			$page_id = wp_insert_post($page);

            $url = "admin.php?page=lx_projects&action=view&id=" . $id . "&code=a";
    	}
    	else if ($_GET["action"] == "delete"){

        	$q = "select lx_project_page_id from " . $this->wpdb->prefix . "lx_project where lx_project_id = %d limit 1";
        	$page_id = $this->wpdb->get_var($this->wpdb->prepare($q, $_GET["id"]));

        	$q = "delete from " . $this->wpdb->prefix . "lx_project where lx_project_id = %d";
        	$q1 = "delete from " . $this->wpdb->prefix . "lx_project_cat_link where lx_project_id = %d";
        	$q2 = "delete from " . $this->wpdb->prefix . "lx_project_cat where lx_project_id = %d";
        	$this->wpdb->query($this->wpdb->prepare($q, $_GET["id"]));
        	$this->wpdb->query($this->wpdb->prepare($q1, $_GET["id"]));
        	$this->wpdb->query($this->wpdb->prepare($q2, $_GET["id"]));

        	wp_delete_post($page_id);

        	$url = "admin.php?page=lx_projects&code=d";
    	}
    	else if ($_GET["action"] == "approve"){

        	$q = "select u.ID, u.user_email, p.lx_project_page_id, p.lx_project_name, p.lx_project_desc ";
        	$q .= "from " . $this->wpdb->prefix . "lx_project p ";
        	$q .= "left join " . $this->wpdb->prefix . "users u on u.ID = p.user_id ";
        	$q .= "where p.lx_project_id = %d limit 1";
        	$row = $this->wpdb->get_row($this->wpdb->prepare($q, $_GET["id"]));
        	$page_id = $row->lx_project_page_id;

        	$q = "update " . $this->wpdb->prefix . "lx_project set lx_project_approved = '1' where lx_project_id = %s limit 1";
        	$this->wpdb->query($this->wpdb->prepare($q, $_GET["id"]));

			$dateFormat = get_option("date_format") . ", " . get_option("time_format");

			$this->wpdb->query("update " . $this->wpdb->prefix . "lx_project set lx_project_page_id = '$page_id' where lx_project_id = '$id' limit 1");
            $cat_id = $this->wpdb->get_var("select term_id from " . $this->wpdb->prefix . "terms where slug = 'new-project' limit 1");
            $link = $this->wpdb->get_var("select guid from " . $this->wpdb->prefix . "posts where ID = '$page_id' limit 1");
            $link = "<a href=\"" . $link . "\">Project Homepage</a>";

    		$body = $this->options["newProjectPostText"];
			$body = str_replace("::PROJECTPAGE::", $link, $body);
			$body = str_replace("::DESC::", $row->lx_project_desc, $body);

			$page = array();

        	$page['post_type']      = 'post';
        	$page['post_title']     = "New Project: " . $row->lx_project_name;
        	$page['post_name']      = "New Project: " . $row->lx_project_name;
        	$page['post_status']    = 'publish';
        	$page['comment_status'] = 'open';
        	$page['post_content']   = $body;
        	$page['post_category']  = array($cat_id);
        	$page['post_excerpt']   = $row->lx_project_desc;
        	$page['post_author']    = $row->ID;
			$page_id = wp_insert_post($page);

            //$exclude = get_option('exclude_pages');

            //foreach($exclude as $e){
            //	if ($e != $row->lx_project_page_id){
            //		$hold[] = $e;
            //	}
            //}
            //update_option('exclude_pages', $hold);

			$headers = "From: " . get_option("blogname") . " Administrator <" . get_option("admin_email") . ">\r\n";
        	$headers .= "X-Sender: <" . get_option("admin_email") . ">\r\n";
      		$headers .= "X-Mailer: PHP\r\n";
      		$headers .= "X-Priority: 3\r\n";
      	  	$headers .= "Reply-To: " . get_option("admin_email") . "\r\n";

      	 	$subject = "Your project '" . $row->lx_project_name . "' has been approved";
      	 	$message = "To view or modify your project, please login into " . get_option("blogname");

      	 	mail($row->user_email, $subject, $message, $headers);

        	$url = "admin.php?page=lx_projects&action=view&id=" . $_GET["id"] . "&code=ap";
    	}
    	else if ($_GET["action"] == "userToggle"){
        	if ($_GET["project_id"] == '' && $_GET["user_id"] == ''){ die("Action without valid arguments"); }
        	$q = "select count(*) as cnt from " . $this->wpdb->prefix . "lx_user where user_id = %d and lx_project_id = %d limit 1";
        	$count = $this->wpdb->get_var($this->wpdb->prepare($q, $_GET["user_id"], $_GET["project_id"]));
			if ($count == 0){
				$q = "insert into " . $this->wpdb->prefix . "lx_user (user_id, lx_project_id, lx_user_perm) values (%d, %d, %d)";
			}
			else {
				$q = "delete from " . $this->wpdb->prefix . "lx_user where user_id = %d and lx_project_id = %d and lx_user_perm = %d limit 1";
			}
			$this->wpdb->query($this->wpdb->prepare($q, $_GET["user_id"], $_GET["project_id"], 0));
			$url = "admin.php?page=lx_projects&action=user&id=" . $_GET["project_id"];
    	}
    	else if ($_GET["action"] == "admin"){
            if ($_GET["project_id"] == '' && $_GET["user_id"] == ''){ die("Action without valid arguments"); }
            $q = "update " . $this->wpdb->prefix . "lx_user set lx_user_perm = 0 where lx_project_id = %d";
            $this->wpdb->query($this->wpdb->prepare($q, $_GET["project_id"]));

            $q = "update " . $this->wpdb->prefix . "lx_user set lx_user_perm = 1 where lx_project_id = %d and user_id = %d limit 1";
            $this->wpdb->query($this->wpdb->prepare($q, $_GET["project_id"], $_GET["user_id"]));

            $q = "update " . $this->wpdb->prefix . "lx_project set user_id = %d where lx_project_id = %d limit 1";
            $this->wpdb->query($this->wpdb->prepare($q, $_GET["user_id"], $_GET["project_id"]));


    		$url = "admin.php?page=lx_projects&action=user&id=" . $_GET["project_id"];
    	}
    	else { die("Action not valid"); }
    	$this->parent->pageDirect($url);
	}

	function projectForm(){
        if ($_GET["id"]){
        	$query .= "select ";
        	$query .= "p.lx_project_name as name, ";
        	$query .= "p.lx_project_desc as `desc`, ";
       	 	$query .= "p.lx_project_url as url, ";
        	$query .= "p.lx_project_donate_url as donate ";
       	 	$query .= "from " . $this->wpdb->prefix . "lx_project p ";
       	 	$query .= "where p.lx_project_id = '" . $_GET["id"] . "' limit 1";

        	$row = $this->wpdb->get_row($query);

        	$action = "modify";
        	$label = "Modify Project:" . $row->name;

        }
        else {
        	$action = "add";
        	$label = "Add Project";
        }

        $categories = $this->catForm("select", $_GET["id"]);

        $text .= "<div class=\"wrap\">";
        $text .= "<h2>ListingX - Projects</h2>";
        $text .= "Use this page to manage your projects.";
        $text .= "<br />";


		$text .= "<div id=\"poststuff\" class=\"metabox-holder\">";
		$text .= "<div id=\"post-body\" class=\"has-sidebar\">";
		$text .= "<div id=\"post-body-content\" class=\"has-sidebar-content\">";
        $text .= "<div class=\"postbox\">";
        $text .= "<h3><label>$label</label></h3>";
		$text .= "<div class=\"inside\">";
        $text .= "<form method=\"post\" action=\"admin.php?page=lx_projects&action=submit\">";
        $text .= "<input type=\"hidden\" name=\"_wpnonce\" value=\"" . wp_create_nonce() . "\" />";
        $text .= "<input type=\"hidden\" name=\"action\" value=\"$action\" />";
        if ($_GET["id"]){
        	$text .= "<input type=\"hidden\" name=\"id\" value=\"" . $_GET["id"] . "\" />";
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
		$text .= "</div></div>";
		$this->text = $text;

	}

	function listProjects(){
    	$pluginBase = 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'listingx';
    	require_once(ABSPATH . $pluginBase . DIRECTORY_SEPARATOR . 'listingx_list.php');
    	global $filter;

    	$list            = new listingx_list();
    	$list->search    = true;
    	$list->orderForm = true;
    	$list->omit      = array("cb");

    	$list->addFilter("p.lx_project_approved", "Approved", array("0" => "No", "1" => "Yes"));

		$text = "<div class=\"wrap\">";
		$text .= "<h2>ListingX - Projects</h2>";
		$text .= "<a href=\"?page=lx_projects&action=form&sub=add\">Add Project</a>";
		$text .= $this->parent->message;

		$headers["cb"]                    = "<input type=\"checkbox\" />";
		$headers["p.lx_project_name"]     = "Project Name";
		$headers["u.user_login"]          = "Owner";
		$headers["c.lx_project_cat_name"] = "Categories";
		$headers["p.lx_project_approved"] = "Approved";

		$order = "p.lx_project_name";
		$sort  = "asc";

     	$query  = "select p.lx_project_id, p.lx_project_name, u.user_login, p.lx_project_approved from ";
     	$query .= $this->wpdb->prefix . "lx_project p left join " . $this->wpdb->prefix . "users u on u.ID = p.user_id order by $order $sort";

     	$result = $this->wpdb->get_results($query);

     	foreach($result as $row){
        	$approved = $filter[$row->lx_project_approved];
           	$categories = $this->catForm("list", $row->lx_project_id);
        	$rows[$row->lx_project_id] = array($row->lx_project_name, $row->user_login, $categories, $approved);
     	}
        $url = "admin.php?page=lx_projects&action=view&id=";
        $list->startList($headers, $url, $order, $sort, $rows, array("page" => "lx_projects"));
        $text .= $list->text . "</div>";
		$this->text = $text;
	}

}
?>
