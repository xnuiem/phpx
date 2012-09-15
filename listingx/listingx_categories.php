<?php

class listingx_categories {
	/**
	* The front-end methods for listingX.
 	* @package WordPress
 	*/
	function __construct($parent){
		global $wpdb;

		$this->parent = $parent;
		$this->wpdb   = $wpdb;



		switch($_GET["action"]){
			case "add":
			case "modify":
			case "delete":
			case "approve":
				$this->submitForm();
				break;

			case "form":
				$this->catForm();
				break;

			default:
				$this->listCat();
				break;
		}
		$this->parent->stroke($this->text);


	}


	function listCat(){
    	$pluginBase = 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'listingx';
    	require_once(ABSPATH . $pluginBase . DIRECTORY_SEPARATOR . 'listingx_list.php');


    	global $filter;

    	$nonce = wp_create_nonce();
    	$list            = new listingx_list();
    	$list->search    = false;
    	$list->orderForm = false;
    	$list->omit      = array("cb");

    	$list->addFilter("c.lx_project_cat_approved", "Approved", array("0" => "No", "1" => "Yes"));

		$text = "<div class=\"wrap\">";
		$text .= "<h2>ListingX - Project Categories</h2>";
		$text .= "<a href=\"?page=lx_categories&action=form&sub=add\">Add Category</a>";
		$text .= $this->parent->message;

		$headers["cb"]                    = "<input type=\"checkbox\" />";
		$headers["c.lx_project_cat_name"]     = "Category Name";
		$headers["c.user_id"] = "Added By";
		$headers["c.lx_project_cat_approved"] = "Approved";

		$order = "c.lx_project_cat_name";
		$sort  = "asc";

     	$query  = "select c.lx_project_cat_id, c.lx_project_cat_name, u.user_login, c.lx_project_cat_approved from ";
     	$query .= $this->wpdb->prefix . "lx_project_cat c left join " . $this->wpdb->prefix . "users u on u.ID = c.user_id ";
     	if ($_GET["c_lx_project_cat_approved"] != ''){
     		$query .= "where c.lx_project_cat_approved = %d ";
     	}
     	$query .= "order by %s %s";

        if ($_GET["c_lx_project_cat_approved"] != ''){
     		$result = $this->wpdb->get_results($this->wpdb->prepare($query, $_GET["c_lx_project_cat_approved"], $order, $sort));
     	}
     	else {
     		$result = $this->wpdb->get_results($this->wpdb->prepare($query, $order, $sort));
		}

     	foreach($result as $row){
        	if ($row->lx_project_cat_approved == 1){ $approved = $filter[$row->lx_project_cat_approved]; }
        	else {
        		$approved = "<a href=\"admin.php?page=lx_categories&action=approve&_wpnonce=$nonce&id=" . $row->lx_project_cat_id . "\">No</a>";
        	}
        	$rows[$row->lx_project_cat_id] = array($row->lx_project_cat_name, $row->user_login,  $approved);
     	}
        $url = "admin.php?page=lx_categories&action=form&id=";
        $list->startList($headers, $url, $order, $sort, $rows, array("page" => "lx_categories"));
        $text .= $list->text . "</div>";
		$this->text = $text;
	}

	function submitForm(){



    	if ($_POST["_wpnonce"]){ $nonce = $_POST["_wpnonce"]; }
    	else if ($_GET["_wpnonce"]){ $nonce = $_GET["_wpnonce"]; }

        if (!wp_verify_nonce($nonce)){ die('Security check'); }
		if ($_POST["action"] == "add"){
    		global $user_ID;
			$name = strip_tags(htmlentities($_POST["name"]));
			$q = "insert into " . $this->wpdb->prefix . "lx_project_cat (user_id, lx_project_cat_name, lx_project_cat_approved) values ";
			$q .= "(%d, %s, %d)";
			$this->wpdb->query($this->wpdb->prepare($q, $user_ID, $name, 1));
			$url = "admin.php?page=lx_categories&code=ca";
		}
		else if ($_POST["action"] == "modify"){
			$name = strip_tags(htmlentities($_POST["name"]));
			$q = "update " . $this->wpdb->prefix . "lx_project_cat set lx_project_cat_name = %s where lx_project_cat_id = %s limit 1";
			$this->wpdb->query($this->wpdb->prepare($q, $name, $_POST["id"]));
			$url = "admin.php?page=lx_categories&code=cm";

		}
		else if ($_GET["action"] == "delete"){
			$id = $_GET["id"];
			$q = "delete from " . $this->wpdb->prefix . "lx_project_cat where lx_project_cat_id = %d limit 1";
			$q2 = "delete from " . $this->wpdb->prefix . "lx_project_cat_link where lx_project_cat_id = %d";
			$this->wpdb->query($this->wpdb->prepare($q, $id));
			$this->wpdb->query($this->wpdb->prepare($q2, $id));
			$url = "admin.php?page=lx_categories&code=cd";

		}
		else if ($_GET["action"] == "approve"){
			$this->wpdb->query("update " . $this->wpdb->prefix . "lx_project_cat set lx_project_cat_approved = '1' where lx_project_cat_id = '" . $_GET["id"] . "' limit 1");
       		$url = "admin.php?page=lx_categories&code=cap";
		}
		$this->parent->pageDirect($url);

	}

	function catForm(){
        if ($_GET["id"]){
        	$query .= "select ";
        	$query .= "lx_project_cat_name as name ";

       	 	$query .= "from " . $this->wpdb->prefix . "lx_project_cat ";
       	 	$query .= "where lx_project_cat_id = '" . $_GET["id"] . "' limit 1";

        	$row = $this->wpdb->get_row($query);

        	$action = "modify";
        	$label = "Modify Category : " . $row->name;

        }
        else {
        	$action = "add";
        	$label = "Add Category";
        }
        $nonce = wp_create_nonce();

        $text .= "<div class=\"wrap\">";
        $text .= "<h2>ListingX - Categories</h2>";

		$text .= "<div id=\"poststuff\" class=\"metabox-holder\">";
		$text .= "<div id=\"post-body\" class=\"has-sidebar\">";
		$text .= "<div id=\"post-body-content\" class=\"has-sidebar-content\">";
        $text .= "<div class=\"postbox\">";
        $text .= "<h3><label>$label</label></h3>";
		$text .= "<div class=\"inside\">";
        $text .= "<form method=\"post\" action=\"admin.php?page=lx_categories&action=$action\">";
        $text .= "<input type=\"hidden\" name=\"_wpnonce\" value=\"" . $nonce . "\" />";
        $text .= "<input type=\"hidden\" name=\"action\" value=\"$action\" />";
        if ($_GET["id"]){
        	$text .= "<input type=\"hidden\" name=\"id\" value=\"" . $_GET["id"] . "\" />";
        }
        $text .= "<table class=\"form-table\">";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Category Name:</strong></td>";
        $text .= "<td><input type=\"text\" name=\"name\" value=\"" . $row->name . "\" />";
        $text .= "</td></tr>";
        $text .= "</table>";
        $text .= "<p class=\"submit\"><input type=\"submit\" name=\"Submit\" value=\"Save Changes\" />";
        if ($_GET["id"]){
        	$text .= "&nbsp;<input type=\"button\" value=\"Delete Category\" onClick=\"confirmAction('Are you Sure you want to Delete this Category?', ";
        	$text .= "'admin.php?page=lx_categories&action=delete&id=" . $_GET["id"] . "&_wpnonce=$nonce');\">";
		}

        $text .= "</p></form>";
		$text .= "</div></div></div></div>";
		$text .= "</div></div>";
		$this->text = $text;
	}

}
?>
