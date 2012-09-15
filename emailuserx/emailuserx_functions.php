<?php



class emailuserx {
	/**
	* The functions for EmailUserX
	* @package WordPress
	*/
    function __construct(){
        global $wpdb;
        $this->wpdb = $wpdb;
    }
    
    
    function emailuserx_admin_menu(){
    	/**
    	* The hook for the admin menu
    	*
    	* @param NULL
    	* @return NULL
    	*/
        add_management_page('EmailUserX', 'EmailUserX', 5, __FILE__, array($this, 'emailuserx_email'));
    }
    
    function emailuserx_email(){
        /**
        * Creates the inteface page and sends the email upon submit.
        * 
        * @param NULL
        * @return NULL
        */
        
        if ($_POST["confirm"] == 1){
            if (!wp_verify_nonce($_POST["_wpnonce"])){ die('Security check'); }            

            require_once(ABSPATH . $this->pluginBase . DIRECTORY_SEPARATOR . 'class.phpmailer.php');  
            $mail = new PHPMailer();  
            $mail->From = get_option('admin_email');
            $mail->FromName = get_option('blogname') . " Mailer"; 
            $mail->AddReplyTo(get_option('admin_email'), get_option('blogname') . " Mailer"); 
            $mail->IsHTML(true);  
            
            $mail->Subject = $_POST["subject"];
            if ($_POST["format"] == "html"){ $mail->MsgHTML($_POST["message"]); }
            $mail->AltBody = strip_tags(str_replace("<br>", '\r\n', $_POST["message"]));   
   
            $list = explode(",", $_POST["memberList"]);
            foreach($list as $id){
                $mail->addAddress($id); 
                $mail->Send();
                $mail->ClearAddresses();     
            }
            $status = "Email(s) Sent";
            
            
            
            
            
            
        }
        $text = "<script type=\"text/javascript\" src=\"../wp-content/plugins/emailuserx/javascript/transfer.js\"></script>";  
        $text .= "<div class=\"wrap\">";
        $text .= "<h2>EmailUserX</h2>";
        $text .= "<span style=\"font-weight: bold; color: #FF0000;\">$status</span>";        
        
        $text .= "<div id=\"poststuff\" class=\"metabox-holder\">";
        $text .= "<div id=\"post-body\" class=\"has-sidebar\">";
        $text .= "<div id=\"post-body-content\" class=\"has-sidebar-content\">";
        $text .= "<div class=\"inside\">";
        $text .= "<form method=\"post\" action=\"tools.php?page=emailuserx/emailuserx_functions.php\" id=\"myForm\" name=\"myForm\">";
        $text .= "<input type=\"hidden\" name=\"_wpnonce\" value=\"" . wp_create_nonce() . "\" />";
        $text .= "<input type=\"hidden\" name=\"confirm\" value=\"1\" />";

        $text .= "<table class=\"form-table\">";
        $text .= "<tr class=\"form-field\">";  
        $text .= "<td><strong>Select Users:</strong></td>";
        $text .= "<td>";
        $text .= "<input type=\"hidden\" name=\"members\" value=\"\" />"; 
        $results = $this->wpdb->get_results("select ID, user_login, user_email from " . $this->wpdb->prefix . "users order by user_login");
        
        $text .= "<select style=\"height: auto;\" name=\"userList\" size=\"10\" multiple onDblClick=\"moveSelectedOptions(this.form.userList, this.form.memberList, this.form.members);\">";
        foreach($results as $row){
            $text .= "<option value=\"" . $row->user_email . "\">" . $row->user_login . "</option>";
        }
        $text .= "</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        $text .= "<select style=\"height: auto;\" name=\"memberList\" size=\"10\" multiple onDblClick=\"moveSelectedOptions(this.form.memberList, this.form.userList, this.form.members, false);\">";
        $text .= "</select>";
        
        
        //$text .= "<script language=\"javascript\">selectAllOptions(document.myForm.memberList); document.myForm.members.value = getSelectedValues(document.myForm.memberList); </script>";
        $text .= "</td></tr>";          
        $text .= "<tr class=\"form-field\">";  
        $text .= "<td><strong>Subject:</strong></td>";
        $text .= "<td><input type=\"text\" id=\"subject\" name=\"subject\" value=\"\" />";
        $text .= "</td></tr>";  
        
        $text .= "<tr class=\"form-field\">";  
        $text .= "<td><strong>Message:</strong></td>";
        $text .= "<td><textarea name=\"message\" id=\"message\" style=\"height: 150px;\" ></textarea>";
        $text .= "</td></tr>";                 
        
        $text .= "<tr class=\"form-field\">";  
        $text .= "<td><strong>Format:</strong></td><td>";
        $text .= "<input type=\"radio\" name=\"format\" id=\"format\" value=\"html\" checked  style=\"width: 15px;\" />HTML &nbsp;&nbsp;";
        $text .= "<input type=\"radio\" name=\"format\" id=\"format\" value=\"plain\" style=\"width: 15px;\" />Plain Text ";
        $text .= "</td></tr>";  
        
        
        $text .= "</table>"; 
        $text .= "</div></div>";
        $text .= "<p class=\"submit\"><input type=\"submit\" name=\"Submit\" value=\"Save Changes\" />";
        $text .= "</div></div></div>";
        print($text);
        
        
        
        
        
    }
}

?>