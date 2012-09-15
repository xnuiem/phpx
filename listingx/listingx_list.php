<?php

class listingx_list {
	/**
	* Global Functions
 	* @package WordPress
 	*/

 	var $filters  = array();
 	var $search   = false;
 	var $orderForm = false;

 	function __construct(){
 		global $wpdb;

 		$this->wpdb = $wpdb;
 		$this->options = get_option('listingx_options');

 	}

 	function listHeaders($headerArray, $link, $order, $sort){

 	}

 	function listRows(){

 	}

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


 	function startList($headers, $link, $order, $sort, $rows, $hidden=''){

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

		$text .= "<thead><tr>$cols</tr></thead><tfoot><tr>$cols</tr></tfoot>";
		$text .= "<tbody>";

        if (!is_array($rows)){
        	$rows = array();
        }
        $x=1;

        $plus = get_option('siteurl') . "/wp-content/plugins/listingx/plus.gif";

        if ($this->fold == true){
        	$subLink = $this->wpdb->get_var("select guid from " . $this->wpdb->prefix . "posts where ID = '" . $this->options["download_page_id"] . "' limit 1");
        }

        foreach(array_keys($rows) as $id){
        	if ($x/2){ $class = "class=\"alternate\""; }
        	else { $x++; }

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
        	if ($fold == 1){
        		$text .= "<tr>\r\n";
        		$text .= "<td colspan=\"5\">";
        		$text .= "<a href=\"javascript:openSub('release-$id', 'image-$id', '" . get_option('siteurl') . "/wp-content/plugins/listingx'); \">";
        		$text .= "<img src=\"$plus\" border=\"0\" width=\"10\" height=\"10\" id=\"image-$id\" /></a>";
        		$text .= "<div id=\"release-$id\" style=\"display:none;\">";
        		$text .= "<table width=\"100%\">";
        		$text .= "<tr><th>Filename</th><th>Size</th><th>Type</th><th>Downloads</th></tr>";

        		foreach($rows[$id]["sub"] as $sub){
	        		$text .= "<tr><td>";
	        		$text .= "<a href=\"$subLink" . "&file=" . $sub["id"] . "\">" . $sub["name"] . "</a></td>\r\n";
        			$text .= "<td>" . $this->fileSize($sub["size"]) . "</td>\r\n";
        			$text .= "<td>" . $sub["type"] . "</td>\r\n";
        			$text .= "<td>" . $sub["download"] . "</td>\r\n";
        			$text .= "</tr>\r\n";
        		}
        		$text .= "</table></div>";

        		$fold = 0;
        	}
        }
        $text .= "</tbody></table>\r\n";
        $this->text = $text;
 	}

 	function endList($listName){

 	}

    function fileSize($size){
    	if ($size < 1000){ return $size; }
    	else if ($size < 1000000){
    		$size = number_format($size/1000, 2) . "K";
    		return $size;
    	}
    	else {
    		$size = number_format($size/1000000, 2) . "M";
    		return $size;
    	}
    }
}
?>
