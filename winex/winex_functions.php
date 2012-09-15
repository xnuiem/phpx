<?php



class wineX {
	/**
	* The functions for WineX, both admin and front-end
	* @package WordPress
	*/

    function winex_fetchWineList($wArray){
		/**
    	* Fetches the wine list from CellarTracker and creates the cache file
    	*
    	* @param array $wArray winex options array
    	* @return string $text page content
    	*/

    	if ($wArray['user_id']){
        	$date = date('Ymd');
    		$url = 'http://www.cellartracker.com/list.asp?iUserOverride=' . $wArray['user_id'] . '&Page=0';

    		if (function_exists('curl_init')){
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url );
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $lines = curl_exec($ch);
                curl_close($ch);
            }
            else {
                $lines = file_get_contents($url);
            }

            if (substr_count($lines, "Access Denied") > 0){
                $text = "WineX needs you to set your CellarTracker \"Privacy Settings\" to \"Low\".";
            }
            else {


                $trans = get_html_translation_table(HTML_ENTITIES);
                $start = "<table width='100%' class='editList'>";
                $end = "<!-- END MAIN PAGE -->";
                $lines = substr($lines, strpos($lines, $start));
                $lines = substr($lines, 0, strpos($lines, $end));
                $trans["href='wine.asp"] = " target='_new' href='http://www.cellartracker.com/wine.asp";
                $trans["href='list.asp"] = " target='_new' href='http://www.cellartracker.com/list.asp";
                $trans['/images/'] = "http://www.cellartracker.com/images/";
                $trans['images/camera.gif'] = "http://www.cellartracker.com/images/camera.gif";
                $trans['lbl_disp.asp'] = "http://www.cellartracker.com/lbl_disp.asp";
                $trans['<i>'] = "<br><i>";
                $trans['</th>'] = "</th>\r\n";
                $trans['</tr>'] = "</tr>\r\n";
                $trans['</td>'] = "</td>\r\n";
                $trans["<span style='background:#FFFFCC'>"] = '';
                $trans['</span>'] = '';
                $trans["<table width='100%' class='editList'>"] = "<table class='wine-table'>";

                unset($trans['"'], $trans[">"], $trans["<"], $trans["&"]);

                $lines = strtr($lines,$trans);

                $headers = substr($lines, strpos($lines, "<tr class='titleRow'>"));
                $headers = substr($headers, 0, strpos($headers, "</tr>"));


                //print($headers);

                $headerArray = explode("<th", $headers);

    			$headerCols[] = '';

                for($i=0;$i<7;$i++){
                    $save = false;
                    $x = $headerArray[$i];
                    if ($i == 2){
                        $save = true;
                        $x = substr($x, 1);
                    }
                    else if ($i == 3 || $i == 4 || $i == 6){
                        $save = true;
                        $x = substr($x, 7);
                    }
                    else if ($i == 5){
                    	$save = true;
                    	$x = substr($x, 20);
                    }

                    if ($save){
        				$x = str_replace('</nobr>', '', $x);
                        $x = str_replace('</th>', '', $x);
                        $headerCols[] = $x;
                    }

                }

                $body = substr($lines, strpos($lines, "<tr class='properties'>") - 23);
                $body = substr($body, 0, strpos($body, "in stock</b>"));


                $rowArray = explode("<tr", $body);
                $rows = array();
                foreach($rowArray as $row){
                    $cell = array();
                    $cells = explode("<td", $row);
                    if (count($cells) == 7){

                        for($i=0;$i<7;$i++){
                            $save = false;
                            $x = $cells[$i];
                            if ($i == 1){
                                $save = true;
                                $x = substr($x, 27);
                            }
                            else if ($i == 2){
                                $save = true;
                                $x = substr($x, 8);
                            }
                            else if ($i == 3){
                                $save = true;
                                $x = substr($x, 20);
                            }
                            else if ($i == 4){
                                $save = true;
                                $x = substr($x, 4);
                            }
                            else if ($i == 5){
                                $save = true;
                                $x = substr($x, 14);
                            }
                            else if ($i == 6){
                                $save = true;
                                $x = substr($x, 1);
                            }

                            if ($save){
                				$x = str_replace('</nobr>', '', $x);
                                $x = str_replace('</th>', '', $x);
                                $cell[] = $x;
                            }
                        }
                        $rows[] = $cell;
                    }
                    else if (count($cells) == 3){
                        $footer = substr($cells[2], 14);
                    }
                }

                if (!$wArray['cols']){
                    $cols = array("0", "1", "2", "3", "4", "5");
                }
                else {
                    if (in_array('type', array_keys($wArray['cols']))){ $cols[] = "0"; }
                    if (in_array('size', array_keys($wArray['cols']))){ $cols[] = "1"; }
                    if (in_array('qty', array_keys($wArray['cols']))){ $cols[] = "2"; }
                    if (in_array('details', array_keys($wArray['cols']))){ $cols[] = "3"; }
                    if (in_array('window', array_keys($wArray['cols']))){ $cols[] = "4"; }
                    if (in_array('rating', array_keys($wArray['cols']))){ $cols[] = "5"; }
                }

                $number = count($cols);

                $text = '<div id="wine-cellar">';
                $text .= "<table>";
                $text .= "<tr>";
                foreach(array_keys($headerCols) as $h){
                    if (in_array($h, $cols)){
                        $text .= "<th>" . $headerCols[$h] . "</th>";
                    }
                }
                $text .= "</tr>";
                //print_r($rows);
                foreach($rows as $r){
                    $text .= "<tr>";
                    foreach(array_keys($r) as $c){
                        if (in_array($c, $cols)){
                            $text .= "<td>" . $r[$c] . "</td>";
                        }
                    }
                    $text .= "</tr>";
                }
                $text .= "<tr><td colspan=\"$number\">$footer</td></tr>";


                $text .= "</table></div>";

                $wArray['date'] = $date;
                $wArray['timestamp'] = time();
                update_option('winex_content', $text);
                update_option('winex_options', $wArray);

            }
        }
        else {
            $text = 'WineX needs your CellarTracker user_id before it can download your cellar listing';

        }
        return $text;

    }



    function winex_showWineList($content){
    	/**
    	* Checks against date to see if a new cache file is required, otherwise returns the contents of the current one.
    	* @param string $content page content, incoming
    	* @return string $content page content
    	*/

		global $id;

        $wArray = get_option('winex_options');
        if ($id == $wArray['page_id']){
            if ($wArray['date'] != date('Ymd')){
                $content = $wArray['css'] . $this->winex_fetchWineList($wArray);
            }
            else {
                $content = $wArray['css'] . get_option('winex_content');
            }
        }
        return $content;
    }

    function winex_install(){
	    /**
	    * Installs the plugin by creating the page and options
	    * @param NULL
	    * @return NULL
	    */

		if (!get_option('winex_options')){
        	$page                   = array();
        	$page['post_type']      = 'page';
        	$page['post_title']     = 'Wine Cellar';
        	$page['post_name']      = 'winecellar';
        	$page['post_status']    = 'publish';
        	$page['comment_status'] = 'closed';
        	$page['post_content']   = 'This page is used to display your CellarTracker wine cellar via WineX.<br /><br /><!--WINEX-->';

        	$page_id = wp_insert_post($page);
        	$wArray['page_id']   = $page_id;
        	$wArray['user_id']   = '';
        	$wArray['date']      = '';
        	$wArray['timestamp'] = '';
        	$wArray['cols']      = array();
        	$wArray['css']       = '';

        	update_option('winex_options', $wArray);
        	update_option('winex_content', array());
        }

    }



    function winex_uninstall(){
    	/**
    	* Uninstalls the plugin by deleting the options and page
    	*
    	* @param NULL
    	* @return NULL
    	*/

        $wArray = get_option('winex_options');

        global $wpdb;
        $sql = "delete from `" . $wpdb->prefix . "posts` where `ID` = '" . $wArray['page_id'] . "' limit 1";
		$wpdb->query($sql);
    	delete_option('winex_options');
    	delete_option('winex_content');

    }



    function winex_admin_menu(){
    	/**
    	* The hook for the admin menu
    	*
    	* @param NULL
    	* @return NULL
    	*/
        add_management_page('WineX', 'WineX', 5, __FILE__, array($this, 'winex_admin_page'));
    }

    function winex_admin_page(){
	    /**
	    * The administration page for updating options
	    *
	    * @param NULL
	    * @return NULL
	    */

        clearstatcache();

        $wArray = get_option('winex_options');
		if ($_POST['action'] == "update"){
            $user_id = trim(rtrim($_POST['user_id']));

            if (!is_numeric($user_id)){
                $message = "Invalid Member ID";
            }

            if ($_POST['css'] != ''){
            	$wArray['css'] = "<style>" . strip_tags($_POST['css']) . "</style>";
            }
            else { $wArray['css'] = ''; }


            if (!$message){
                if ($user_id != $wArray['user_id']){
                    $wArray['user_id'] = $user_id;
                    $wArray['date'] = 0;
                }

                if ($_POST['winex_date'] == 0){
                    $wArray['date'] = 0;
                }

                $cols = array();
                if ($_POST['type'] == 1){ $cols['type'] = 1; }
                if ($_POST['size'] == 1){ $cols['size'] = 1; }
                if ($_POST['qty'] == 1){ $cols['qty'] = 1; }
                if ($_POST['details'] == 1){ $cols['details'] = 1; }
                if ($_POST['window'] == 1){ $cols['window'] = 1; }
                if ($_POST['rating'] == 1){ $cols['rating'] = 1; }

                $wArray['cols'] = $cols;

                update_option('winex_options', $wArray);
				$message = 'Options Updated';
            }
		}


		if ($wArray["date"] == 0){ $lastUpdate = "No Results Cached"; }
		else { $lastUpdate = date("M-d-Y H:i", $wArray["timestamp"]); }
        $text .= "<div class=\"wrap\">";
        $text .= "<h2>WineX</h2>";
        $text .= "WineX enables you to import your <a href=\"http://www.cellartracker.com\" target=\"_new\">CellarTracker</a>";
        $text .= " into your WP installation.  <br /><br />WineX downloads your cellar";
        $text .= " once a day and saves it.  This keeps it running fast for both your users and your server.";
        //$text .= "  If you would like WineX to reset the cache contents, use the check box below.";

        if ($message){ $text .= "<br /><b><span style=\"color:#FF0000;\">$message</span></b>"; }
		$text .= "<div id=\"poststuff\" class=\"metabox-holder\">";
		$text .= "<div id=\"post-body\" class=\"has-sidebar\">";
		$text .= "<div id=\"post-body-content\" class=\"has-sidebar-content\">";

        $text .= "<div class=\"postbox\">";
        $text .= "<h3><label>WineX General Settings</label></h3>";
		$text .= "<div class=\"inside\">";

        $text .= "<form method=\"post\" action=\"\">";
        $text .= "<input type=\"hidden\" name=\"action\" value=\"update\" />";

        if ($message){ $text .= "<br /><b><span style=\"color:#FF0000;\">$message</span></b>"; }

        $text .= "<table class=\"form-table\">";
        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"member_number\">CellarTracker Member #:</label></th>";
        $text .= "<td><input type=\"text\" name=\"user_id\" value=\"" . $wArray['user_id'] . "\" style=\"width: 100px; \" />";
        $text .= "</td></th></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"reset_cache\">Reset Cache:</label></th>";
        $text .= "<td>";
        $text .= "<input type=\"checkbox\" name=\"winex_date\" value=\"0\" style=\"width: 15px; border: 0;\" /><br />";
        $text .= "(Last Updated: " . $lastUpdate . ")";
        $text .= "</td></th></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"cols\">Select Columns:</label></th>";
        $text .= "<td>";


        if ($wArray['cols']['type'] == 1){ $c = "checked"; }
        else { $c = ''; }

        $text .= "<input type=\"checkbox\" name=\"type\" value=\"1\" $c style=\"width: 15px; border: 0;\" /> Wine Type<br />";


        if ($wArray['cols']['size'] == 1){ $c = "checked"; }
        else { $c = ''; }

        $text .= "<input type=\"checkbox\" name=\"size\" value=\"1\" $c style=\"width: 15px; border: 0;\" /> Size<br />";


        if ($wArray['cols']['qty'] == 1){ $c = "checked"; }
        else { $c = ''; }

        $text .= "<input type=\"checkbox\" name=\"qty\" value=\"1\" $c style=\"width: 15px; border: 0;\" /> Quantity<br />";


        if ($wArray['cols']['details'] == 1){ $c = "checked"; }
        else { $c = ''; }

        $text .= "<input type=\"checkbox\" name=\"details\" value=\"1\" $c style=\"width: 15px; border: 0;\" /> Details<br />";


        if ($wArray['cols']['window'] == 1){ $c = "checked"; }
        else { $c = ''; }

        $text .= "<input type=\"checkbox\" name=\"window\" value=\"1\" $c style=\"width: 15px; border: 0;\" /> Drinking Window<br />";


        if ($wArray['cols']['rating'] == 1){ $c = "checked"; }
        else { $c = ''; }

        $text .= "<input type=\"checkbox\" name=\"rating\" value=\"1\" $c style=\"width: 15px; border: 0;\" /> Rating<br />";


        $text .= "</td></th></tr>";

        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"css\">Custom CSS:</label></th>";
        $text .= "<td><textarea name=\"css\" cols=\"40\" rows=\"10\">" . strip_tags($wArray['css']) . "</textarea>";
        $text .= "</td></th></tr>";

        $text .= "</table>";



        $text .= "<p class=\"submit\"><input type=\"submit\" name=\"Submit\" value=\"Save Changes\" />";
        $text .= "</p></form></div></div>";
        $text .= "</div></div></div>";
        print($text);
    }


}

?>
