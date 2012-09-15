<?php

class usecurex {
	/**
	* The admin functions for USecureX
	* @package WordPress
	*/
    
    var $numberPerPage = 50;
    var $baseURL;
    var $pluginBase;
    var $wpdb;
    
    /**
    * Constructor method, just creates a pair of global variables.
    * 
    */


    function __construct(){
        
        global $wpdb;
        $this->wpdb    = $wpdb;
        $this->options = get_option('usecurex_options');
        
    }
    
    /**
    * The actual function that kicks of the process of administration
    * 
    */
    
    function init(){
        switch($_GET["sub"]){
            case "settings":
                $this->adminSettings();
                break;
                
            case "form":
                $this->groupForm();
                break;
                
            case "submit":
                $this->groupSubmit();
                break;
            
            default:
                $this->listGroups();
                break;
        }
        $this->stroke($this->text);
    }
    
    /**
    * The settings page and update function
    * 
    */
    
    function adminSettings(){
        if ($_POST["update"]){
            if ($_POST["_wpnonce"]){ $nonce = $_POST["_wpnonce"]; }
            if (!wp_verify_nonce($nonce)){ die('Security check'); }            
            $this->options["default_page"] = $_POST["default_page"];
            update_option("usecurex_options", $this->options);
            $_POST = array();
            $status = "Settings Updated";
        }
        $text = "<div class=\"wrap\">";
        $text .= "<h2>USecureX Settings</h2>";
        $text .= "<span style=\"font-weight: bold; color: #FF0000;\">$status</span>";        
        
        $text .= "<div id=\"poststuff\" class=\"metabox-holder\">";
        $text .= "<div id=\"post-body\" class=\"has-sidebar\">";
        $text .= "<div id=\"post-body-content\" class=\"has-sidebar-content\">";
        $text .= "<div class=\"inside\">";
        $text .= "<form method=\"post\" action=\"" . $this->baseURL . "&sub=settings\" id=\"myForm\" name=\"myForm\">";
        $text .= "<input type=\"hidden\" name=\"_wpnonce\" value=\"" . wp_create_nonce() . "\" />";
        $text .= "<input type=\"hidden\" name=\"update\" value=\"1\" />";

        $text .= "<table class=\"form-table\">";
        $text .= "<tr class=\"form-field\">";  
        $text .= "<td><strong>Page to redirect to upon Authorized Action:</strong></td>";
        $text .= "<td><select name=\"default_page\">";
        $query = "select post_title, guid from " . $this->wpdb->prefix . "posts where post_type = 'page' order by post_title";
        $row = $this->wpdb->get_results($query);        
        foreach($row as $r){
            if ($this->options["default_page"] == $r->guid){ $s = "selected"; }
            else { $s = ''; }
            $text .= "<option value=\"" . $r->guid . "\" $s>" . $r->post_title . "</option>";
        }
        $text .= "</select></td></tr>"; 
        $text .= "</table>"; 
        $text .= "</div></div>";
        $text .= "<p class=\"submit\"><input type=\"submit\" name=\"Submit\" value=\"Save Changes\" />";
        $text .= "</div></div></div>";
        $this->text = $text;
        
    }

    function usecurex_install(){
	    /**
	    * Installs the plugin by creating the options
	    * @param NULL
	    * @return NULL
	    */
        update_option('usecurex_options', array());

        $this->wpdb->query("CREATE TABLE `" . $this->wpdb->prefix . "usecurex_group` (`usecurex_group_id` int(10) NOT NULL AUTO_INCREMENT,`usecurex_group_name` varchar(50) NOT NULL,PRIMARY KEY (`usecurex_group_id`)) ENGINE=MyISAM DEFAULT CHARSET=latin1;");
        $this->wpdb->query("CREATE TABLE `" . $this->wpdb->prefix . "usecurex_link` (`usecurex_group_id` int(10) NOT NULL, `usecurex_field_name` varchar(50) NOT NULL, `usecurex_field_id` varchar(50) NOT NULL,  UNIQUE KEY `usecurex_group_id` (`usecurex_group_id`,`usecurex_field_name`,`usecurex_field_id`)) ENGINE=MyISAM DEFAULT CHARSET=latin1;");        	

    }



    function usecurex_uninstall(){
    	/**
    	* Uninstalls the plugin by deleting the options
    	*
    	* @param NULL
    	* @return NULL
    	*/
        $this->wpdb->query("drop table `" . $this->wpdb->prefix . "usecurex_group`");
        $this->wpdb->query("drop table `" . $this->wpdb->prefix . "usecurex_link`");
    	delete_option('usecurex_options');

    }
    
    /**
    * outputs the actual text.
    * 
    * @param string $text the text in which to wrap and then print
    */
    
    function stroke($text){
        
        $body = "<script type='text/javascript' src='../wp-content/plugins/usecurex/javascript/form.js'></script>";
        $body .= "<script type='text/javascript' src='../wp-content/plugins/usecurex/javascript/suitex.js'></script>";    

        
        $body .= $this->adminHeaderMenu();
        $body .= $text;
        $body .= "<div id=\"dimmer\">&nbsp;</div><div id=\"response\">&nbsp;</div>";
        print($body);
        
    }
    
    /**
    * Creates the header menu
    * 
    * @return   string  $text
    */
    
    function adminHeaderMenu(){
         
        $text = "&nbsp;&nbsp;<a href=\"" . $this->baseURL . "&sub=settings\">Settings</a>";
        $text .= "&nbsp;&nbsp;<a href=\"" . $this->baseURL . "\">View Groups</a>";
        $text .= "&nbsp;&nbsp;<a href=\"" . $this->baseURL . "&sub=form\">Add New Group</a>"; 
        return $text;
    }    



    function usecurex_admin_menu(){
    	/**
    	* The hook for the admin menu
    	*
    	* @param NULL
    	* @return NULL
    	*/
        add_management_page('USecureX', 'USecureX', 10, __FILE__, array($this, 'init'));
    }
    
    /**
    * Creates the Group Listing
    * 
    * @param string $code the results code string
    */
    
    function listGroups($code=''){
        require_once(ABSPATH . $this->pluginBase . DIRECTORY_SEPARATOR . 'suitex_list.php'); 

        $text .= "<div class=\"wrap\">";
        $text .= "<h2>User Groups</h2>";
        $text .= "<span style=\"color: #FF0000; font-weight: bold;\">$code</span>";    
        $text .= $this->status;

        $headers["group_name"]              = "Group Name";
        $headers["members"]                 = "# of Members";
        $headers["pages"]                   = "# of Pages";

        $order = "usecurex_group_name";
        $sort  = "asc";
        
        if ($_GET["limit"]){ $limit = $_GET["limit"]; }
        else { $limit = 0; }

        
        $query = "select usecurex_group_id, usecurex_group_name from " . $this->wpdb->prefix . "usecurex_group order by $order $sort";


        $count=0;
        $result = $this->wpdb->get_results($query);
        foreach($result as $row){
            $count++;
            $memberCount = $this->wpdb->get_var("select count(usecurex_group_id) from " . $this->wpdb->prefix . "usecurex_link where usecurex_group_id = '" . $row->usecurex_group_id . "' and usecurex_field_name = 'user_id'");
            $pageCount = $this->wpdb->get_var("select count(usecurex_group_id) from " . $this->wpdb->prefix . "usecurex_link where usecurex_group_id = '" . $row->usecurex_group_id . "' and usecurex_field_name = 'page_id'");
            $rows[$row->usecurex_group_id] = array($row->usecurex_group_name, $memberCount, $pageCount);    
        }
        $url = $this->baseURL . "&sub=form&id=";
        
        $list = new suitex_list();
        $list->search       = false;
        $list->orderForm    = false;
        $list->filters      = false;
        //$list->omit         = array("cb");
        //$list->paging       = true;
        //$this->pluginPath   = $this->pluginBase;
        $list->setNum       = $this->numberPerPage;

        
        
        $list->startList($headers, $url, $order, $sort, $rows, $limit, $count);
        
        $text .= $list->text;
        $text .= "</div>";
        $this->text = $text;        
       
        
    }
    
    /**
    *  Submits the forms
    * 
    */
    
    function groupSubmit(){
        if ($_POST["_wpnonce"]){ $nonce = $_POST["_wpnonce"]; }
        else if ($_GET["_wpnonce"]){ $nonce = $_GET["_wpnonce"]; }

        if (!wp_verify_nonce($nonce)){ die('Security check'); }
                
        if ($_POST["id"]){
            $this->wpdb->query($this->wpdb->prepare("update " . $this->wpdb->prefix . "usecurex_group set usecurex_group_name = %s where usecurex_group_id = %d limit 1", $_POST["group_name"], $_POST["id"]));
            
            $this->wpdb->query($this->wpdb->prepare("delete from " . $this->wpdb->prefix . "usecurex_link where usecurex_group_id = %d", $_POST["id"]));
            foreach(array_keys($_POST) as $f){
                if (substr_count($f, "page_") != 0){
                    $field_id = str_replace("page_", '', $f);
                    $this->wpdb->query($this->wpdb->prepare("insert into " . $this->wpdb->prefix . "usecurex_link (usecurex_group_id, usecurex_field_name, usecurex_field_id) values (%d, %s, %d)", $_POST["id"], "page_id", $field_id));
                }
            }
            $members = explode(",", $_POST["members"]);
            foreach($members as $m){
                $this->wpdb->query($this->wpdb->prepare("insert into " . $this->wpdb->prefix . "usecurex_link (usecurex_group_id, usecurex_field_name, usecurex_field_id) values (%d, %s, %d)", $_POST["id"], "user_id", $m));
            }              
            $_POST = array();
            $this->listGroups("Group Modified");            
        }
        else if ($_GET["id"]){
            $this->wpdb->query($this->wpdb->prepare("delete from " . $this->wpdb->prefix . "usecurex_group where usecurex_group_id = %d", $_GET["id"]));
            $this->wpdb->query($this->wpdb->prepare("delete from " . $this->wpdb->prefix . "usecurex_link where usecurex_group_id = %d", $_GET["id"]));
            $this->listGroups("Group Deleted"); 
            
        }
        else {
            $this->wpdb->query($this->wpdb->prepare("insert into " . $this->wpdb->prefix . "usecurex_group (usecurex_group_name) values (%s)", $_POST["group_name"]));
            $group_id = $this->wpdb->insert_id;
            
            foreach(array_keys($_POST) as $f){
                if (substr_count($f, "page_") != 0){
                    $field_id = str_replace("page_", '', $f);
                    $this->wpdb->query($this->wpdb->prepare("insert into " . $this->wpdb->prefix . "usecurex_link (usecurex_group_id, usecurex_field_name, usecurex_field_id) values (%d, %s, %d)", $group_id, "page_id", $field_id));
                }
            }
            
            $members = explode(",", $_POST["members"]);
            
            foreach($members as $m){
                $this->wpdb->query($this->wpdb->prepare("insert into " . $this->wpdb->prefix . "usecurex_link (usecurex_group_id, usecurex_field_name, usecurex_field_id) values (%d, %s, %d)", $group_id, "user_id", $m));
            }  
            $_POST = array();
            $this->listGroups("Group Added");
        }
        
        
    }
    
    /**
    * Creates the forms
    * 
    */
    
    function groupForm(){
        $pageArray = array();
        
        $users = array();
        $members = array();
        
        if ($_GET["id"]){
            $groupName = $this->wpdb->get_var($this->wpdb->prepare("select usecurex_group_name from " . $this->wpdb->prefix . "usecurex_group where usecurex_group_id = %d limit 1", $_GET["id"]));
            $query = "select ID, user_login from " . $this->wpdb->prefix . "users order by user_login";
            $results = $this->wpdb->get_results($query);
            foreach($results as $row){
                $check = $this->wpdb->get_var($this->wpdb->prepare("select count(usecurex_group_id) from " . $this->wpdb->prefix . "usecurex_link where usecurex_group_id = %d and usecurex_field_name = 'user_id' and usecurex_field_id = %d limit 1", $_GET["id"], $row->ID));
                if ($check == 0){
                    $users[$row->ID] = $row->user_login;
                }
                else {
                    $members[$row->ID] = $row->user_login;
                }
            }  
            
            $results = $this->wpdb->get_results($this->wpdb->prepare("select usecurex_field_id from " . $this->wpdb->prefix . "usecurex_link where usecurex_group_id = %d and usecurex_field_name = 'page_id'", $_GET["id"]));
            foreach($results as $row){
                $pageArray[] = $row->usecurex_field_id;
            }
            
                     
            
            
            
            
            
            $label = "Modify Group";
        }
        else {
            $query = "select ID, user_login from " . $this->wpdb->prefix . "users order by user_login";
            $results = $this->wpdb->get_results($query);
            foreach($results as $row){
                $users[$row->ID] = $row->user_login;
            }
            $label = "Add Group";
        }
        $text = "<div class=\"wrap\">";
        $text .= "<h2>$label</h2>";
        $text .= "<script type=\"text/javascript\" src=\"../wp-content/plugins/usecurex/javascript/transfer.js\"></script>";  
        



        $text .= "<div id=\"poststuff\" class=\"metabox-holder\">";
        $text .= "<div id=\"post-body\" class=\"has-sidebar\">";
        $text .= "<div id=\"post-body-content\" class=\"has-sidebar-content\">";
        $text .= "<div class=\"inside\">";
        $text .= "<form method=\"post\" action=\"" . $this->baseURL . "&sub=submit\" id=\"myForm\" name=\"myForm\">";
        $text .= "<input type=\"hidden\" name=\"_wpnonce\" value=\"" . wp_create_nonce() . "\" />";
        
        if ($_GET["id"]){
            $text .= "<input type=\"hidden\" name=\"id\" value=\"" . $_GET["id"] . "\" />";
        }
        $text .= "<table class=\"form-table\">";
        $text .= "<tr class=\"form-field\">";  
        $text .= "<td><strong>Group Name:</strong></td>";
        $text .= "<td><input type=\"text\" id=\"group_name\" name=\"group_name\" value=\"" . $groupName . "\" />";
        $text .= "</td></tr>"; 

        $text .= "</table>"; 
        $text .= "</div>";
        
        $text .= "<div class=\"postbox\">"; 
        $text .= "<h3><label>Pages</label></h3>";  
        $text .= "<div class=\"inside\">";        
        $text .= "<table class=\"form-table\">";   
        $query = "select ID, post_title, guid from " . $this->wpdb->prefix . "posts where post_type = 'page' order by post_title";
        $row = $this->wpdb->get_results($query);
        $x=1;
        foreach($row as $r){
            if ($x == 1){ $text .= "<tr>"; }
            if (in_array($r->ID, $pageArray)){ $c = "checked"; }
            else { $c = ''; }
            $text .= "<td>";
            $text .= "<input type=\"checkbox\" name=\"page_" . $r->ID . "\" value=\"1\" $c />&nbsp;";
            $text .= "<a href=\"" . $r->guid . "\" target=\"_blank\">" . $r->post_title . "</a>";
            $text .= "</td>";
            
            if ($x == 5){ 
                $text .= "</tr>"; 
                $x=1;
            }
            else { $x++; }
        }
        
        
        
        
               
        $text .= "</table>";
        $text .= "</div></div>";

        $text .= "<div class=\"postbox\">"; 
        $text .= "<h3><label>Members</label></h3>";  
        $text .= "<div class=\"inside\">";        
        $text .= "<table class=\"form-table\">";     
        $text .= "<tr class=\"form-field\">";  
        $text .= "<td>";
        $text .= "<input type=\"hidden\" name=\"members\" value=\"\" />User List:<br />"; 
        $text .= "<select style=\"height: auto;\" name=\"userList\" size=\"10\" multiple onDblClick=\"moveSelectedOptions(this.form.userList, this.form.memberList, this.form.members);\">";
        foreach(array_keys($users) as $u){
            $text .= "<option value=\"$u\">" . $users[$u] . "</option>";
        }
        $text .= "</select></td><td>Member List:<br />";
        $text .= "<select style=\"height: auto;\" name=\"memberList\" size=\"10\" multiple onDblClick=\"moveSelectedOptions(this.form.memberList, this.form.userList, this.form.members, false);\">";
        foreach(array_keys($members) as $m){
            $text .= "<option value=\"$m\">" . $members[$m] . "</option>";
        }
        $text .= "</select>";
        
        $text .= "<script language=\"javascript\">selectAllOptions(document.myForm.memberList); document.myForm.members.value = getSelectedValues(document.myForm.memberList); </script>";
        $text .= "</td></tr>";         
        
        
        
        
        
        
             
        $text .= "</table>";
        $text .= "</div></div>";
        $text .= "<p class=\"submit\"><input type=\"submit\" name=\"Submit\" value=\"Save Changes\" />";
        if ($_GET["id"]){
            $text .= "&nbsp;<input type=\"button\" name=\"Delete\" value=\"Delete\" onClick=\"confirmAction('Confirm Delete?', '" . $this->baseURL . "&sub=submit&id=" . $_GET["id"] . "&_wpnonce=" . wp_create_nonce() . "');\" />";    
        }
        
        $text .= "</p>";    

        
        $text .= "</div></div></div>"; 
        
        $this->text = $text;      
    }

}

?>
