<?php
/**
*  the front end class for USecureX
*/
class usecurex_front {
    var $wpdb;
    /**
    * the construct function, does nothing but setup global variables.
    * 
    */
    function __construct(){
        global $wpdb;
        $this->wpdb = $wpdb;
        //$this->wpdb->show_errors = true;
        $this->options = get_option("usecurex_options");
    }
    /**
    * checks to see if this user has permission to view that page.  If not, redirects to setting page.
    * 
    */
    
    function usecurex_front_init(){
        $fail = true;
        
        global $post, $user_ID;

        $query = "select count(usecurex_group_id) from " . $this->wpdb->prefix . "usecurex_link where usecurex_field_name = 'page_id' and usecurex_field_id = '" . $post->ID . "' limit 1";
        $groups = $this->wpdb->get_var($query);
        
        if ($groups != 0){
            $query = "select count(usecurex_group_id) from " . $this->wpdb->prefix . "usecurex_link where usecurex_group_id in (select usecurex_group_id from " . $this->wpdb->prefix . "usecurex_link where usecurex_field_name = 'page_id' and usecurex_field_id = '" . $post->ID . "') and usecurex_field_name = 'user_id' and usecurex_field_id = '$user_ID' limit 1";
            $count = $this->wpdb->get_var($query);
            if ($count != 0){ $fail = false;}
        }
        else { $fail = false; }
        
        if ($fail != false){
            header("Location: " . $this->options["default_page"]);
            exit;
        }
    }
}  
?>
