<?php

/**
* The administration functions for BookX
* 
* @param    object  $wpdb
* @param    array   $options
* @param    string  $baseURL
* @param    string  $pluginURL
* @param    string  $numberPerPage
* @param    array   $bookArray
* @param    string  $status
* @param    array   $filter
*/
  
class svnx_admin {
    
    var $wpdb;
    var $options        = array();
    var $baseURL        = "tools.php?page=svnx/includes/svnx_admin.php";
    var $filter         = array();
    var $numberPerPage  = 50;
    
    /**
    * The contstruct function.  Does nothing other than set up variables.
    *
    * @global object wpdb
    */

    function __construct(){
        global $wpdb;
        
        //ini_set('allow_url_fopen', "1");
        $this->wpdb    = $wpdb;
        $this->svnx_checkCode();
        //$this->wpdb->show_errors(); 
    }
    
    /**
    * Checks to see if there is a status code.  
    * 
    * @return   string  $this->status
    * 
    */

    function svnx_checkCode(){
        $codeArray["a"]  = "Repository Added";
        $codeArray["m"]  = "Repository Modified";
        $codeArray["d"]  = "Repository Deleted";
        $codeArray["am"] = "Configuration Options Modified";
        
        if ($_GET["code"]){
            $this->status = "<br /><b><span style=\"color:#FF0000;\">" . $codeArray[$_GET["code"]] . "</span></b>";   
        }
        
        if (!is_writable(SVNX_DIR . '/config')){
            $this->fault = "<br />" . SVNX_DIR . 'config' . " needs to be writable by the webserver.";
        }
        
        if (!is_file(SVNX_DIR . '/config/config.php')){
            $this->fault .= "<br />Update your <a href=\"" . $this->baseURL . "&sub=admin\">Configuration Options</a> (Save them if you haven't already so it can create a config file)";
        }
    }


    /**
    * Executes the class based on the URI
    *                                                      
    */
    
    function svnx_run(){
        
        switch($_GET["sub"]){
            case "admin":
                $this->svnx_admin();
                break;
                
            case "submit":
                $this->svnx_submit();
                break;
                
            case "form":
                $this->svnx_form();
                break;
                
            case "list":
            default:
                $this->svnx_list();
                break;
        }    
        
    }
    
    /**
    * Strokes $text to add menu options and return the actual content.
    * 
    * @param mixed $text
    */
    
    function svnx_stroke($text){
        $body = $this->adminHeaderMenu();
        $body .= $text;
        print($body);
        
    }
    
    /**
    * Creates the header menu
    * 
    * @return   string  $text
    */
    
    function adminHeaderMenu(){
        if (!$this->nonce){ $this->nonce = wp_create_nonce(); }
        $text = "<a href=\"" . $this->baseURL . "&sub=admin\">Configuration Options</a>";  
        $text .= "&nbsp;&nbsp;<a href=\"" . $this->baseURL . "&sub=list\">View Repositories</a>"; 
        $text .= "&nbsp;&nbsp;<a href=\"" . $this->baseURL . "&sub=form\">Add New Repository</a>"; 
        $text .= "<script type='text/javascript' src='" . SVNX_URL. "includes/suitex.js'></script>"; 
        if ($this->fault){
            $text .= "<div style=\"width: 85%; border: 2px solid #FF0000; background-color: #FFFFFF; padding: 5px;\"><b>SVNX needs your Attention</b>" . $this->fault . "</div>";    
        }
        return $text;
    }
    
    /** 
    * Creates the General Configuration Option Menu
    * 
    */
    
    function svnx_admin(){
        
        
        if ($_POST["_wpnonce"]){ 
            $nonce = $_POST["_wpnonce"]; 
            if (!wp_verify_nonce($nonce)){ die('Security check'); }
            $this->options["dir"] = $_POST["dir"];
            $this->options["bin"] = $_POST["bin"];
            $this->options["css"] = $_POST["css"];
            update_option('svnx_options', $this->options);
            
            $configFile = SVNX_DIR . "config/config.php";
            $configString = stripslashes("<?php \r\n \$config->setSvnConfigDir('" . $_POST["dir"] . "');\r\n \$config->setSVNCommandPath('" . $_POST["bin"] . "'); \r\n ?>");
            $handle = fopen($configFile, 'w');
            fwrite($handle, $configString);
            fclose($handle);   

            
            
              
        }
        
        //CHECK DIRECTORY TO BE WRITABLE
        
        $this->nonce = wp_create_nonce();
        
        $text = "<div class=\"wrap\">";
        $text .= "<h2>SVNX</h2>";
        $text .= "<br />";


        $text .= "<div id=\"poststuff\" class=\"metabox-holder\">";
        $text .= "<div id=\"post-body\" class=\"has-sidebar\">";
        $text .= "<div id=\"post-body-content\" class=\"has-sidebar-content\">";
        $text .= "<div class=\"postbox\">";
        $text .= "<h3><label>Configuration Options</label></h3>";
        $text .= $this->status;
        
        $text .= "<div class=\"inside\">";
        $text .= "<form method=\"post\" action=\"" . $this->baseURL . "&sub=admin&code=am\">";
        $text .= "<input type=\"hidden\" name=\"_wpnonce\" value=\"" . $this->nonce . "\" />";
        
        

        $text .= "<table class=\"form-table\">";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Config Directory (usually /tmp)</strong></td>";
        $text .= "<td><input type=\"text\" name=\"dir\" value=\"" . stripslashes($this->options["dir"]) . "\" />";
        $text .= "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Path to SVN binary (usually /usr/local/bin)</strong></td>";
        $text .= "<td><input type=\"text\" name=\"bin\" value=\"" . stripslashes($this->options["bin"]) . "\" />";
        $text .= "</td></tr>";        

        
      
        
        $text .= "<tr class=\"form-field\">";
        $text .= "<td valign=\"top\"><strong>CSS to Include</strong></td>";
        $text .= "<td><textarea cols=\"50\" rows=\"15\" name=\"css\">" . stripslashes($this->options["css"]) . "</textarea>";
        $text .= "</td></tr>";        

        $text .= "</table>";        
        
        
        
        $text .= "<p class=\"submit\"><input type=\"submit\" name=\"Submit\" value=\"Save Changes\" />";
        $text .= "</p></form>";
        $text .= "</div></div>";
        
        $text .= "</div></div>";
        $text .= "</div></div>";
        $this->svnx_stroke($text);        
        
    }
    
     
    /**
    * Installs the plugin by creating the page and options
    *
    * @param NULL
    * @return NULL
    */

    function svnx_install(){
        if (!get_option('svnx_options')){
            
            $options = array();
            $options["dir"] = '/tmp';
            $options["bin"] = '/usr/local/bin';
            $options["css"] = 'code, pre {\r\n
  font-family:\'Consolas\',monospace,sans-serif;\r\n
  text-align:left;\r\n
  margin:0;\r\n
}\r\n
.new a {color:green}\r\n
.del a {color:red}\r\n
.toggleup a:link,\r\n
.toggleup a:visited,\r\n
.toggleup a:focus {\r\n
  padding-left:22px;\r\n
  background:url(images/toggleup.png) no-repeat 2px 50%;\r\n
}\r\n
.toggleup a:hover {\r\n
  background:#F0E68C url(images/toggleup.png) no-repeat 2px 50%;\r\n
}\r\n
.toggledown a:link,\r\n
.toggledown a:visited,\r\n
.toggledown a:focus {\r\n
  padding-left:22px;\r\n
  background:url(images/toggledown.png) no-repeat 2px 50%;\r\n
}\r\n
.toggledown a:hover {\r\n
  background:#F0E68C url(images/toggledown.png) no-repeat 2px 50%;\r\n
}\r\n
.diff a:link,\r\n
.diff a:visited,\r\n
.diff a:focus {\r\n
  padding-left:22px;\r\n
  background:url(images/diff.png) no-repeat 2px 50%;\r\n
}\r\n
.diff a:hover {\r\n
  background:#F0E68C url(images/diff.png) no-repeat 2px 50%;\r\n
}\r\n
.geshi a:link,\r\n
.geshi a:visited,\r\n
.geshi a:focus,\r\n
.geshi a:hover {\r\n
    padding-left:0;\r\n
    background: none;\r\n
}\r\n
.geshi a:hover {\r\n
    text-decoration: underline;\r\n
}\r\n
\r\n
.newpath td.diff, td.diff pre {\r\n
  border:1px solid #f0f0f0;\r\n
  background-color:#f8f8f8;\r\n
  background-position:2px 50%;\r\n
  background-repeat:no-repeat;\r\n
}\r\n
.newpath td.diffdeleted, td.diffdeleted pre {\r\n
  border:1px solid #e8d4bc;\r\n
  background-color:#f8e4cc;\r\n
  background-image:url(images/bullet_delete.png);\r\n
  background-position:2px 50%;\r\n
  background-repeat:no-repeat;\r\n
}\r\n
.newpath tr:hover td.diffdeleted, tr.diffcode:hover td.diffdeleted pre {\r\n
  border-color:#bb9977;\r\n
  background-color:#ffccaa;\r\n
}\r\n
.newpath td.diffadded, td.diffadded pre {\r\n
  border:1px solid #cdf0cd;\r\n
  background-color:#ddffdd;\r\n
  background-image:url(images/bullet_add.png);\r\n
  background-repeat:no-repeat;\r\n
  background-position:2px 50%;\r\n
}\r\n
.newpath tr:hover td.diffadded, tr.diffcode:hover td.diffadded pre {\r\n
  border-color:#88bb88;\r\n
  background-color:#bbffbb;\r\n
}\r\n
.newpath td.diffchanged, td.diffchanged pre {\r\n
  border:1px solid #f0f0bc;\r\n
  background-color:#ffffcc;\r\n
  background-image:url(images/bullet_yellow.png);\r\n
  background-repeat:no-repeat;\r\n
  background-position:2px 50%;\r\n
}\r\n
.newpath tr:hover td.diffchanged, tr.diffcode:hover td.diffchanged pre {\r\n
  border-color:#bbbb55;\r\n
  background-color:#ffff99;\r\n
}\r\n
\r\n
code {\r\n
  white-space: pre-wrap;\r\n
}\r\n
\r\n
span.listing a, a.listing {\r\n
  padding-left:22px;\r\n
  background-image:url(images/sitemap_color.png);\r\n
  background-repeat:no-repeat;\r\n
  background-position:3px 50%;\r\n
}\r\n
span.detail a {\r\n
  padding-left:22px;\r\n
  background-image:url(images/file.png);\r\n
  background-repeat:no-repeat;\r\n
  background-position:3px 50%;\r\n
}\r\n
span.changes a {\r\n
  padding-left:22px;\r\n
  background-image:url(images/page_white_edit.png);\r\n
  background-repeat:no-repeat;\r\n
  background-position:3px 50%;\r\n
}\r\n
span.compact a {\r\n
  padding-left:22px;\r\n
  background-image:url(images/arrow_in.png);\r\n
  background-repeat:no-repeat;\r\n
  background-position:3px 50%;\r\n
}\r\n
span.full a {\r\n
  padding-left:22px;\r\n
  background-image:url(images/arrow_out.png);\r\n
  background-repeat:no-repeat;\r\n
  background-position:3px 50%;\r\n
}\r\n
span.ignorews a {\r\n
  padding-left:22px;\r\n
  background-image:url(images/pilcrow_delete.png);\r\n
  background-repeat:no-repeat;\r\n
  background-position:3px 50%;\r\n
}\r\n
span.regardws a {\r\n
  padding-left:22px;\r\n
  background-image:url(images/pilcrow.png);\r\n
  background-repeat:no-repeat;\r\n
  background-position:3px 50%;\r\n
}\r\n
span.log a,\r\n
td.log a {\r\n
  padding-left:22px;\r\n
  background-image:url(images/log.png);\r\n
  background-repeat:no-repeat;\r\n
  background-position:3px 50%;\r\n
}\r\n
span.blame a,\r\n
td.blame a {\r\n
  padding-left:22px;\r\n
  background-image:url(images/blame.png);\r\n
  background-repeat:no-repeat;\r\n
  background-position:3px 50%;\r\n
}\r\n
span.mime a,\r\n
td.mime a {\r\n
  padding-left:22px;\r\n
  background-image:url(images/eye.png);\r\n
  background-repeat:no-repeat;\r\n
  background-position:3px 50%;\r\n
}\r\n
span.svn a,\r\n
td.svn a {\r\n
  padding-left:22px;\r\n
  background-image:url(images/link.png);\r\n
  background-repeat:no-repeat;\r\n
  background-position:3px 50%;\r\n
}\r\n
\r\n
li.compress a,\r\n
span.compress a,\r\n
tbody tr td.compress a:link,\r\n
tbody tr td.compress a:visited,\r\n
tbody tr td.compress a:link {\r\n
  padding-left:22px;\r\n
  background-image:url(images/compress.png);\r\n
  background-repeat:no-repeat;\r\n
  background-position:3px 50%;\r\n
}\r\n
li.feed a,\r\n
span.feed a,\r\n
tbody tr td.feed a,\r\n
tbody tr td.feed a:link,\r\n
tbody tr td.feed a:visited,\r\n
tbody tr td.feed a:link {\r\n
  padding-left:22px;\r\n
  background-image:url(images/xml.gif);\r\n
  background-repeat:no-repeat;\r\n
  background-position:3px 50%;\r\n
}\r\n
.goyoungest a {\r\n
  color:#e25f53;\r\n
  padding-left:22px;\r\n
  background-image:url(images/exclamation.png);\r\n
  background-repeat:no-repeat;\r\n
  background-position:3px 50%;\r\n
}\r\n
.goyoungest a:hover {\r\n
  background-color:#fad4c8;\r\n
  color:#000;\r\n
}\r\n
td.code {\r\n
  padding:2px 2px 0 2px;\r\n
}\r\n
.icon {\r\n
  vertical-align: middle;\r\n
}\r\n
';
            
            update_option('svnx_options', $options);

        }
        
        
    }

    /**
    * Uninstalls the plugin by deleting the options and page
    */

    function svnx_uninstall(){
        delete_option('svnx_options');
    }

    /**
    * The form to add or modify a book.
    * 
    */
    
    function svnx_form($code=''){
        
        if ($code != ''){

            
            if ($_POST["id"]){
                $query = "select bx_item_name as name from " . $this->wpdb->prefix . "bx_item where bx_item_id = %d limit 1";
                $row = $this->wpdb->get_row($this->wpdb->prepare($query, $_POST["id"]));
                $row->isbn = $_POST["isbn"];
                $row->sidebar = $_POST["sidebar"];
                $row->comments = $_POST["comments"];
                $_GET["id"] = $_POST["id"];
                $label = "Modify Book : " . $row->name; 
                $action = "modify";
            }
            else {
                $row->isbn = $_POST["isbn"];
                $row->sidebar = $_POST["sidebar"];
                $row->comments = $_POST["comments"];
                $label = "Add Book";
                $action = "add";                 
            }

            $status = "<span style=\"font-weight: bold; color: #FF0000;\">" . $code . "</span><br />";            
        }
        else if ($_GET["id"]){
            $id = $_GET["id"];
            $row = $this->options["repo"][$id];        
        
            $label = "Modify Repository : " . $row["name"];            
            $action = "modify";
            
        }
        else {
            $label = "Add Repository";
            $action = "add";    
        }
        
        
        
        
        $this->nonce = wp_create_nonce();
        
        $text = "<div class=\"wrap\">";
        $text .= "<h2>SVNX</h2>";
        $text .= "<br />";


        $text .= "<div id=\"poststuff\" class=\"metabox-holder\">";
        $text .= "<div id=\"post-body\" class=\"has-sidebar\">";
        $text .= "<div id=\"post-body-content\" class=\"has-sidebar-content\">";
        $text .= "<div class=\"postbox\">";
        $text .= "<h3><label>$label</label></h3>";
        $text .= $status;
        
        $text .= "<div class=\"inside\">";
        $text .= "<form method=\"post\" action=\"" . $this->baseURL . "&sub=submit\">";
        $text .= "<input type=\"hidden\" name=\"_wpnonce\" value=\"" . $this->nonce . "\" />";
        $text .= "<input type=\"hidden\" name=\"action\" value=\"$action\" />";
        
        if ($_GET["id"]){
            $text .= "<input type=\"hidden\" name=\"id\" value=\"" . $_GET["id"] . "\" />";
        }
        $text .= "<table class=\"form-table\">";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Repository Name:</strong></td>";
        $text .= "<td><input type=\"text\" name=\"name\" value=\"" . $row["name"] . "\" />";
        $text .= "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>URL:</strong></td>";
        $text .= "<td><input type=\"text\" name=\"url\" value=\"" . $row["url"] . "\" />";
        $text .= "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Username:</strong></td>";
        $text .= "<td><input type=\"text\" name=\"username\" value=\"" . $row["username"] . "\" />";
        $text .= "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Password:</strong></td>";
        $text .= "<td><input type=\"text\" name=\"password\" value=\"" . $row["password"] . "\" />";
        $text .= "</td></tr>";  
        if (!$_GET["id"]){      
            $text .= "<tr class=\"form-field\">";
            $text .= "<td><strong>Create Page:</strong></td>";
            $text .= "<td><input type=\"checkbox\" value=\"1\" name=\"page\" />";
            $text .= "</td></tr>";  
        }



        $text .= "</table>";
        $text .= "<p class=\"submit\"><input type=\"submit\" name=\"Submit\" value=\"Save Changes\" />";
        if ($action == "modify"){
            $deleteURL = $this->baseURL . "&sub=submit&id=" . $_GET["id"] . "&_wpnonce=" . $this->nonce;
            $text .= "&nbsp;<input type=\"button\" value=\"Delete\" onClick=\"confirmAction('Are you sure you want to delete this book?', '$deleteURL');\" />";
        }
        $text .= "</p></form>";
        $text .= "</div></div>";
        $text .= "</div></div>";
        $text .= "</div></div>";
        $this->svnx_stroke($text);
    }
    
    /**
    * form actions to alter a book
    * 
    */
    
    function svnx_submit(){
        if ($_POST["_wpnonce"]){ $nonce = $_POST["_wpnonce"]; }
        else if ($_GET["_wpnonce"]){ $nonce = $_GET["_wpnonce"]; }

        if (!wp_verify_nonce($nonce)){ die('Security check'); }
        

    
        
        if ($_POST["action"] == "add"){
            $id = strtolower(ereg_replace("[^A-Za-z0-9]", "", $_POST["name"]));

            $arr["name"]     = $_POST["name"];
            $arr["url"]      = $_POST["url"];
            $arr["username"] = $_POST["username"];
            $arr["password"] = $_POST["password"];
            
            $this->options["repo"][$id] = $arr;
            update_option('svnx_options', $this->options);  
            $code = "a";  
            $configFile = SVNX_DIR . "config/" . $id . ".php";
            if ($_POST["username"] != ''){
                $configString = stripslashes("<?php \r\n \$config->addRepository('" . $_POST["name"] . "', '" . $_POST["url"] . "', null, '" . $_POST["username"] . "', '" . $_POST["password"] . "'); \r\n ?>");      
            }
            else {
                $configString = stripslashes("<?php \r\n \$config->addRepository('" . $_POST["name"] . "', '" . $_POST["url"] . "') \r\n ?>");      
            }
            
            $handle = fopen($configFile, 'w');
            fwrite($handle, $configString);
            fclose($handle);   
            
            if ($_POST["page"] == 1){
                $page                   = array();
                $page['post_type']      = 'page';                                       
                $page['post_title']     = $_POST["name"];
                $page['post_name']      = $id;
                $page['post_status']    = 'publish';
                $page['comment_status'] = 'closed';
                $page['post_content']   = 'This page displays one of your SVNX Repositories';
                $page_id = wp_insert_post($page);  
            
                add_post_meta($page_id, "svn", $id, true);         
            }
        }
        else if ($_POST["action"] == "modify"){
            $id              = $_POST["id"];
            $arr["name"]     = $_POST["name"];
            $arr["url"]      = $_POST["url"];
            $arr["username"] = $_POST["username"];
            $arr["password"] = $_POST["password"];
            
            $this->options["repo"][$id] = $arr;
            update_option('svnx_options', $this->options);  
            $code = "m";
            
            $configFile = SVNX_DIR . "config/" . $id . ".php";
            if ($_POST["username"] != ''){
                $configString = stripslashes("<?php \r\n \$config->addRepository('" . $_POST["name"] . "', '" . $_POST["url"] . "', null, '" . $_POST["username"] . "', '" . $_POST["password"] . "'); \r\n ?>");      
            }
            else {
                $configString = stripslashes("<?php \r\n \$config->addRepository('" . $_POST["name"] . "', '" . $_POST["url"] . "') \r\n ?>");      
            }
            
            $handle = fopen($configFile, 'w');
            fwrite($handle, $configString);
            fclose($handle);    
            
                     
            
            
                        
        }
        else {
            $id = $_GET["id"];
            unset($this->options["repo"][$id]);
            update_option('svnx_options', $this->options);  
            $code = "d";
            
            $configFile = SVNX_DIR . "config/" . $id . ".php";
            unlink($configFile);

        }
        
        $url = $this->baseURL . "&code=$code";
        $text = "<script language=\"javascript\">";
        $text .= "goToURL('$url'); ";
        $text .= "</script>";
        $this->svnx_stroke($text);
        
    }
    
    /**
    * The administration view of the book list.
    * 
    */
    
    function svnx_list(){
        
        require_once(SVNX_DIR . 'includes/suitex_list.php'); 

        $text .= "<div class=\"wrap\">";
        $text .= "<h2>SVNX</h2>";
        $text .= $this->status;

        $headers["name"]                = "Name";
        $headers["url"]                 = "URL";
        $headers["username"]            = "Username";
        $headers["id"]                  = "ID";




        foreach(array_keys($this->options["repo"]) as $key){
            $rows[$key] = array($this->options["repo"][$key]["name"], $this->options["repo"][$key]["url"],$this->options["repo"][$key]["username"],$key);
        }
        $url = $this->baseURL . "&sub=form&id=";
        



        $list = new suitex_list();
        $list->search       = false;
        $list->orderForm    = false;
        $list->filters      = false;
        $list->omit         = array("cb");
        $this->paging       = true;
        $this->pluginPath   = SVNX_URL;
        
        
        
        $list->startList($headers, $url, $order, $sort, $rows, $limit, $this->numberPerPage);
        $text .= $list->text;
        $text .= "</div>";
        
        $this->svnx_stroke($text);       
        
    }
    
    /**
    * Addes the admin menu option using the WP hook.
    * 
    */
    
    
    function svnx_adminMenu(){
        add_management_page('SVNX', 'SVNX', 5, __FILE__, array($this, 'svnx_run')); 
    } 
}
?>