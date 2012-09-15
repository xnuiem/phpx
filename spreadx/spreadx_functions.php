<?php

class spreadX {
	/**
	* The functions for SpreadX, both admin and front-end
	* @package WordPress
	*/

    function __construct(){
        $this->options = get_option('spreadx_options');
    }
    
    function spreadx_install(){
	    /**
	    * Installs the plugin by creating the options
	    * @param NULL
	    * @return NULL
	    */

        $options = array();
        $options["sites"]   = array("digg", "stumble", "technorati", "facebook", "delicious");
        $options["buttons"] = '<div id="spreadx">&nbsp;<a href="http://digg.com/submit?phase=2&url=::URL::" target="_new"><img src="' . get_option('siteurl') . '/wp-content/plugins/spreadx/images/digg.gif" alt="Digg" border="0" /></a>&nbsp;&nbsp;<a href="http://www.facebook.com/share.php?u=::URL::" target="_new"><img src="' . get_option('siteurl') . '/wp-content/plugins/spreadx/images/facebook.gif" alt="Facebook" border="0" /></a>&nbsp;&nbsp;<a href="http://www.stumbleupon.com/submit?url=::URL::&title=::TITLE::" target="_new"><img src="' . get_option('siteurl') . '/wp-content/plugins/spreadx/images/stumble.gif" alt="StumbleUpon" border="0" /></a>&nbsp;&nbsp;<a href="http://technorati.com/faves?add=::URL::" target="_new"><img src="' . get_option('siteurl') . '/wp-content/plugins/spreadx/images/technorati.gif" alt="Technorati" border="0" /></a>&nbsp;&nbsp;<a href="http://del.icio.us/post?url=::URL::&title=::TITLE::" target="_new"><img src="' . get_option('siteurl') . '/wp-content/plugins/spreadx/images/delicious.gif" alt="Deli.cio.us" border="0" /></a>&nbsp;</div>';
        $options["scope"]   = array("post");
        
        update_option('spreadx_options', $options);
        	
        

    }



    function spreadx_uninstall(){
    	/**
    	* Uninstalls the plugin by deleting the options
    	*
    	* @param NULL
    	* @return NULL
    	*/

    	delete_option('spreadx_options');

    }



    function spreadx_admin_menu(){
    	/**
    	* The hook for the admin menu
    	*
    	* @param NULL
    	* @return NULL
    	*/
        add_management_page('SpreadX', 'SpreadX', 6, __FILE__, array($this, 'spreadx_admin_page'));
    }

    function spreadx_admin_page(){
	    /**
	    * The administration page for updating options
	    *
	    * @param NULL
	    * @return NULL
	    */

        clearstatcache();
        $scope = array();
        $buttons = '';
        $sites = array();
        if ($_POST["action"] == "update"){
            $message    = "Websites Updated";
            if ($_POST["sites"]){
                $buttons = "<div id=\"spreadx\">";
                foreach($_POST["sites"] as $site){
                    if (in_array($site, array_keys($this->pages))){
                        $sites[] = $site;
                        $buttons .= "&nbsp;<a href=\"" . $this->pages[$site][2] . "\" target=\"_new\">";
                        $buttons .= "<img src=\"" . get_option('siteurl') . "/wp-content/plugins/spreadx/images/" . $site . ".gif\" ";
                        $buttons .= "alt=\"" . $this->pages[$site][0] . "\" border=\"0\" />";
                        $buttons .= "</a>&nbsp;";
                            
                    }
                }

                $buttons .= "</div>";
            }
            
            if ($_POST["scope"]){
                foreach($_POST["scope"] as $s){
                    $scope[] = $s;
                }
            }

            $this->options["sites"]     = $sites;
            $this->options["buttons"]   = $buttons;
            $this->options["scope"]     = $scope;
            //$this->options["twitter_username"] = $_POST["twitter_username"];
            update_option("spreadx_options", $this->options);
        }
        
        $text .= "<div class=\"wrap\">";
        $text .= "<h2>SpreadX</h2>";

        if ($message){ $text .= "<br /><b><span style=\"color:#FF0000;\">$message</span></b>"; }
		$text .= "<div id=\"poststuff\" class=\"metabox-holder\">";
		$text .= "<div id=\"post-body\" class=\"has-sidebar\">";
		$text .= "<div id=\"post-body-content\" class=\"has-sidebar-content\">";

        $text .= "<div class=\"postbox\">";
        $text .= "<h3>SpreadX General Options</h3>";
		$text .= "<div class=\"inside\">";

        $text .= "<form method=\"post\" action=\"\">";
        $text .= "<input type=\"hidden\" name=\"action\" value=\"update\" />";

        $text .= "<table class=\"form-table\">";
        
        foreach(array_keys($this->pages) as $page){
            if (in_array($page, $this->options["sites"])){ $c = "checked"; }
            else { $c = ''; }
            $text .= "<tr class=\"form-field form-required\">";
            $text .= "<th scope=\"row\" valign=\"top\"><label><a href=\"" . $this->pages[$page][1] . "\" target=\"_new\">" . $this->pages[$page][0] . "</a>:</label></th>";
            $text .= "<td><input type=\"checkbox\" name=\"sites[]\" value=\"$page\" $c />";
            $text .= "</td></th></tr>";
        }
        if (in_array("post", $this->options["scope"])){ $c = "checked"; }
        else { $c = ''; }        
        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label>Posts:</label></th>";
        $text .= "<td>";

        $text .= "<input type=\"checkbox\" name=\"scope[]\" value=\"post\" $c />";
        $text .= "</td></th></tr>";
        
        if (in_array("page", $this->options["scope"])){ $c = "checked"; }
        else { $c = ''; }
        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label>Pages:</label></th>";
        $text .= "<td>";

        $text .= "<input type=\"checkbox\" name=\"scope[]\" value=\"page\" $c />";
        $text .= "</td></th></tr>";


        //$text .= "<tr class=\"form-field form-required\">";
        //$text .= "<th scope=\"row\" valign=\"top\"><label>Twitter Username:</label></th>";
        //$text .= "<td>";
        //$text .= "<input type=\"text\" name=\"twitter_username\" value=\"" . $this->options["twitter_username"] . "\" />";
        //$text .= "</td></th></tr>";        
        
        
        $text .= "</table>";
        $text .= "<p class=\"submit\"><input type=\"submit\" name=\"Submit\" value=\"Save Changes\" />";
        $text .= "</p></form></div></div>";
        $text .= "</div></div></div>";
        print($text);
    }


}

?>
