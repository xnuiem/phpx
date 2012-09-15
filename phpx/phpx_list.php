<?php

class phpx_list {
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
    var $searchLabel = "Search";
    var $linkID      = true;
    var $sortable    = false;
    var $dateFilter  = false;
    

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
    * @param    string      $col        field name
    * @param    string      $name       Label
    * @param    array       $options    option list
    * @param    mixed       $current    current value
    */

 	function addFilter($col, $name, $options, $current=''){
 		$text = "<select name=\"$col\">";
        $text .= "<option value=\"\">$name</option>";
        if (is_array($options)){
        	foreach(array_keys($options) as $o){
                if ($current == $o){ $s = "selected"; }
        		else { $s = ''; }
                if ($_GET[$col] == ''){ $s = ''; }
        		$text .= "<option value=\"$o\" $s >" . $options[$o] . "</option>";
			}
        }
 		$text .= "</select>";
 		$this->filters[] = $text;

 	}
    
    function addDateFilter(){
        $text .= "Start Date: <input type=\"text\" name=\"start\" id=\"startDate\" value=\"" . $_GET["start"] . "\" />&nbsp;&nbsp;";
        $text .= "End Date <input type=\"text\" name=\"end\" id=\"endDate\" value=\"" . $_GET["end"] . "\" />";
        $text .= "<script language=\"javascript\">
            \$(function() {
                \$( \"#startDate\" ).datepicker();
                \$( \"#endDate\" ).datepicker();
            });
        </script>";
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
                if ($_GET["order"] == ''){ $s = ''; }
 				$text .= "<option value=\"$h\" $s>" . $headers[$h] . "</option>";
 			}
 		}
 		$text .= "</select>";
 		$this->filters[] = $text;
 	}
    
    /**
    * Actually starts the list
    * 
    * @param    array   $headers    column headers
    * @param    string   $link       the URL used to get back to this page (for filters/search/sort)
    * @param    string   $order      current order column
    * @param    string   $sort       current sort (asc/desc)
    * @param    array   $rows       array of the values
    * @param    int   $limit      what number we are starting on
    * @param    int  $number     how many there are total
    * @param    mixed   $hidden
    */


 	function startList($headers, $link, $order, $sort, $rows, $limit, $number, $hidden=''){
        
        if ($this->search || $this->filters || $this->orderForm || $this->dateFilter){

            $text .= "<form id=\"posts-filter\" action=\"" . $_SERVER["PHP_SELF"] . "\" method=\"get\">";
        }
        
		
        if (is_array($hidden)){
			foreach(array_keys($hidden) as $hide){
				$h .= "<input type=\"hidden\" name=\"$hide\" value=\"" . $hidden[$hide] . "\" />\r\n";
			}
		}
        if ($this->actionButtons){
            $text .= "<p class=\"list-buttons\">";
            $text .= $this->actionButtons;
            $text .= "</p>";
        }
            
        

		if ($this->search){
            //if ($this->searchLink){ $searchAction = $this->searchLink; }
			//else { $searchAction = $link; }

			//$text .= "<form class=\"search-form\" action=\"$link\" method=\"get\">";
			$text .= "<p class=\"search-box\">";
			$text .= "<label class=\"hidden\" for=\"\">" . $this->searchLabel . ":</label>";
			$text .= $h;
			$text .= "<input type=\"text\" class=\"search-input\" id=\"project-search-input\" name=\"s\" value=\"" .  $_GET["s"] . "\" />";
			$text .= "<input type=\"submit\" value=\"" . $this->searchLabel . "\" class=\"button\" />";
			$text .= "</p>";
			$text .= "<br class=\"clear\" />";
		}
        if ($this->filters || $this->orderForm || $this->dateFilter || $this->paging){
            $text .= "<div class=\"tablenav\">";
        


		
			if ($this->orderForm){ $this->createOrderForm($headers, $order); }
            if ($this->dateFilter){ $this->addDateFilter(); }
			
			

			$text .= "<div class=\"alignleft actions\">";
            foreach($this->filters as $f){
                $text .= $f . "&nbsp;";
            }
			$text .= $h;
			$text .= "<input type=\"submit\" id=\"post-query-submit\" value=\"Filter\" class=\"button-secondary\" />";
			$text .= "</div>";
			
        
        
            if ($this->paging){
                $pager = "<div class=\"tablenav-pages\">" . $this->paging($limit, $number, $_SERVER["PHP_SELF"] . "?" . $_SERVER["QUERY_STRING"]) . "</div><br class=\"clear\" />"; 
                $text .= $pager;
            }
            $text .= "</div><div class=\"clear\"></div>";
        }
      
        
        $text .= "<input type=\"hidden\" id=\"_wpnonce\" name=\"_wpnonce\" value=\"" . wp_create_nonce() . "\" />";
        $text .= "<input type=\"hidden\" name=\"_wp_http_referer\" value=\"" . $_SERVER["PHP_SELF"] . "\" />";
        $text .= "<table class=\"widefat fixed\" cellspacing=\"0\">\r\n";
        
        if (is_array($headers)){
		    foreach(array_keys($headers) as $h){
			    $cols .= "<th scope=\"col\" id=\"$h\" class=\"manage-column column-" . $h;
			    if ($h == "cb"){ $cols .= " check-column"; $cb=true; }
			    $cols .= "\" style=\"\">";
                if ($this->sortable == true){
                    if ($_GET["sort"] == "asc" || !$_GET["sort"]){ $linkSort = "desc"; }
                    else { $linkSort = "asc"; }
                    $cols .= "<a href=\"" . $_SERVER["REQUEST_URI"] . "&sort=$linkSort&order=" . $h . "\">";
                }
                $cols .= $headers[$h];
                if ($this->sortable == true){ 
                    if ($order == $h){
                        if ($sort == "desc"){ $cols .= " &darr; "; }
                        else { $cols .= " &uarr; "; }
                    }
                    $cols .= "</a>"; 
                }
                $cols .= "</th>";
		    }

		    $text .= "<thead>";
            $text .= "<tr>$cols</tr></thead>";
            $text .= "<tfoot>";
            $text .= "<tr>$cols</tr></tfoot>";
		    $text .= "<tbody>";
        }

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
            		if ($j == 1 && $this->linkID == true && $id != '' && $link != ''){
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
        if ($this->paging){
             $text .= "<div class=\"tablenav\">$pager</div>";
        }
        
        if ($this->search || $this->filters || $this->orderForm){
            $text .= "</form>";    
        }
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

        //ore = "Page: ";

        $show = $limit + $this->setNum;
        if ($show > $number){ $show = $number; }
        if (!$number){ $limit1 = 0; $more = ''; }
        elseif (!$limit){ $limit1 = 1; }
        else { $limit1 = $limit; }

        $showing = "<span class=\"displaying-num\">Displaying $limit1 - $show of $number</span>";
        if ($limit){
            $new = $limit - $this->setNum;
            if ($new < 0){ $new = 0; }
            
            $startPages .= "<a class=\"prev page-numbers\" href=\"" . $url . "&limit=$new\"><<</a>";
            
            
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
            if ($save < 2){
                $st = 0;
                $en = 5;
            }
            else {
                $st = $save - 2;
                $en = $save + 4;
            }
            if ($limit == $x){
                $currentPage = $a;
                $currPages .= "<span class=\"page-numbers current\">$a</span>";
                $stopFlag=1;
            }
            else if ($x > ($number - $this->setNum) && $stopFlag == 0){

                $currentPage = $a;
                $currPages .= "<span class=\"page-numbers current\">$a</span>";
            }
            else if ($a > $st && $a < $en){
                
                $currPages.= " <a class=\"page-numbers\" href=\"" . $url . "&limit=$x\">$a</a> ";
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
            
            $endPages = "<a class=\"next page-numbers\" href=\"" . $url . "&limit=$new\">>></a>";
            //$more .= "<a class=\"links\" href=\"" . $url . "&limit=$end\">>></a>";
        }
        
        if ($currentPage > 3){ 
            $startPages .= "<a class=\"page-numbers\" href=\"" . $url . "&limit=0\">1</a>";
            if ($currentPage != 4){
                $startPages .= "<span class=\"page-numbers dots\"> ... </span>";
            }
        }
        
        if ($currentPage < ($pages - 2)){
            
            if (($pages - $currentPage) == 3){ $endPages = "<a class=\"page-numbers\" href=\"" . $url . "&limit=$end\">$pages</a>" . $endPages; }
            else { $endPages = "<span class=\"page-numbers dots\"> ... </span><a class=\"page-numbers\" href=\"" . $url . "&limit=$end\">$pages</a>" . $endPages; }
        }

        $more = $showing . " " . $startPages . $currPages . $endPages;


        return $more;
        //REIVEIEW

    }    
}
?>
