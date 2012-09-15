<?php

class themex_functions {
	/**
	* The front-end methods for themeX.
 	* @package  WordPress
    * @author   Xnuiem
    * @param    bool    $debug  Set to true to print debugging statements.  Remove for version 1.1--REVIEW.
 	*/          

    var $debug = false;
    
	function checkTheme(){
        global $wpdb;
		/**
	 	* Checks to see if the theme/time are in sync, and changes the theme if not.
	 	*/

		$time = date("H", time());
		$current = get_current_theme();
		$themes  = get_themes();
		$options = get_option("themex_options");

		$offset = 0;
        $current = $themes[$current]["Template"];
        
        if ($this->debug == true){ print($current); }

		if (count($options) > 3){
			//Ok, module is active, let's see what to do.
			if ($options["type"] == "simple"){
				if ($options["timeZone"]){
	       	 	$server = date("O");
        			if ($server != $options["timeZone"]){
						if ($server < 0){
							$offset = ($server - $options["timeZone"])/100;
							if ($options["timeZone"] > 0){
								$offset = $offset * -1;
							}
							$time = $time - $offset;
						}
						else {
							$offset = ($server - $options["timeZone"])/100;
							if ($options["timeZone"] > 0){
	                        	$offset = abs($offset);
							}
							$time = $time + $offset;
						}
        			}
				}
                
				if (($time) >= $options['dayStart']){
					//Time is past or equal to day start and current theme is night.
					if ($this->debug == true){ print("Inside 1"); }
					$newTheme = 'dayTheme';
				}

				if (($time) < $options['dayStart']){
					//Time is prior to day start and current theme is day
					if ($this->debug == true){ print("Inside 2"); }
                    $newTheme = 'nightTheme';
				}

            	if (($time) >= $options['nightStart'] && $options['dayStart'] < $options['nightStart']){
	            	//Time is after or equal to night start and current theme is day and day start is less than night start
            		if ($this->debug == true){ print("Inside 3"); }
                    $newTheme = 'nightTheme';
            	}
                
                if ($newTheme && $current != $options[$newTheme]){
                    if ($this->debug == true){ print("CHANGED to $newTheme"); }
                    switch_theme($options[$newTheme], $options[$newTheme]);
                    $current = $options[$newTheme];
                }
            }
            else if ($options["type"] == "date"){
            	for($m=1;$m<13;$m++){
     			   	$fieldName = "theme" . $m;
     		   		$monthName = "theme" . $m . "-month";
     			   	$dayName   = "theme" . $m . "-day";
        			$yearName  = "theme" . $m . "-year";
        			$timeName  = "theme" . $m . "-time";

            	    $compareDate = $options[$yearName] . $options[$monthName] . $options[$dayName] . $options[$timeName];

            	    if ($options[$dayName] == ''){ break; }

            	    if ($compareDate <= date('YndH') && $compareDate > $checkDate){
                   		$checkDate  = $compareDate;
                   		$checkTheme = $options[$fieldName];
            	    }
            	    else if ($compareDate > date('YndH')){ break; }

            	}
            	if ($current != $checkTheme){ switch_theme($checkTheme, $checkTheme); }
            }
		}

	}


}
?>
