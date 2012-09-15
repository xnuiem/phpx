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
  
class bookx_admin {
    
    var $version        = "1.1";
    var $wpdb;

    var $baseURL        = "tools.php?page=bookx/includes/bookx_admin.php";
    var $numberPerPage  = "50";
    var $bookArray      = array();
    var $status         = '';

    
    /**
    * The contstruct function.  Does nothing other than set up variables.
    *
    * @global object wpdb
    */

    function __construct(){
        global $wpdb;
        
        ini_set('allow_url_fopen', "1");
        $this->wpdb    = $wpdb;
        
        $this->bookx_checkCode($_GET["code"]);
        
        
        
        
        //$this->wpdb->show_errors(); 
    }
    
    function bookx_iniForms(){
        require_once(BOOKX_DIR . "includes/bookx_admin_forms.php");
        $this->forms = new bookx_admin_forms();
        $this->forms->parent = $this;
    }
    
    
    
    /**
    * Checks to see if there is a status code.  
    * 
    * @param    string  $code           code, usualy from GET but can be passed directly
    * @return   string  $this->status   HTML formatted status update
    * 
    * 
    */

    function bookx_checkCode($code=''){
        $codeArray["a"]  = "Book Added";
        $codeArray["m"]  = "Book Modified";
        $codeArray["d"]  = "Book Deleted";
        $codeArray["c"]  = "Configuration Saved";
        $codeArray["b"]  = "Book List Refreshed";
        $codeArray["as"] = "Books Added";
        $codeArray["i"]  = "Books Imported";
        $codeArray["f"]  = "File Missing";
        
        if ($code){
            $this->status = "<br /><b><span style=\"color:#FF0000;\">&nbsp;" . $codeArray[$code] . "</span></b>";   
        }
    }


    /**
    * Executes the class based on the URI
    *                                                      
    */
    
    function bookx_run(){
        $this->bookx_upgrade();
        switch($_GET["sub"]){
            case "submit":
                $this->bookx_iniForms();
                $this->bookx_submit();
                break;
                
            case "form":
                $this->bookx_iniForms();
                $this->forms->bookx_form();
                break;
                
            case "admin":
                $this->bookx_iniForms();
                $this->forms->bookx_adminPage();
                break;
                
            case "refresh":
                $this->bookx_refreshAll();
                break;
                
            case "export": 
                $this->bookx_export();
                break;
                
            case "list":
            default:
                $this->bookx_list();
                break;
        }    
        
    }
    
    /**
    * Strokes $text to add menu options and return the actual content.
    * 
    * @param mixed $text
    */
    
    function bookx_stroke($text){
        $body = $this->adminHeaderMenu();
        $body .= $text;
        print($body);
        
    }
    
    /**
    * Creates and Export File that can be imported through General Options to restore booklist, or transplant it.
    * 
    */
    
    function bookx_export(){
        if (!wp_verify_nonce($_GET["_wpnonce"])){ die('Security check'); }    
        if (!is_writable(BOOKX_DIR . "export/")){
            $text .= "<div id=\"message\" class=\"error\">In order to create an export file, the directory " . BOOKX_DIR . "export/ must be writable by the webserver.</div>";        
        }
        
        else {
            if (!$this->var->options["export"]){
                $chars = array("a","A","b","B","c","C","d","D","e","E","f","F","g","G","h","H","i","I","j","J", "k","K","l","L","m","M","n","N","o","O","p","P","q","Q","r","R","s","S","t","T","u","U","v","V","w","W","x","X","y","Y","z","Z","1","2","3","4","5","6","7","8","9","0");
                $max_elements = count($chars) - 1;
                $fileName = srand((double)microtime()*1000000);
                for($i=0;$i<12;$i++){
                    $fileName .= $chars[rand(0,$max_elements)];
                }
                $this->var->options["export"] = md5($fileName);  
                update_option('bookx_options', $this->var->options);
            }
            
        
            $results = $this->wpdb->get_results("select bx_item_comments, bx_item_isbn, bx_item_sidebar, bx_item_summary, bx_item_no_update_desc from " . $this->wpdb->prefix . "bx_item");
            foreach($results as $row){
                $body .= "\"" . $row->bx_item_isbn . "\"|\"" . $row->bx_item_comments . "\"|\"" . $row->bx_item_sidebar . "\"|\"" . $row->bx_item_summary  . "\"|\"" . $row->bx_item_no_update_desc . "\"\r\n";
            }
        

            $fp = fopen(BOOKX_DIR . "export/" . $this->var->options["export"], "w");
            fwrite($fp, $body);
            fclose($fp);
            
            $url = $this->baseURL . "&sub=admin&export=" . $this->var->options["export"] . "#export";
            $text = "<script language=\"javascript\">";
            $text .= "goToURL('$url'); ";
            $text .= "</script>"; 
            $this->bookx_stroke($text);       
        }
    }
    
 
    
    
    /**
    * Creates the header menu
    * 
    * @return   string  $text
    */
    
    function adminHeaderMenu(){
        if (!$this->nonce){ $this->nonce = wp_create_nonce(); }
        $text = "<a href=\"" . $this->baseURL . "&sub=admin\">BookX Options</a>";
        $text .= "&nbsp;&nbsp;<a href=\"" . $this->baseURL . "&sub=list\">View Books</a>"; 
        $text .= "&nbsp;&nbsp;<a href=\"" . $this->baseURL . "&sub=form\">Add New Book</a>"; 
        $text .= "&nbsp;&nbsp;<a href=\"" . $this->baseURL . "&sub=refresh&_wpnonce=" . $this->nonce . "\">Refresh Book List</a>";
        $text .= "<script type='text/javascript' src='" . BOOKX_URL. "suitex/suitex.js'></script>"; 
        
        $text .= "<link rel='stylesheet' href='" . BOOKX_URL . "css/style.css' type='text/css' />";

        
        return $text;
    }
    
    function bookx_startFetch($isbn){
        
        if ($this->bookx_getFetch()){
            $this->fetchObj->bookx_fetchItem($isbn);
            if ($this->var->options["failover"] == 0 && $this->bookArray["name"] == ''){
                $this->bookArray = array();
                return false;
            }
            else if ($this->bookArray["name"] == ''){
                $this->fetchObj = null;
                $this->bookx_startFetch($isbn);
            }
            else {
                return true;
            }
        }
        else {
            $this->bookArray = array();
        }
        
        
        
    }
    
    function bookx_getFetch(){
        if (!$this->tried || count($this->tried) == 0){ $fetch = $this->var->options["fetch"]; }
        else {
            foreach(array_keys($this->var->fetchSourceArray) as $f){
                if (!in_array($f, $this->tried)){
                    $fetch = $f;
                }
            }
        }
        
        if ($fetch){
            $fetchClassName = "bookx_fetch_" . $fetch;
            $fileName = BOOKX_DIR . "fetch/bookx_fetch_" . $fetch . ".php";
            require_once($fileName);
            $this->fetchObj = new $fetchClassName($this);
            $this->tried[] = $fetch;
            return true;
        }
        else {
            $this->bookArray = array(); 
        }
        
    }
    
    /**
    * Refreshes all the books in the list
    * 
    * @param boolean $importSkipNonce
    */
    
    function bookx_refreshAll($importSkipNonce=false){
        
        
        
        if ($importSkipNonce != true){ 
            $code = "b";
            if (!wp_verify_nonce($_GET["_wpnonce"])){ die('Security check'); }   
        }
        else { $code = "i"; }
        
        $sql = "select bx_item_id, bx_item_isbn, bx_item_no_update_desc from " . $this->wpdb->prefix . "bx_item";
        $result = $this->wpdb->get_results($sql);
        
        foreach($result as $row){
            $this->tried = array();
            $this->bookArray = array();
            $this->bookx_startFetch($row->bx_item_isbn); 
            
            if ($this->bookArray["name"] != ''){
            
                $sql = "update " . $this->wpdb->prefix . "bx_item set ";
                foreach(array_keys($this->bookArray) as $key){     
                    if ($key != "summary"){$sql .= "bx_item_" . $key . " = '" . addslashes($this->bookArray[$key]) . "', "; }
                    else if ($row->bx_item_no_update_desc != 1){ $sql .= "bx_item_summary = '" . addslashes($this->bookArray["summary"]) . "', ";  }
                }
                $sql .= "bx_item_date_added = " . time() . " where bx_item_id = " . $row->bx_item_id . " limit 1";
                $this->wpdb->query($sql);
            }
        }
        
        $url = $this->baseURL . "&code=$code";
        $text = "<script language=\"javascript\">";
        $text .= "goToURL('$url'); ";
        //$text .= "window.location = '$url';";
        $text .= "</script>";
        $this->bookx_stroke($text);        
        
    }
    
    /**
    * Adds a value to the bookarray
    * 
    * @param    mixed   $key
    * @param    mixed   $value
    * @param    mixed   $noStrip
    */
                             
    function addBookToArray($key, $value, $noStrip = false){

        if ($noStrip == true){ 
            $this->bookArray[$key] = trim(rtrim($value)); 
        }
        else if ($noStrip == false){ 
            $this->bookArray[$key] = trim(rtrim(strip_tags($value))); 
        }
        else { 
            $this->bookArray[$key] = trim(rtrim(strip_tags($value, "$noStrip"))); 
        }
        
    }
    
    function bookx_upgrade(){

        if ($this->var->options["version"] != $this->version){
            require_once(BOOKX_DIR . "includes/bookx_upgrade.php");           
            
            if ($this->var->options["failover"] == '' || !$this->var->options["failover"]){ $this->var->options["failover"] = "0"; }            
            if (!$this->var->options["fetch"] || $this->var->options["fetch"] == ''){ $this->var->options["fetch"] = "bn"; }

            if ($this->var->options["version"] == '' || !$this->var->options["version"]){ //Assume version 0.6 
                $this->var->options["version"] = "0.6";
            }
            
            
            
            foreach($upgradeArray[$this->var->options["version"]] as $sql){
                $this->wpdb->query($sql);
            }
            
            
            
            $this->var->options["version"] = $this->version;
            update_option('bookx_options', $this->var->options);
        }
    }

    /**
    * Installs the plugin by creating the page and options
    *
    * @param NULL
    * @return NULL
    */

    function bookx_install(){
        if (!get_option('bookx_options')){
            $sql = "CREATE TABLE `" . $this->wpdb->prefix . "bx_item` (
  `bx_item_id` int(10) NOT NULL AUTO_INCREMENT,
  `bx_item_name` varchar(255) DEFAULT NULL,
  `bx_item_author` varchar(255) DEFAULT NULL,
  `bx_item_comments` text,
  `bx_item_date` int(10) DEFAULT NULL,
  `bx_item_date_added` int(10) DEFAULT NULL,
  `bx_item_format` varchar(255) DEFAULT NULL,
  `bx_item_image` text,
  `bx_item_isbn` varchar(15) DEFAULT NULL,
  `bx_item_link` text,
  `bx_item_pages` int(10) NOT NULL DEFAULT '0',
  `bx_item_price` float(4,2) NOT NULL DEFAULT '0.00',
  `bx_item_sidebar` tinyint(1) NOT NULL DEFAULT '0',
  `bx_item_summary` text,
  `bx_item_publisher` varchar(255) DEFAULT NULL,
  `bx_item_no_update_desc` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`bx_item_id`),
  UNIQUE KEY `bx_item_isbn` (`bx_item_isbn`),
  KEY `bx_item_sidebar` (`bx_item_sidebar`)
);";
            $this->wpdb->query($sql);
        
            $page                   = array();
            $page['post_type']      = 'page';                                       
            $page['post_title']     = 'Recommended Books';
            $page['post_name']      = 'booklist';
            $page['post_status']    = 'publish';
            $page['comment_status'] = 'closed';
            $page['post_content']   = 'This page displays your BookX front end.';

            $page_id = wp_insert_post($page);
            $options = array();
            $options['page_id']                 = $page_id;
            $options['widget_title']            = "Recommended Books";
            $options['per_page']                = "10";         
            $options['widget_image_height']     = "45"; 
            $options['widget_image_width']      = "45";
            $options['list_image_height']       = "100";
            $options['list_image_width']        = "100";
            $options['detail_image_height']     = "250";
            $options['detail_image_width']      = "250";
            $options['listTemplate']            = "<a href=\"::LINK::\">::IMAGE::</a> <h2><a href=\"::LINK::\">::TITLE::</a></h2> by ::AUTHOR:: <br />\r\n::SUMMARY::::MORE::";
            $options['detailTemplate']          = "<a href=\"::ELINK::\">::IMAGE::</a> <strong><a href=\"::ELINK::\">::TITLE::</a></strong> by ::AUTHOR:: <br />\r\n(::FORMAT::)<br />\r\n<strong>Price: </strong>\$::PRICE::<br />\r\n<strong>Pages: </strong>::PAGES::<br />\r\n<br />::SUMMARY::<br /><br />::COMMENTS::\r\n<br />";
            $options['widgetTemplate']          = "<a href=\"::LINK::\">::IMAGE::</a> <strong><a href=\"::LINK::\">::TITLE::</a></strong> by ::AUTHOR::";
            $options['widget_sort']             = "desc";
            $options['widget_order']            = "bx_item_name";
            $options['list_characters']         = "250";
            $options['css']                     = ".bookx_list_entry img,\r\n.bookx_detail_entry img {\r\n padding: 5px;\r\n }\r\n.bookx_detail_entry {\r\n height: 100%;\r\n }\r\n.bookx_list_entry {\r\nheight: 250px;\r\nborder-bottom: 1px solid #000000;\r\nmargin-bottom: 10px;\r\n}\r\n"; 
            $options['list_image_align']        = "left";
            $options['detail_image_align']      = "left";
            $options['list_search']             = "0";
            $options['list_filter']             = "1";
            $options['list_order_default']      = "bx_item_name";
            $options['list_sort_default']       = "asc"; 
            $options['fetch']                   = "bn"; 
            
            $chars = array("a","A","b","B","c","C","d","D","e","E","f","F","g","G","h","H","i","I","j","J", "k","K","l","L","m","M","n","N","o","O","p","P","q","Q","r","R","s","S","t","T","u","U","v","V","w","W","x","X","y","Y","z","Z","1","2","3","4","5","6","7","8","9","0");
            $max_elements = count($chars) - 1;
            $fileName = srand((double)microtime()*1000000);
            for($i=0;$i<12;$i++){
                $fileName .= $chars[rand(0,$max_elements)];
            }
            
            
            $options["export"] = md5($fileName);             
            update_option('bookx_options', $options);

            
            
        }
        /*else {
            $sql = "SHOW INDEXES IN " . $this->wpdb->prefix . "bx_item";

            $indexes = $this->wpdb->get_results($sql);
            foreach($indexes as $ind){
                if ($ind->Key_name == "bx_item_name"){
                    $gt04 = true;
                    break;
                }
            }
            if (!$gt04){
                $sql = "ALTER TABLE `" . $this->wpdb->prefix . "bx_item` ADD INDEX ( `bx_item_name` , `bx_item_author` , `bx_item_publisher` );";
                $this->wpdb->query($sql);
            }
        

        }*/
        
    }

    /**
    * Uninstalls the plugin by deleting the options and page
    */

    function bookx_uninstall(){
        
        /*$sql = "delete from `" . $this->wpdb->prefix . "posts` where `ID` = '" . $this->var->options['page_id'] . "' limit 1";
        $this->wpdb->query($sql);
        $sql = "drop table " . $this->wpdb->prefix . "bx_item";
        $this->wpdb->query($sql);
        delete_option('bookx_options');  
        */

    }




    
    /**
    * form actions to alter a book
    * 
    */
    
    function bookx_submit(){
        if ($_POST["_wpnonce"]){ $nonce = $_POST["_wpnonce"]; }
        else if ($_GET["_wpnonce"]){ $nonce = $_GET["_wpnonce"]; }

        if (!wp_verify_nonce($nonce)){ die('Security check'); }
        
        $comments = str_replace("\r\n", "<br />", strip_tags(htmlentities($_POST["comments"])));
        if ($_POST["action"] != "adds"){ 
            
            $this->bookx_startFetch(strip_tags($_POST["isbn"])); 
        }        
        
        if ($_POST["action"] == "adds"){
            $books = explode("\r\n", $_POST["books"]);
            foreach($books as $b){
                $this->bookx_startFetch(strip_tags($b));
                $sql = "insert into " . $this->wpdb->prefix . "bx_item ";
                $fields = '';
                $values = '';
                foreach(array_keys($this->bookArray) as $key){
                    $fields .= "bx_item_" . $key . ", ";
                    $values .= "'" . addslashes($this->bookArray[$key]) . "', ";
                }
                $fields .= "bx_item_comments, bx_item_sidebar, bx_item_date_added";
                $values .= "'$comments', 0, " . time();
                $sql .= "($fields) values ($values)";
                
                $this->wpdb->query($sql);
                //$this->wpdb->print_error();
                $code = "as";                
            } 
        }
        else if ($_POST["action"] == "add"){
            
            if ($this->bookArray["name"] == ''){
                $this->forms->bookx_form("ISBN Number Not Found.");
                return false;
            }   
                     
            $sql = "insert into " . $this->wpdb->prefix . "bx_item ";
            foreach(array_keys($this->bookArray) as $key){
                if ($key != "summary"){
                    $fields .= "bx_item_" . $key . ", ";
                    $values .= "'" . addslashes($this->bookArray[$key]) . "', ";
                }
            }
            if ($_POST["no_update"] == 1){
                $fields .= "bx_item_summary, ";
                $values .= "'" . addslashes($_POST["summary"]) . "', "; 
            }  
            else {
                $fields .= "bx_item_summary,";
                $values .= "'" . addslashes($this->bookArray["summary"]) . "', ";                
            }          
            $fields .= "bx_item_comments, bx_item_sidebar, bx_item_date_added, bx_item_no_update_desc";
            $values .= "'$comments', " . $_POST["sidebar"] . ", " . time() . ", '" . $_POST["no_update"] . "' ";
            $sql .= "($fields) values ($values)";

            $code = "a";
        }
        else if ($_POST["action"] == "modify"){

            if ($this->bookArray["name"] == ''){
                $this->forms->bookx_form("ISBN Number Not Found.");
                return false;
            }   
            $sql = "update " . $this->wpdb->prefix . "bx_item set ";
            foreach(array_keys($this->bookArray) as $key){     
                if ($key != "bx_item_summary"){
                    $sql .= "bx_item_" . $key . " = '" . addslashes($this->bookArray[$key]) . "', ";
                }                        
            }
            if ($_POST["no_update"] == 1){
                $sql .= "bx_item_summary = '" . $_POST["summary"] . "', ";     
            }
            else {
                $sql .= "bx_item_summary = '" . addslashes($this->bookArray["summary"]) . "', ";    
            }
            $sql .= "bx_item_comments = '$comments', bx_item_sidebar = " . $_POST["sidebar"] . ", bx_item_no_update_desc = '" . $_POST["no_update"] . "', ";
            $sql .= "bx_item_date_added = " . time() . " where bx_item_id = " . $_POST["id"] . " limit 1";

            $code = "m";
                        
        }
        else {
            $sql = "delete from " . $this->wpdb->prefix . "bx_item where bx_item_id = %d limit 1";
            $sql = $this->wpdb->prepare($sql, $_GET["id"]);        
            $code = "d";    
        }
        //print_r($this->bookArray);
        //die();
        if ($code != "as"){
            if (!$this->wpdb->query($sql)){
                $this->forms->bookx_form("SQL Query Failed<br /><br />" . mysql_error() . "<br /><br />$sql"); 
                return false;    
            }
            //$this->wpdb->print_error(); 
            //die("HERE");
        }
        
        $url = $this->baseURL . "&code=$code";
        $text = "<script language=\"javascript\">";
        $text .= "goToURL('$url'); ";
        //$text .= "window.location = '$url';";
        $text .= "</script>";

        $this->bookx_stroke($text);
    }
    
    /**
    * The administration view of the book list.
    * 
    */
    
    function bookx_list(){
        
        
        require_once(BOOKX_DIR . 'suitex/suitex_list.php'); 
        



        $text .= "<div class=\"wrap\">";
        $text .= "<h2>BookX</h2>";
        $text .= $this->status;

        $headers["bx_item_name"]        = "Title";
        $headers["bx_item_author"]      = "Author";
        $headers["bx_item_isbn"]        = "ISBN";
        $headers["bx_item_sidebar"]     = "Sidebar";

        $order = "bx_item_name";
        $sort  = "asc";
        
        if ($_GET["limit"]){ $limit = $_GET["limit"]; }
        else { $limit = 0; }

        $query = "select count(bx_item_id) from " . $this->wpdb->prefix . "bx_item";
        $count = $this->wpdb->get_var($query); 
        
        $query = "select ";
        $query .= "bx_item_name as item, ";
        $query .= "bx_item_author as author, ";
        $query .= "bx_item_isbn as isbn, ";
        $query .= "bx_item_sidebar as sidebar, ";
        $query .= "bx_item_id as id ";
        $query .= "from " . $this->wpdb->prefix . "bx_item ";
        $query .= "order by $order $sort limit $limit, " . $this->numberPerPage;
        
        $result = $this->wpdb->get_results($query);

        foreach($result as $row){
            $sidebar = $this->var->filter[$row->sidebar];
            
            if ($row->item){ $itemName = $row->item; }
            else { $itemName = "Import Failed"; }
            $rows[$row->id] = array($itemName, $row->author, $row->isbn, $sidebar);
        }
        $url = $this->baseURL . "&sub=form&id=";
        



        $list = new suitex_list();
        $list->search       = false;
        $list->orderForm    = false;
        $list->filters      = false;
        $list->omit         = array("cb");
        $list->paging       = true;
        $list->pluginPath   = BOOKX_URL;
        $list->setNum       = $this->numberPerPage;
        
        
        
        $list->startList($headers, $url, $order, $sort, $rows, $limit, $count);
        $text .= $list->text;
        $text .= "</div>";
        
        $this->bookx_stroke($text);       
        
    }
    
    /**
    * Addes the admin menu option using the WP hook.
    * 
    */
    
    
    function bookx_adminMenu(){
        add_management_page('BookX', 'BookX', 5, __FILE__, array($this, 'bookx_run')); 
    } 
}
?>