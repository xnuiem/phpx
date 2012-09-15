<?php
class multiX {
    /**
    * The functions for multiX
    * @global   array   $options
    */
    
    function __construct(){
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->options = get_option('multix_options');
        
        if ($_GET["code"]){
            $codeArray["m"] = "Website Modified";
            $codeArray["g"] = "New Key Generated";
            $codeArray["a"] = "Website Added";
            $codeArray["d"] = "Website Deleted";
            $codeArray['u'] = 'Invalid Username/Password';
            $codeArray['r'] = 'Site already exists on remote host';
            $codeArray['l'] = 'Site already exists';
            $this->status = "<br /><b><span style=\"color:#FF0000;\">" . $codeArray[$_GET["code"]] . "</span></b>";  
        }
         
        
    }

    function multix_install(){
        /**
        * Installs the plugin by creating the options
        * @param NULL
        * @return NULL
        */
                
        if (!is_plugin_active('phpx/phpx.php')){
            die('MultiX requires the PHPX Framework.  Please install PHPX and then reinstall MutliX.');
        }
        
        if (!get_option('multix_options')){
            $sql = "CREATE TABLE `" . $this->wpdb->prefix . "multix` (`multix_id` int(10) NOT NULL AUTO_INCREMENT,`multix_name` varchar(50) NOT NULL,`multix_uri` varchar(255) NOT NULL,`multix_key` varchar(255) NOT NULL, PRIMARY KEY (`multix_id`)) ENGINE=MyISAM";
            $this->wpdb->query($sql);
            update_option('multix_options', $this->options);
        }

    }



    function multix_uninstall(){
        /**
        * Uninstalls the plugin by deleting the options
        *
        * @param NULL
        * @return NULL
        */
        $sql = "drop table `" . $this->wpdb->prefix . "mulitx`";
        $this->wpdb->query($sql);
        delete_option('multix_options');


    }



    function multix_admin_menu(){
        /**
        * The hook for the admin menu
        *
        * @param NULL
        * @return NULL
        */
        add_management_page('MultiX', 'MultiX', 5, __FILE__, array($this, 'multix_run'));
    }
    
    
    function multix_stroke($text){
        $body = '<div id="phpxContainer">'; 
        $body .= $this->adminHeaderMenu();
        $body .= $text;
        $body .= '<div id="dimmer"></div><div id="confirmWin"><div id="confirmWinText"></div><div id="buttonArea"></div></div>';
        $body .= '</div>';
        print($body);
        
    }
    
    /**
    * Creates the header menu
    * 
    * @return   string  $text
    */
    
    function adminHeaderMenu(){
        if (!$this->nonce){ $this->nonce = wp_create_nonce(); }
        
        $text .= "&nbsp;&nbsp;<a href=\"" . $this->baseURL . "&sub=list\">List Sites</a>"; 
        $text .= "&nbsp;&nbsp;<a href=\"" . $this->baseURL . "&sub=form\">Add Site</a>"; 
        $text .= "&nbsp;&nbsp;<a href=\"javascript:confirmAction('Are you sure?  This will invalidate all incoming authentication requests until the new key is populated.', '" . $this->baseURL . "&sub=generate');\">Generate New Key</a>";        
        
        return $text;
    }   
     
    function multix_run(){
        switch($_GET["sub"]){
            case "form":
                $this->multix_form();
                break;
            
            case "submit":
                $this->multix_submit();
                break;
                
            case 'auto':
                $this->multix_autoAdd();
                break;
                
            case "generate":
                $this->multix_generateKey();
                break;
                
            case "list":
            default:
                $this->multix_default_page();
                break;
                
        }
        $this->multix_stroke($this->text);
        
    }
    
    function multix_autoAdd(){
        global $current_user;

        $fields = array();
        $fields['user'] = $current_user->user_login;
        $fields['pass'] = $_POST['password'];
        $fields['key']  = $this->options['key'];
        $fields['uri']  = get_bloginfo('wpurl');
        $fields['name'] = get_bloginfo('name');
        $fields['action'] = 'mxadd';
        $fields['multix_login'] = 1;
        
        $fieldString = '';
        foreach($fields as $key => $value){
            $fieldString .= $key . "=" . urlencode($value) . "&";
        }        
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $_POST['uri']);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldString);  
        $response = curl_exec($ch);
        curl_close($ch);
        
        if (substr_count($response, '|') != 0){
            $res = explode('|', $response);
            $id = $this->wpdb->get_var($this->wpdb->prepare('select multix_id from ' . $this->wpdb->prefix . 'multix where multix_key = %s limit 1', $res[0]));
            if (!$id){
                $this->wpdb->query($this->wpdb->prepare('insert into ' . $this->wpdb->prefix . 'multix (multix_name, multix_uri, multix_key) values (%s, %s, %s)', $res[1], $res[2], $res[0]));
                $code = 'a';
            }
            else {
                $code = 'l';  
            }            
            
        }   
        else {
            $code = $response;
        }       
        $text = "<script language=\"javascript\">";
        $text .= "goToURL('" . $this->baseURL . "&code=$code'); ";
        $text .= "</script>";        
        $this->text = $text;   
    }
    
    function multix_generateKey($noOutput = false){
        $chars = array("a","A","b","B","c","C","d","D","e","E","f","F","g","G","h","H","i","I","j","J", "k","K","l","L","m","M","n","N","o","O","p","P","q","Q","r","R","s","S","t","T","u","U","v","V","w","W","x","X","y","Y","z","Z","1","2","3","4","5","6","7","8","9","0");
        $max_elements = count($chars) - 1;
        $key = srand((double)microtime()*1000000);
        for($i=0;$i<15;$i++){
            $key .= $chars[rand(0,$max_elements)];
        }
        $key = md5($key);
        $this->options["key"] = $key;  
        update_option("multix_options", $this->options);  
        
        if ($noOutput == false){
            $text = "<script language=\"javascript\">";
            $text .= "goToURL('" . $this->baseURL . "&code=g'); ";
            $text .= "</script>";        
            $this->text = $text;
        }
    }  
    
    function multix_default_page(){
        $text = "<div class=\"wrap\">";
        $text .= "<h2>MultiX</h2>";
        $text .= "MulitX is your multiple wordpress site administration plugin.  It allows you to login to remote sites without having to \"login\"";        
        $text .= "<br /><br />";
        $text .= $this->status;
        $text .= "<br /><br />";
        $text .= "The current website key is: " . $this->options["key"];
        
        require_once(PHPX_DIR . 'phpx_list.php');
        
        $headers["multix_name"] = "Website Name";
        $headers["multix_uri"] = "Website URI";
        $headers["Login"] = "Login";
        
        $sql = "select * from " . $this->wpdb->prefix . "multix";
        $result = $this->wpdb->get_results($sql);
        $rows = array();
        global $current_user;
       
        foreach($result as $row){
            $loginForm = "<form method=\"post\" action=\"" . $row->multix_uri . "\">";
            $loginForm .= "<input type=\"hidden\" name=\"multix_login\" value=\"1\" />";
            $loginForm .= "<input type=\"hidden\" name=\"log\" value=\"" . $current_user->user_login . "\" />";
            $loginForm .= "<input type=\"hidden\" name=\"token\" value=\"" . $this->options["key"] . "\" />";
            $loginForm .= "<input type=\"hidden\" name=\"key\" value=\"" . $row->multix_key . "\" />";
            $loginForm .= "<input type=\"hidden\" name=\"ref\" value=\"" . $_SERVER["HTTP_HOST"] . "\" />";
            $loginForm .= '<input type="hidden" name="action" value="mxlogin" />';
            $loginForm .= "<p class=\"submit\"><input name=\"submit\" type=\"submit\" value=\"Login\" /></p>";
            $loginForm .= "</form>";
            $rows[$row->multix_id] = array($row->multix_name, $row->multix_uri, $loginForm);
        }
        
        $list = new phpx_list();
        $list->search       = false;
        $list->orderForm    = false;
        $list->filters      = false;
        $list->omit         = array("cb");
        $this->paging       = false;
        $this->pluginPath   = $this->pluginBase;
        $url = $this->baseURL . "&sub=form&id=";
        
        
        $list->startList($headers, $url, "multix_name", "asc", $rows, "0", "100");
        $text .= $list->text;        
        
        
        
        
        $text .= "</div>";
        $this->text = $text;      

        
    }
    
    function multix_submit(){
        if ($_POST["_wpnonce"]){ $nonce = $_POST["_wpnonce"]; }
        else if ($_GET["_wpnonce"]){ $nonce = $_GET["_wpnonce"]; }

        if (!wp_verify_nonce($nonce)){ die('Security check'); }
                
        if ($_POST["id"] != ''){
            $code = "m";  
   
            $sql = "update " . $this->wpdb->prefix . "multix set multix_name = %s, multix_uri = %s, multix_key = %s where multix_id = %d limit 1";
            $query = $this->wpdb->prepare($sql, $_POST["name"], $_POST["uri"], $_POST["key"], $_POST["id"]);  
        }   
        else if ($_GET["id"] != ''){
            $code = "d";
            $sql = "delete from " . $this->wpdb->prefix . "multix where multix_id = %d limit 1";
            $query = $this->wpdb->prepare($sql, $_GET["id"]);
            
        } 
        else {
            $sql = "insert into " . $this->wpdb->prefix . "multix (multix_name, multix_uri, multix_key) values (%s, %s, %s)";
          
            
            $query = $this->wpdb->prepare($sql, $_POST["name"], $_POST["uri"], $_POST["key"]);
            $code = "a";
            
        }
        
        $this->wpdb->query($query);
        
        $text = "<script language=\"javascript\">";
        $text .= "goToURL('" . $this->baseURL . "&code=$code'); ";
        $text .= "</script>";        
        $this->text = $text;                
        
    }

    function multix_form(){
        /**
        * The administration page for updating options
        *
        * @param NULL
        * @return NULL
        */

        if ($_GET["id"] != ''){
            $action = "modify";
            
            
            $sql = "select * from " . $this->wpdb->prefix . "multix where multix_id = %d limit 1";
            $row = $this->wpdb->get_row($this->wpdb->prepare($sql, $_GET["id"]));
            $label = "Modify " . $row->multix_name; 
            
        }
        else {
            $action = "add";
            $label = "Add Website";
        }

        $this->nonce = wp_create_nonce(); 
        


        
        
        $text = "<div id=\"poststuff\" class=\"metabox-holder\">";
        $text .= "<div id=\"post-body\" class=\"has-sidebar\">";
        $text .= "<div id=\"post-body-content\" class=\"has-sidebar-content\">";

        $text .= "<div class=\"postbox\">";
        $text .= "<h3><label>$label</label></h3>";
        $text .= "<div class=\"inside\">";

        $text .= "<form method=\"post\" action=\"" . $this->baseURL . "&sub=submit\">";
        $text .= "<input type=\"hidden\" name=\"_wpnonce\" value=\"" . $this->nonce . "\" />";
        
        
        if ($action == "modify"){
            $text .= "<input type=\"hidden\" name=\"id\" value=\"" . $row->multix_id . "\" />";
        }
        

        $text .= "<table class=\"form-table\">";
        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"member_number\">Site Name</label></th>";
        $text .= "<td><input type=\"text\" name=\"name\" value=\"" . $row->multix_name . "\" />";
        $text .= "</td></tr>";
        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"member_number\">Site URI</label></th>";
        $text .= "<td><input type=\"text\" name=\"uri\" value=\"" . $row->multix_uri . "\" />";
        $text .= "</td></tr>";
        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"member_number\">Site Key</label></th>";
        $text .= "<td><input type=\"text\" name=\"key\" value=\"" . $row->multix_key . "\" />";
        $text .= "</td></tr>";        

        $text .= "</table>";



        $text .= "<p class=\"submit\"><input type=\"submit\" name=\"Submit\" value=\"Submit\" />";
        if ($action == "modify"){
            $deleteURL = $this->baseURL . "&sub=submit&id=" . $_GET["id"] . "&_wpnonce=" . $this->nonce;
            $text .= "&nbsp;&nbsp;<input type=\"button\" value=\"Delete\" onClick=\"confirmAction('Are you sure you want to delete this site?', '$deleteURL');\" />";
        }
        $text .= "</p></form></div></div>";
        
        if (in_array  ('curl', get_loaded_extensions()) && $action != 'modify'){
        
        
        
            $text .= "<div class=\"postbox\">";
            $text .= "<h3><label>Automatically Add Site</label></h3>";
            $text .= "<div class=\"inside\">";

            $text .= "<form method=\"post\" action=\"" . $this->baseURL . "&sub=auto\">";
            $text .= "<input type=\"hidden\" name=\"_wpnonce\" value=\"" . $this->nonce . "\" />";
            $text .= "<table class=\"form-table\">";
            $text .= "<tr class=\"form-field form-required\">";
            $text .= "<th scope=\"row\" valign=\"top\"><label for=\"member_number\">Site URI</label></th>";
            $text .= "<td><input type=\"text\" name=\"uri\" value=\"" . $row->multix_uri . "\" />";
            $text .= "</td></tr>";
            $text .= "<tr class=\"form-field form-required\">";
            $text .= "<th scope=\"row\" valign=\"top\"><label for=\"member_number\">Password on Remote Site</label></th>";
            $text .= "<td><input type=\"password\" name=\"password\" value=\"\" />";
            $text .= "</td></tr>";        

            $text .= "</table>";



            $text .= "<p class=\"submit\"><input type=\"submit\" name=\"Submit\" value=\"Submit\" />";
            $text .= "</p></form></div></div>";        
        }            
        $text .= "</div></div>";
        
         
        $this->text = $text;
    }
    
    function multix_api(){
        switch($_POST['action']){
            case 'mxlogin':
                $this->multix_login();
                break;
                
            case 'mxcheck':
                $this->multix_checkStats();
                break;
                
            case 'mxadd':
                $this->multix_addSite();
                break;
        }
    }
    
    function multix_addSite(){
        //check permissions
        $creds = array('user_login' => urldecode($_POST['user']), 'user_password' => urldecode($_POST['pass']), 'remember' => false);
        $user = wp_signon($creds);
        if (is_wp_error($user)){
            //print($user->get_error_message());
            print('u');
            exit;
        }
        
        if ($this->options['key'] == ''){
            $this->multix_generateKey(true);
        }
        
        //looks good, create the relationship
        $id = $this->wpdb->get_var($this->wpdb->prepare('select multix_id from ' . $this->wpdb->prefix . 'multix where multix_key = %s limit 1', $_POST['key']));
        if (!$id){
            $this->wpdb->query($this->wpdb->prepare('insert into ' . $this->wpdb->prefix . 'multix (multix_name, multix_uri, multix_key) values (%s, %s, %s)', $_POST['name'], $_POST['uri'], $_POST['key']));
            print($this->options['key'] . '|' . get_bloginfo('name') . '|' . get_bloginfo('wpurl'));        
        }
        else {
            print('r');
        }
        exit;
    }
    
    function multix_checkStats(){
        
    }
    
    function multix_checkUser(){
        $user = get_userdatabylogin($_POST["log"]);
        return new WP_User($user->ID); 
    }
    
    function multix_login(){

        if ($_POST["key"] && $_POST["multix_login"]){
            if ($_POST["key"] != $this->options["key"]){
                die("Key Mismatch");
            }

            $uri = $this->wpdb->get_var($this->wpdb->prepare("select multix_uri from " . $this->wpdb->prefix . "multix where multix_key = %s limit 1", $_POST["token"]));
            
            if (!$uri){
                die("Invalid Token");
            }    
            
            $check = str_replace("http://", '', $_POST["ref"]);
            $check = str_replace("https://", '', $check);
            
            $uri = str_replace("http://", '', $uri);
            $uri = str_replace("https://", '', $uri);
            
                
            if (substr_count($uri, $check) == 0){
                die("Invalid Referer");
            }
            
            if ($_SERVER["SERVER_PORT"] == "443"){ $secure = true; }
            else { $secure = false; }
            
            
            $user = $this->multix_checkUser();
            wp_set_auth_cookie($user->ID, true, $secure);
            do_action('wp_login', $user->user_login);                
           
            header("Location: wp-admin/index.php");
            exit();
            
        }
    }
    
    
    function multix_dashboard_setup(){
        wp_add_dashboard_widget('multix_sites', 'MultiX Sites', array($this, 'multix_dashboard'));
    }
    
    function multix_dashboard(){
        require_once(PHPX_DIR . 'phpx_list.php');
        $text = 'Click on a link below to be taken and automatically logged into that website.<br /><br />';
        
        $result = $this->wpdb->get_results('select * from ' . $this->wpdb->prefix . 'multix');
        $rows = array();
        global $current_user;
       
        //$headers["multix_name"] = "Website Name";
        //$headers["Login"] = "Login";
        
        foreach($result as $row){
            $loginForm = "<form method=\"post\" action=\"" . $row->multix_uri . "\" class=\"phpx\">";
            $loginForm .= "<input type=\"hidden\" name=\"multix_login\" value=\"1\" />";
            $loginForm .= "<input type=\"hidden\" name=\"log\" value=\"" . $current_user->user_login . "\" />";
            $loginForm .= "<input type=\"hidden\" name=\"token\" value=\"" . $this->options["key"] . "\" />";
            $loginForm .= "<input type=\"hidden\" name=\"key\" value=\"" . $row->multix_key . "\" />";
            $loginForm .= "<input type=\"hidden\" name=\"ref\" value=\"" . $_SERVER["HTTP_HOST"] . "\" />";
            $loginForm .= '<input type="hidden" name="action" value="mxlogin" />';
            $loginForm .= "<p class=\"submit\"><input name=\"submit\" type=\"submit\" value=\"Login\" /></p>";
            $loginForm .= "</form>";
            $rows[$row->multix_id] = array($row->multix_name, $loginForm);
        }
        
        $list = new phpx_list();
        $list->search       = false;
        $list->orderForm    = false;
        $list->filters      = false;
        $list->omit         = array("cb");
        $this->paging       = false;
        $this->pluginPath   = $this->pluginBase;
        
        $list->startList($headers, $url, "multix_name", "asc", $rows, "0", "100");
        $text .= $list->text;           
        print($text);
    }
}

?>
