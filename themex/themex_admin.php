<?php

class themex_admin {

    function themex_install(){
	    /**
	    * Installs the plugin by creating the page and options
	    */

		if (!get_option('themex_options')){
        	update_option('themex_options', array());
        }

    }

    function themex_uninstall(){
    	/**
    	* Uninstalls the plugin by deleting the options and page
    	*/

    	delete_option('themex_options');
    }

    function themex_admin_menu(){
    	/**
    	* The hook for the admin menu
    	*/
        add_management_page('ThemeX', 'ThemeX', 5, __FILE__, array($this, 'themex_admin_page'));
    }

    function formLists(){
    	/**
    	* Produces Lists for the usage of the forms
    	* @return	array	$this->timeZoneArray	List of time zones in array format.
    	* @return 	array	$this->monthArray		List of months in array format.
    	*/

    	$tz = array();
		$tz["GMT"] = "000";
		$tz["UTC"] = "000";
		$tz["ECT"] = "+100";
		$tz["EET"] = "+200";
		$tz["ART"] = "+200";
		$tz["EAT"] = "+300";
		$tz["MET"] = "+330";
		$tz["NET"] = "+400";
		$tz["PLT"] = "+500";
		$tz["IST"] = "+530";
		$tz["BST"] = "+600";
		$tz["VST"] = "+700";
		$tz["CTT"] = "+800";
		$tz["JST"] = "+900";
		$tz["ACT"] = "+930";
		$tz["AET"] = "+1000";
		$tz["SST"] = "+1100";
		$tz["NST"] = "+1200";
		$tz["MIT"] = "-1100";
		$tz["HST"] = "-1000";
		$tz["AST"] = "-900";
		$tz["PST"] = "-800";
		$tz["PNT"] = "-700";
		$tz["MST"] = "-700";
		$tz["CST"] = "-600";
		$tz["EST"] = "-500";
		$tz["IET"] = "-500";
		$tz["PRT"] = "-400";
		$tz["CNT"] = "-330";
		$tz["AGT"] = "-300";
		$tz["BET"] = "-300";
		$tz["CAT"] = "-100";

		$this->timeZoneArray = $tz;

		$months = array();
		$months["1"] = "Jan";
		$months["2"] = "Feb";
		$months["3"] = "Mar";
		$months["4"] = "Apr";
		$months["5"] = "May";
		$months["6"] = "Jun";
		$months["7"] = "Jul";
		$months["8"] = "Aug";
		$months["9"] = "Sep";
		$months["10"] = "Oct";
		$months["11"] = "Nov";
		$months["12"] = "Dec";
		$this->months = $months;

    }

    function themex_admin_page(){
    	/**
    	* Creates the Admin page
    	*
    	*/

		global $pluginBase;

        clearstatcache();
        $this->formLists();
        $options = get_option('themex_options');
		if ($_POST['action'] == "update"){
        	if ($_POST['type'] == "simple"){
            	$options["type"]       = "simple";
                $options["dayTheme"]   = $_POST["dayTheme"];
                $options["dayStart"]   = $_POST["dayStart"];
                $options["nightTheme"] = $_POST["nightTheme"];
                $options["nightStart"] = $_POST["nightStart"];

				$message = 'Options Updated.  Simple Rotation Active.';
			}
			else if ($_POST["type"] == "time"){
                $options["timeZone"] = $this->timeZoneArray[$_POST["timezone"]];
                $options["timeZoneAbbr"] = $_POST["timezone"];
                $message = 'Time Zone Updated';
			}
			else if ($_POST["type"] == "date"){
  				$options["type"] = "date";
  				for($m=1;$m<13;$m++){
      		  		$fieldName = "theme" . $m;
	      		  	$monthName = "theme" . $m . "-month";
    	    		$dayName   = "theme" . $m . "-day";
     		   		$yearName  = "theme" . $m . "-year";
     		   		$timeName  = "theme" . $m . "-time";

     		   		if ($_POST[$dayName] != '' && $_POST[$yearName] != ''){
	     		   		$options[$fieldName] = $_POST[$fieldName];
     		   			$options[$monthName] = $_POST[$monthName];
     		   			$options[$dayName]   = $_POST[$dayName];
     		   			$options[$yearName]  = $_POST[$yearName];
     		   			$options[$timeName]  = $_POST[$timeName];
     		   		}
     		   		else {
	     		   		$options[$fieldName] = '';
     		   			$options[$monthName] = '';
     		   			$options[$dayName]   = '';
     		   			$options[$yearName]  = '';
     		   			$options[$timeName]  = '';
     		   		}
     		   	}
			}

			update_option('themex_options', $options);

		}

        $themes = get_themes();
		$tArray = array();
		foreach($themes as $t){
			$tArray[$t["Template"]] = $t["Name"];
		}

		$text .= "<style>";
		$text .= "input.themex-day { width: 25px; } ";
		$text .= "input.themex-year { width: 40px; } ";
		$text .= "</style>";


        $text .= "<div class=\"wrap\">";


        $text .= "<h2>ThemeX</h2>";
        $text .= "ThemeX automatically alternates themes based on the time of day or date.";
        $text .= "<br />Select your options below and press 'Save Changes'.  Simple Rotation and Date ";
        $text .= "Based are mutually exclusive, meaning they cannot be used at the same time, it is ";
        $text .= "either one or the other.";

        if ($message){ $text .= "<br /><b><span style=\"color:#FF0000;\">$message</span></b>"; }
		$text .= "<div id=\"poststuff\" class=\"metabox-holder\">";
		$text .= "<div id=\"post-body\" class=\"has-sidebar\">";
		$text .= "<div id=\"post-body-content\" class=\"has-sidebar-content\">";


        $text .= "<div class=\"postbox\">";
        $text .= "<h3><label>Time Zone</label></h3>";
		$text .= "<div class=\"inside\">";
        $text .= "<form method=\"post\" action=\"\">";
        $text .= "<input type=\"hidden\" name=\"action\" value=\"update\" />";
        $text .= "<input type=\"hidden\" name=\"type\" value=\"time\" />";

		$offset = date('O');

        $text .= "<table class=\"form-table\">";
        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th>";
        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"time_zone\">Time Zone:</label></th>";
        $text .= "<td>";

        $text .= "<select name=\"timezone\">";

        $used = false;

        foreach(array_keys($this->timeZoneArray) as $t){
        	if ($options["timeZoneAbbr"] == $t){ $s = "selected"; $used = true; }
        	else if ($this->timeZoneArray[$t] == $offset && !$used){ $s = "selected"; }
        	else { $s = ''; }
        	$text .= "<option value=\"$t\" $s>" . $this->timeZoneArray[$t] . " $t</option>";
        }



        $text .= "</select>";
        $text .= "&nbsp; &nbsp; Your server time zone is: $offset " . date('T');

        $text .= "</td></tr>";
        $text .= "</table>";
        $text .= "<p class=\"submit\"><input type=\"submit\" name=\"Submit\" value=\"Save Changes\" />";
        $text .= "</p></form>";
		$text .= "</div></div>";

		$text .= "<div class=\"postbox\">";
        $text .= "<h3><label>Simple Rotation</label></h3>";
		$text .= "<div class=\"inside\">";
        $text .= "<form method=\"post\" action=\"\">";
        $text .= "<input type=\"hidden\" name=\"action\" value=\"update\" />";
        $text .= "<input type=\"hidden\" name=\"type\" value=\"simple\" />";

        $text .= "<table class=\"form-table\">";
        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"day_theme\">Day Theme:</label></th>";
        $text .= "<td><select name=\"dayTheme\">";
        foreach(array_keys($tArray) as $t){
        	if ($t == $options["dayTheme"]){ $s = "selected"; }
        	else { $s = '';  }
        	$text .= "<option value=\"$t\" $s>" . $tArray[$t] . "</option>";
        }
        $text .= "</select> starts at ";
        $text .= "<select name=\"dayStart\">";
        for($i=0;$i<24;$i++){
        	if (strlen($i) == 1){ $v = "0" . $i; }
        	else { $v = $i; }
        	if ($v == $options["dayStart"]){ $s = "selected"; }
        	else { $s = ''; }
        	$text .= "<option value=\"$v\" $s>$v</option>";
        }
        $text .= "</select> hours";

        $text .= "</td></th></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"reset_cache\">Night Theme:</label></th>";
        $text .= "<td><select name=\"nightTheme\">";
        foreach(array_keys($tArray) as $t){
        	if ($t == $options["nightTheme"]){ $s = "selected"; }
        	else { $s = '';  }
        	$text .= "<option value=\"$t\" $s>" . $tArray[$t] . "</option>";
        }
        $text .= "</select> starts at ";
        $text .= "<select name=\"nightStart\">";
        for($i=0;$i<24;$i++){
        	if (strlen($i) == 1){ $v = "0" . $i; }
        	else { $v = $i; }
        	if ($v == $options["nightStart"]){ $s = "selected"; }
        	else { $s = ''; }
        	$text .= "<option value=\"$v\" $s>$v</option>";
        }
        $text .= "</select> hours";

        $text .= "</td></th></tr>";
        $text .= "</table>";
        $text .= "<p class=\"submit\"><input type=\"submit\" name=\"Submit\" value=\"Save Changes\" />";
        $text .= "</p></form>";
        $text .= "</div></div>";

        $text .= "<div class=\"postbox\">";
        $text .= "<h3><label>Date Based Rotation</label></h3>";
		$text .= "<div class=\"inside\">";
		$text .= "These fields will be evaluated in the order they are entered here. ";
		$text .= "The one with the latest date that has passed will be the active theme.";
		$text .= "  Any entries after a blank line or a date that has not yet passed will be ignored.";
		$text .= "<br /><br /><b>For Example:</b> <br />";
		$text .= "If Theme 1 starts on Jan 06, Theme 2 on March 15, and Theme 3 on Feburary 26, ";
		$text .= " and the current date is June 14, Theme 2 will be active.  1 could be active, but 2 is newer, 3 is newer than 1";
		$text .= " but not 2, so it is skipped.";
		$text .= "<br /><br /><b>Another Example:</b> <br />";
		$text .= "If Theme 1 starts on Jan 06, Theme 2 on July 15, and Theme 3 on Feburary 26, ";
		$text .= " and the current date is June 14, Theme 1 will be active.  1 is active because 2 has not yet passed.";
		$text .= " 3 is not active because although the date is in the past, 2 has not yet passed.";

		//$text .= "  This is done in order to attempt to keep the front-end processing as quick as possible.";

		$text .= "<br /><br />";
		$text .= "<script type='text/javascript' src='../wp-content/plugins/themex/javascript/themex.js'></script>";

        $text .= "<form method=\"post\" action=\"\" name=\"dateForm\">";
        $text .= "<input type=\"hidden\" name=\"action\" value=\"update\" />";
        $text .= "<input type=\"hidden\" name=\"type\" value=\"date\" />";
		$text .= "<table class=\"form-table\">";
		for($m=1;$m<13;$m++){
        	$fieldName = "theme" . $m;
        	$monthName = "theme" . $m . "-month";
        	$dayName   = "theme" . $m . "-day";
        	$yearName  = "theme" . $m . "-year";
        	$timeName  = "theme" . $m . "-time";
        	$text .= "<tr class=\"form-field\">";
       	 	$text .= "<th scope=\"row\" valign=\"top\"><label for=\"$fieldName\">Theme $m:</label></th>";
        	$text .= "<td><select name=\"$fieldName\" id=\"$fieldName\">";

	        foreach(array_keys($tArray) as $t){
        		if ($t == $options[$fieldName]){ $s = "selected"; }
        		else { $s = '';  }
        		$text .= "<option value=\"$t\" $s>" . $tArray[$t] . "</option>";
	        }
        	$text .= "</select> starts on ";
        	$text .= "<select name=\"$monthName\" id=\"$monthName\">";
        	foreach(array_keys($this->months) as $month){
        		if ($month == $options[$monthName]){ $s = "selected"; }
        		else { $s = ''; }

        		$text .= "<option value=\"$month\" $s>" . $this->months[$month] . "</option>";
        	}
        	$text .= "</select>";
        	$text .= "<input id=\"$dayName\" class=\"themex-day\" type=\"text\" maxlength=\"2\" name=\"$dayName\" value=\"" . $options[$dayName] . "\">,";
        	$text .= " <input id=\"$yearName\" class=\"themex-year\"type=\"text\" maxlength=\"4\" name=\"$yearName\" value=\"" . $options[$yearName] . "\">";

        	$text .= " at <select name=\"$timeName\" id=\"$timeName\">";
        	for($i=0;$i<24;$i++){
        		if (strlen($i) == 1){ $v = "0" . $i; }
        		else { $v = $i; }
        		if ($v == $options[$timeName]){ $s = "selected"; }
        		else { $s = ''; }
    	    	$text .= "<option value=\"$v\" $s>$v</option>";
            }
        	$text .= "</select>";

        	if ($m != 1){
        		$text .= "<a href=\"javascript:themexMove('$m', 'up');\">";
        		$text .= "<img src=\"../wp-content/plugins/themex/images/arrow_up_blue.png\" border=\"0\"></a> ";
			}
			if ($m != 12){
        		$text .= "<a href=\"javascript:themexMove('$m', 'down');\">";
        		$text .= "<img src=\"../wp-content/plugins/themex/images/arrow_down_blue.png\" border=\"0\"></a> ";
			}
        	$text .= "</td></tr>";
		}
		$text .= "</table>";
		$text .= "<p class=\"submit\"><input type=\"submit\" name=\"Submit\" value=\"Save Changes\" />";
        $text .= "</p></form>";

 		$text .= "</div></div>";
        $text .= "</div></div>";
        print($text);



    }
}

?>
