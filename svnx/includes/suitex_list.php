<?php

class suitex_list {
	/**
	* 
    * @package  WordPress
    * @author   Xnuiem
    * @abstract A basic framework to return list results within a wordpress structure.
    * 
    * @param    array   $filters    The array of fields to filter against
    * @param    bool    $search     If a search form should be included
    * @param    bool    $orderForm  If a order(sort) form should be included
    * @param    string  $pluginPath The path to the plugin directory
    * @param    object  $wpdb       The WPDB object from Wordpress
    * @param    bool    $paging     If the list should use multiple pages
    * $param    array   $omit       The list of fields to omit from the actual list, hidden fields
    * 
 	*/

 	var $filters    = array();
 	var $search     = false;
 	var $orderForm  = false;
    var $pluginPath = '';
    var $wpdb;
    var $paging     = false;  
    var $omit       = array();

    /**
    * Just creates a global variable
    * 
    */
    
 	function __construct(){
 		global $wpdb;
 		$this->wpdb = $wpdb;
 	}
    
    /**
    * Adds a filter to the list.
    * 
    * @param    mixed   $col
    * @param    mixed   $name
    * @param    mixed   $options
    * @param    mixed   $current
    */

 	function addFilter($col, $name, $options, $current=''){
 		$text = "<select name=\"$col\">";
        $text .= "<option value=\"\">$name</option>";
        if (is_array($options)){
        	foreach(array_keys($options) as $o){
        		if ($current == $o){ $s = "selected"; }
        		else { $s = ''; }
        		$text .= "<option value=\"$o\">" . $options[$o] . "</option>";
			}
        }
 		$text .= "</select>";
 		$this->filters[] = $text;

 	}
    
    /**
    * Adds a sort form to the list
    * 
    * @param    mixed   $headers
    * @param    mixed   $current
    */

 	function createOrderForm($headers, $current=''){
 		$text = "<select name=\"order\">";
 		$text .= "<option value=\"\">Order by</option>";
 		foreach(array_keys($headers) as $h){
 			if (!in_array($h, $this->omit)){
 				if ($h == $current){ $s = "selected"; }
 				else { $s = ''; }
 				$text .= "<option value=\"$h\">" . $headers[$h] . "</option>";
 			}
 		}
 		$text .= "</select>";
 		$this->filters[] = $text;
 	}
    
    /**
    * Actuall starts the list
    * 
    * @param    array   $headers
    * @param    mixed   $link
    * @param    mixed   $order
    * @param    mixed   $sort
    * @param    mixed   $rows
    * @param    mixed   $limit
    * @param    mixed   $number
    * @param    mixed   $hidden
    */


 	function startList($headers, $link, $order, $sort, $rows, $limit, $number, $hidden=''){

		if (is_array($hidden)){
			foreach(array_keys($hidden) as $hide){
				$h .= "<input type=\"hidden\" name=\"$hide\" value=\"" . $hidden[$hide] . "\" />\r\n";
			}
		}

		if ($this->search){
			//if ($this->searchLink){ $searchAction = $this->searchLink; }
			//else { $searchAction = $link; }

			$text .= "<form class=\"search-form\" action=\"$link\" method=\"get\">";
			$text .= "<p class=\"search-box\">";
			$text .= "<label class=\"hidden\" for=\"\">" . $this->searchLabel . ":</label>";
			$text .= $h;
			$text .= "<input type=\"text\" class=\"search-input\" id=\"project-search-input\" name=\"s\" value=\"" .  $_GET["s"] . "\" />";
			$text .= "<input type=\"submit\" value=\"" . $this->searchLabel . "\" class=\"button\" />";
			$text .= "</p>";
			$text .= "</form>";
			$text .= "<br class=\"clear\" />";
		}

		if ($this->filters || $this->orderForm){
			if ($this->orderForm){ $this->createOrderForm($headers, $order); }
			$text .= "<form id=\"posts-filter\" action=\"$link\" method=\"get\">";
			$text .= "<div class=\"tablenav\">";

			$text .= "<div class=\"alignleft actions\">";
            foreach($this->filters as $f){
            	$text .= $f . "&nbsp;";
            }
			$text .= $h;
			$text .= "<input type=\"submit\" id=\"post-query-submit\" value=\"Filter\" class=\"button-secondary\" />";
			$text .= "</div><br class=\"clear\" /></div>";
			$text .= "<div class=\"clear\"></div>";


        }

        $text .= "<input type=\"hidden\" id=\"_wpnonce\" name=\"_wpnonce\" value=\"" . wp_create_nonce() . "\" />";
        $text .= "<input type=\"hidden\" name=\"_wp_http_referer\" value=\"" . $_SERVER["PHP_SELF"] . "\" />";
        $text .= "<table class=\"widefat fixed\" cellspacing=\"0\">\r\n";

		foreach(array_keys($headers) as $h){
			$cols .= "<th scope=\"col\" id=\"$h\" class=\"manage-column column-" . $h;
			if ($h == "cb"){ $cols .= " check-column"; $cb=true; }
			$cols .= "\" style=\"\">" . $headers[$h] . "</th>";
		}

		$text .= "<thead>";
        if ($this->paging){ 
            $pager = "<tr>" . $this->paging($limit, $number, $url) . "</tr>"; 
            $text .= $pager;
        }
        $text .= "<tr>$cols</tr></thead>";
        $text .= "<tfoot>";
        if ($this->paging){
            $text .= $pager;
        }
        $text .= "<tr>$cols</tr></tfoot>";
		$text .= "<tbody>";

        if (!is_array($rows)){
        	$rows = array();
        }
        $x=1;

        $plus = get_option('siteurl') . $this->pluginPath . "/plus.gif"; //REVIEW

        foreach(array_keys($rows) as $id){
        	if ($x/2){ $class = "class=\"alternate\"";  }

        	$text .= "<tr id=\"link-$id\" valign=\"middle\" $class>\r\n";
        	if ($cb){
        		$text .= "<th scope=\"row\" class=\"check-column\">";
        		$text .= "<input type=\"checkbox\" name=\"linkcheck[]\" value=\"$id\" />";
        		$text .= "</th>\r\n";
        	}
        	//else { die("NO CHECK BOX"); }
        	$j=1;
        	foreach($rows[$id] as $r){
            	$rowspan = '';
            	if ($j == 1 && $this->fold == true){ $rowspan = "rowspan=\"2\""; $fold=1; }
            	if (!is_array($r)){
            		$text .= "<td $rowspan>";
            		if ($j == 1){
            			$text .= "<strong><a href=\"$link" . "$id\">";
            			$text .= $r . "</a></strong>";
            		}
        	    	else { $text .= $r; }

    	        	$text .= "</td>\r\n";
	            	$j++;
            	}
        	}
        	$text .= "</tr>\r\n";
            $x++;
        }
        $text .= "</tbody></table>\r\n";
        $this->text = $text;
 	}
    
    /**
    * Creates the paging
    * 
    * @param    mixed   $limit
    * @param    mixed   $number
    * @param    mixed   $url
    */

    function paging($limit, $number, $url){

        $more = "Page: ";

        $show = $limit + $this->setNum;
        if ($show > $number){ $show = $number; }
        if (!$number){ $limit1 = 0; $more = ''; }
        elseif (!$limit){ $limit1 = 1; }
        else { $limit1 = $limit; }

        $showing = "Showing $limit1 - $show ($number Total)";
        if ($limit){
            $new = $limit - $this->setNum;
            if ($new < 0){ $new = 0; }
            $more .= "<a class=\"links\" href=\"" . $url . "&limit=0\"><<</a> ";
            $more .= "<a class=\"links\" href=\"" . $url . "&limit=$new\">Previous Page</a>";
        }
        $y=0;
        for($x=0; $x < $number; $x += $this->setNum){
            if ($x == $limit){
                $save = $y;
                break;
            }
            $y++;
        }

        $a=1;
        $stopFlag=0;
        for($x=0; $x < $number; $x += $this->setNum){
            if ($save < 5){
                $st = 0;
                $en = 10;
            }
            else {
                $st = $save - 4;
                $en = $save + 6;
            }
            if ($limit == $x){
                $more .= " $a ";
                $stopFlag=1;
            }
            else if ($x > ($number - $this->setNum) && $stopFlag == 0){
                $more .= " $a";
            }
            else if ($a > $st && $a < $en){
                $more .= " <a class=\"links\" href=\"" . $url . "&limit=$x\">$a</a> ";
            }
            $a++;
        }
        if ($number > ($limit + $this->setNum)){
            $new = $limit + $this->setNum;
            $check = $number/$this->setNum;
            if (substr_count($check, ".") > 0){
                $pages = substr($check, 0, strpos($check, ".")) + 1;
            }
            $end = $pages*$this->setNum - $this->setNum;
            $more .= "<a class=\"links\" href=\"" . $url . "&limit=$new\">Next Page</a>";
            $more .= "<a class=\"links\" href=\"" . $url . "&limit=$end\">>></a>";
        }

        $more = $showing . " " . $more;


        return $more;


    }    
}
?>
