<?php

class monitorx_admin {
    
    var $wpdb;
    var $options        = array();
    var $baseURL        = "tools.php?page=monitorxx/includes/monitorx_admin.php";
    var $filter         = array();
    var $numberPerPage  = 50;    
    
    function __construct(){
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->monitorx_checkCode();    
        
    }
    
    function monitorx_install(){
        $page                   = array();
        $page['post_type']      = 'page';                                       
        $page['post_title']     = 'MonitorX';
        $page['post_name']      = 'monitorx';
        $page['post_status']    = 'publish';
        $page['comment_status'] = 'closed';
        $page['post_content']   = 'This page is for your MonitorX Plugin.';
        
        
        $page_id = wp_insert_post($page);  
        
        $options = array();
        $options['page_id'] = $page_id;
        $options['site_key'] = $this->monitorx_generateKey();
        $options['site_type'] = "client";
        
        
        update_option('monitorx_options', $options);        

    }
    
    function monitorx_toggleClient(){
        if ($this->options["site_type"] == "client"){
            $sql = "CREATE TABLE IF NOT EXISTS `" . $this->wpdb->prefix . "monitorx_site` (`monitorx_site_id` int(10) NOT NULL AUTO_INCREMENT, `monitorx_site_name` varchar(50) NOT NULL,`monitorx_site_uri` varchar(255) NOT NULL, `monitorx_site_key` varchar(255) NOT NULL, PRIMARY KEY (`monitorx_site_id`), KEY `monitorx_site_key` (`monitorx_site_key`)) ENGINE=MyISAM DEFAULT CHARSET=latin1";
            $this->wpdb->query($sql);
        
            $sql = "CREATE TABLE IF NOT EXISTS `" . $this->wpdb->prefix . "monitorx_status` (  `monitorx_site_id` int(10) NOT NULL,  `monitorx_status_date` int(10) NOT NULL,  `monitorx_status_status` tinyint(1) NOT NULL,  `monitorx_status_notify` tinyint(1) NOT NULL,  `monitorx_status_type` tinyint(1) NOT NULL,  KEY `monitorx_site_id` (`monitorx_site_id`),  KEY `monitorx_status_type` (`monitorx_status_type`)) ENGINE=MyISAM DEFAULT CHARSET=latin1";
            $this->wpdb->query($sql);        
            $this->options["site_type"] == "server";
            update_option("monitorx_options", $this->options);
        }
        else {
            $sql = "drop table " . $this->wpdb->prefix . "monitorx_status";
            $this->wpdb->query($sql);
            $sql = "drop table " . $this->wpdb->prefix . "monitorx_site";
            $this->wpdb->query($sql);
            $this->options["site_type"] == "client";
            update_option("monitorx_options", $this->options);
        }
    }
    
    function monitorx_uninstall(){
        $sql = "delete from `" . $this->wpdb->prefix . "posts` where `ID` = '" . $this->options['page_id'] . "' limit 1";
        $this->wpdb->query($sql);


        delete_option('monitorx_options');        
        
    }
    
    function monitorx_generateKey(){
        $chars = array("a","A","b","B","c","C","d","D","e","E","f","F","g","G","h","H","i","I","j","J", "k","K","l","L","m","M","n","N","o","O","p","P","q","Q","r","R","s","S","t","T","u","U","v","V","w","W","x","X","y","Y","z","Z","1","2","3","4","5","6","7","8","9","0");
        $max_elements = count($chars) - 1;
        $key = srand((double)microtime()*1000000);
        for($i=0;$i<15;$i++){
            $key .= $chars[rand(0,$max_elements)];
        }
        $key = md5($key);
        return $key;
    }    
    
    function monitorx_adminMenu(){
        add_management_page('MonitorX', 'MonitorX', 5, __FILE__, array($this, 'monitorx_run'));        
        
    }
    
    function monitorx_checkCode(){
        
    }
    
    function monitorx_admin(){
        //update type
        //notify type
        //options
        if ($_POST["_wpnonce"]){ 
            if (!wp_verify_nonce($_POST["_wpnonce"])){ die('Security check'); }
            $this->options["type"] = $_POST["type"];
            $this->options["updateKey"] = $_POST["updateKey"];
                        
            
            
            
            
            update_option('monitorx_options', $this->options);
            
  
            
            
              
        }
        

        
        $this->nonce = wp_create_nonce();
        
        $text = "<div class=\"wrap\">";
        $text .= "<h2>MonitorX</h2>";
        $text .= "<br />";


        $text .= "<div id=\"poststuff\" class=\"metabox-holder\">";
        $text .= "<div id=\"post-body\" class=\"has-sidebar\">";
        $text .= "<div id=\"post-body-content\" class=\"has-sidebar-content\">";
        $text .= "<div class=\"postbox\">";
        $text .= "<h3><label>Configuration Options</label></h3>";
        $text .= $this->status;
        
        $text .= "<div class=\"inside\">";
        $text .= "<form method=\"post\" action=\"" . $this->baseURL . "&sub=admin&code=\">";
        $text .= "<input type=\"hidden\" name=\"_wpnonce\" value=\"" . $this->nonce . "\" />";
        
        

        $text .= "<table class=\"form-table\">";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Update Type</strong></td>";
        $text .= "<td><input type=\"text\" name=\"dir\" value=\"" . stripslashes($this->options["dir"]) . "\" />";
        $text .= "</td></tr>";

        $text .= "</table>";        
        
        
        
        $text .= "<p class=\"submit\"><input type=\"submit\" name=\"Submit\" value=\"Save Changes\" />";
        $text .= "</p></form>";
        $text .= "</div></div>";
        
        $text .= "</div></div>";
        $text .= "</div></div>";
        $this->svnx_stroke($text);           
        
    }
    

    
    function monitorx_run(){
        switch($_GET["sub"]){
            case "admin":
                $this->monitorx_admin();
                break;
                
            case "form":
                $this->monitorx_form();
                break;
                
            case "submit":
                $this->monitorx_submit();
                break;
            
            case "refresh":
                $this->obj->monitorx_refresh(false);
                break;    
            
            
            case "list": 
            default:
                $this->monitorx_list();
                break;
            
            
            
        }    
    }
    

    
    function monitorx_submit(){
        
        
    }   
    
    function monitorx_form(){
        
        
    }
    
    function monitorx_list(){
        print_r($this->options);
        if ($this->options["site_type"] == "client"){
            //CLIENT, do you want me to be a serveer?
        }
        else {
        
            require_once(MONITORX_DIR . 'includes/suitex_list.php'); 

            $text .= "<div class=\"wrap\">";
            $text .= "<h2>MonitorX</h2>";


            $headers["site"]        = "Site";
            $headers["status"]      = "Status";
            $headers["version"]     = "Wordpress Version";
            $headers["plugin"]      = "Plugin Status";
            $headers["url"]         = "Link";

            $order = "monitorx_site_name";
            $sort  = "asc";
        
       
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
                $sidebar = $this->filter[$row->sidebar];
            
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
            $this->paging       = true;
            $this->pluginPath   = BOOKX_URL;
            
        
        
            $list->startList($headers, $url, $order, $sort, $rows, $limit, $this->numberPerPage);
            $text .= $list->text;
            $text .= "</div>";
        
            
        }
        $this->monitorx_stroke($text);             
        
    }
    
    function monitorx_stroke($text){
        $body = $this->monitorx_headerMenu();
        $body .= $text;
        print($body);
    }
    
    function monitorx_headerMenu(){
        
    }
    
    
    
    
}
  
?>
