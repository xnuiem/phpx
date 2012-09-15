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
    var $paging     = false;  
    var $omit       = array();
    var $setNum     = '50';
    var $link       = true;
    var $setExport  = false;
    var $fold       = false;

    /**
    * Adds a filter to the list.
    * 
    * @param    mixed   $col
    * @param    mixed   $name
    * @param    mixed   $options
    * @param    mixed   $current
    */

     function addFilter($col, $name, $options, $current=''){
        $text = "<select name=\"$col\" id=\"$col\">";
        $text .= "<option value=\"\">$name</option>";
        if (is_array($options)){
            foreach(array_keys($options) as $o){
                if ($current == $o){ $s = "selected"; }
                else { $s = ''; }
                $text .= "<option value=\"$o\" $s>" . $options[$o] . "</option>";
            }
        }
         $text .= "</select>";
         $this->filters[] = $text;

     }
     
     /**
     * Creates a Date Form using JS calendar functions
     * 
     * @param array $current current Date
     * @param boolean $useTime if the date should also show the time selection
     */
    
    function dateForm($current=array(), $useTime = false){
        if (count($current) == 1){ $startOnly = true; }
        if ($useTime == true){
            if ($current[0] != ''){ $current[0] .= " 00:00"; }
            else { $current[0] = date("m/d/Y") . " 00:00"; }
            if ($current[1] != ''){ $current[1] .= " 23:59"; }
            else { $current[1] = date("m/d/Y") . " 23:59"; }
        }
                
        $text .= "<style type=\"text/css\">@import url(templates/calendar-blue.css);</style>";
        $text .= "<script type=\"text/javascript\" src=\"javascript/calendar.js\"></script>";
        $text .= "<script type=\"text/javascript\" src=\"javascript/calendar-en.js\"></script>";
        $text .= "<script type=\"text/javascript\" src=\"javascript/calendar-setup.js\"></script>\r\n";        
        if ($startOnly){
            $text .= "Date: ";
            $text .= "<input type='text' name='dateStart' id='dateStart' value='" . $current[0] . "' /> ";
            $text .= "<img src='images/cal.gif' id='dateStartImage' style=\"cursor: pointer;\" title=\"Date selector\" /> ";
            $text .= "<script type=\"text/javascript\">";
            if ($useTime == true){
                $text .= "Calendar.setup({ inputField : \"dateStart\", ifFormat : \"%m/%d/%Y %H:%M\", button : \"dateStartImage\", singleClick : true, weekNumbers : false, showOthers : true, step : 1, showsTime : true});";
            }
            else {
                $text .= "Calendar.setup({ inputField : \"dateStart\", ifFormat : \"%m/%d/%Y\", button : \"dateStartImage\", singleClick : true, weekNumbers : false, showOthers : true, step : 1});";
            }
            $text .= "</script>";             
        }
        else {
            $text .= "Date from: ";
            $text .= "<input type='text' name='dateStart' id='dateStart' value='" . $current[0] . "' /> ";
            $text .= "<img src='images/cal.gif' id='dateStartImage' style=\"cursor: pointer;\" title=\"Date selector\" />";
            $text .= " to "; 
            $text .= "<input type='text' name='dateEnd' id='dateEnd' value='" . $current[1] . "' /> ";

            $text .= "<img src='images/cal.gif' id='dateEndImage' style=\"cursor: pointer;\" title=\"Date selector\" /> ";                
            $text .= "<script type=\"text/javascript\">";
            if ($useTime == true){
            
                $text .= "Calendar.setup({ inputField : \"dateStart\", ifFormat : \"%m/%d/%Y %H:%M\", button : \"dateStartImage\", singleClick : true, weekNumbers : false, showOthers : true, step : 1, showsTime : true});";
                $text .= "Calendar.setup({ inputField : \"dateEnd\", ifFormat : \"%m/%d/%Y %H:%M\", button : \"dateEndImage\", singleClick : true, weekNumbers : false, showOthers : true, step : 1, showsTime : true});";
            }
            else {
                $text .= "Calendar.setup({ inputField : \"dateStart\", ifFormat : \"%m/%d/%Y\", button : \"dateStartImage\", singleClick : true, weekNumbers : false, showOthers : true, step : 1});";
                $text .= "Calendar.setup({ inputField : \"dateEnd\", ifFormat : \"%m/%d/%Y\", button : \"dateEndImage\", singleClick : true, weekNumbers : false, showOthers : true, step : 1});";
            }
            $text .= "</script>";             
        }
        $this->dateFormText = $text;
        
        
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
     * Exports the list results to file.  Right now only to a CSV
     * 
     * @param mixed $type type of file
     * @param array $headers array of column headers
     * @param array $rows array of the data rows
     * @param mixed $fileName export file name
     */

    function export($type, $headers, $rows, $fileName){
        
        
        
        if ($type == "csv"){
            $header1 = "Content-Type: text/csv;";
            $header2 = "Content-Disposition: attachment; filename=$fileName" . ".csv";
            $delimit = ",";    
        }
        
        if (is_array($headers) && count($headers) > 0){
            foreach($headers as $h){
                $text .= strtoupper($h) . $delimit;
            }
            $text = substr($text, 0, -(strlen($delimit))) . "\r\n";
        }
        
        foreach($rows as $r){
            $row = '';
            foreach($r as $item){ 
                $row .= $item . $delimit;
            }
            $row = substr($row, 0, -(strlen($delimit))) . "\r\n";
            $text .= $row;
        }
        header($header1);
        header($header2);
        print($text);
        exit();
        
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
            $text .= "<form name=\"filter\" id=\"filter\" method=\"get\">";
            $text .= "<div class=\"tablenav\">";

            $text .= "<div class=\"alignleft actions\">";
            foreach($this->filters as $f){
                $text .= $f . "&nbsp;";
            }
            
            $text .= $h;
            
            if ($this->dateFormText){
                $text .= $this->dateFormText;
            }
            
            $text .= "<input type=\"button\" class=\"button\" value=\"Filter\" onClick=\"filterForm('" . $this->url . "');\" />";
            
            if ($this->setExport != false){
                $text .= " <input type=\"button\" class=\"button\" value=\"Export\" onClick=\"exportForm('" . $this->url . "&export=" . $this->setExport . "');\" />";    
            }
            
            $text .= "</div><br class=\"clear\" /></div></form>";
            $text .= "<div class=\"clear\"></div>";
        }

        //$text .= "<input type=\"hidden\" id=\"_wpnonce\" name=\"_wpnonce\" value=\"" . wp_create_nonce() . "\" />";
        //$text .= "<input type=\"hidden\" name=\"_wp_http_referer\" value=\"" . $_SERVER["PHP_SELF"] . "\" />";
        if ($this->form){
            $text .= "<form method=\"" . $this->form["method"] . "\" action=\"" . $this->form["action"] . "\" id=\"" . $this->form["name"] . "\" name=\"" . $this->form["name"] . "\" >";
        }
        
        $text .= "<table class=\"widefat fixed\" cellspacing=\"0\">\r\n";
        $x=1;
        $this->colCount = 0;
        foreach(array_keys($headers) as $h){
            $hide = '';
            if ($this->hide){
                if (in_array($x, array_keys($this->hide))){
                    $hide = "style=\"color: #0066CC;\" onClick=\"toggleHideCol($x);\"";  
                    $expandClass = " expandHeaders";  
                    $currentHide = $x;
                }
                else if (is_array($this->hide[$currentHide])){
                    if (in_array($x, $this->hide[$currentHide])){
                        $expandClass = '';  
                        $hide = "style=\"display:none;\"";
                    }
                }
            }
            
             
            $cols .= "<th scope=\"col\" id=\"header-$x\" class=\"manage-column column-" . $h . $expandClass;
            if ($h == "cb"){ $cols .= " check-column"; $cb=true; }
            $cols .= "\" $hide>" . $headers[$h] . "</th>";
            $this->colCount++;
            $x++;
        }

        $text .= "<thead>";
        if ($this->paging){ 
            $pager = "<tr>" . $this->paging($limit, $number, $this->url) . "</tr>"; 
            $text .= $pager;
        }
         
        $text .= "<tr>$cols</tr></thead>";
        $text .= "<tfoot>";
        $text .= "<tr>" . str_replace("header", "footer", $cols) . "</tr>";
        if ($this->paging){
            $text .= $pager;
        }        
        $text .= "</tfoot>";
        $text .= "<tbody>";

        if (!is_array($rows)){
            $rows = array();
        }
        $x=1;

        //$plus = get_option('siteurl') . $this->pluginPath . "/plus.gif"; //REVIEW
        
        foreach(array_keys($rows) as $id){
            if ($x/2){ $class = "class=\"alternate\"";  }
            $x++; 

            $text .= "<tr id=\"link-$id\" valign=\"middle\" $class>\r\n";
            if ($cb){
                $text .= "<td scope=\"row\" class=\"check-column\">";
                $text .= "<input type=\"checkbox\" name=\"linkcheck[]\" value=\"$id\" />";
                $text .= "</td>\r\n";
            }
            //else { die("NO CHECK BOX"); }
            $j=1;
            $currentHide = ''; 
            foreach($rows[$id] as $r){
                $elemId = '';
                $rowspan = '';
                if ($j == 1 && $this->fold == true){ 
                    $subRowCount = count($rows[$id]["sub"]) + 1;
                    //$rowspan = "rowspan=\"" . $subRowCount . "\""; 
                    $fold=1; 
                }
                if (!is_array($r)){
                    $tdClass='';
                    if ($this->styles){
                        foreach(array_keys($this->styles) as $style){
                            if (in_array($j, $this->styles[$style])){
                                $tdClass = "class=\"$style\"";
                            }
                        }
                    }
                    
                    $hide = '';
                    if ($this->hide){
                        if (in_array($j, array_keys($this->hide))){
                            $currentHide = $j;
                        }
                        else if (is_array($this->hide[$currentHide])){
                            
                            if (in_array($j, $this->hide[$currentHide])){
                                $hide = "style=\"display:none;\"";
                                $elemId = "id=\"" . $x . "_" . "$j\"";
                            }
                        }
                    }                    
                    
                    
                    if ($j == 1){ $text .= "<td id=\"parentElement_" . $id . "\" $tdClass>";  }
                    else { $text .= "<td $tdClass $hide $elemId>"; }
                    
                    if ($j == 1 && $this->link == true){
                        if (substr_count($link, "::ID::")){ $send = str_replace("::ID::", $id, $link); }
                        else { $send = $link . $id; }
                        $text .= "<strong><a href=\"$send\">";
                        $text .= $r . "</a></strong>";
                    }
                    else if ($j == 1){
                        if ($fold == 1){
                            $text .= "<span id=\"addNotes\" onClick=\"openSubTable('$id', '$subRowCount', '" . $this->foldName . "');\">";
                            $text .= "<img src=\"images/plus.gif\" width=\"10\" height=\"10\" border=\"0\" alt=\"Open Sub Container\" id=\"image_$id\" /> ";
                            $text .= $r;
                            $text .= "</a>";
                        }
                        else {
                            $text .= "<strong>$r</strong>";
                        }
                    }
                    else { $text .= $r; }

                    $text .= "</td>\r\n";
                    $j++;
                    
                }
            }
            $text .= "</tr>\r\n";
            if ($fold == 1){
                $f=0;
                foreach($rows[$id]["sub"] as $sub){
                    $currentHide = '';
                    $k=1;
                    
                    $text .= "<tr style=\"display: none;\" id=\"" . $this->foldName . "_$id" . "_$f\">";
                    if ($this->foldName == "subber"){ $text .= "<td>&nbsp;</td>"; }
                    foreach($sub as $s){
                        $tdClass='';  
                        if ($this->styles){
                            foreach(array_keys($this->styles) as $style){
                                $l = $k + 1;
                                if (in_array($l, $this->styles[$style])){
                                    $tdClass = "class=\"$style\"";
                                }
                            }
                        } 
                        $hide = '';
                        $elemId = '';
                        if ($this->hide){
                            if (in_array($l, array_keys($this->hide))){
                                $currentHide = $l;
                            }
                            else if (is_array($this->hide[$currentHide])){
                            
                                if (in_array($l, $this->hide[$currentHide])){
                                    $hide = "style=\"display:none;\"";
                                    $elemId = "id=\"$x" . "_" . $f . "_" . $l . "\"";
                                }
                            }
                        }                                               
                        $text .= "<td $tdClass $hide $elemId>";
                        if ($k == 1){ $text .= "<strong>$s</strong>"; }
                        //else if ($k == 2){ $text .= "<strong>$s</strong>"; }
                        else { $text .= $s; }
                        $text .= "</td>";
                        $k++;
                    }
                    $text .= "</tr>\r\n";
                    $f++;
                    if ($f > $maxSub || !$maxSub){ $maxSub = $f; }
                } 
           
                $fold = 0;
            } 
            
        }            
        
        
        $text .= "</tbody></table>\r\n";
        if ($this->form){ 
            $text .= "</form>"; 
        }
        $text .= "<script language=\"javascript\">";
        $text .= "var maxParentRows = '$x'; var maxSubRows = '$maxSub';  ";
        
        if ($this->hide){
            $js = '';
            foreach(array_keys($this->hide) as $h){
                $js .= "var header_" . $h . " = '";
                foreach($this->hide[$h] as $s){
                    $js .= $s . ",";
                }
                $js = substr($js, 0, -1) . "';";
            }
            $text .= $js;
        }
        
        
        
        
        $text .= "</script>";
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
            $more .= "<a class=\"links\" href=\"" . $url . "&limit=0\"><<</a>&nbsp;";
            $more .= "&nbsp;<a class=\"links\" href=\"" . $url . "&limit=$new\">Previous Page</a>";
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
                $more .= "&nbsp;<a class=\"links\" href=\"" . $url . "&limit=$x\">$a</a>&nbsp;";
            }
            $a++;
        }
        
        if ($number > ($limit + $this->setNum)){
            $new = $limit + $this->setNum;
            $check = $number/$this->setNum;
            if (substr_count($check, ".") > 0){
                $pages = substr($check, 0, strpos($check, ".")) + 1;
            }
            $end = $pages * $this->setNum - $this->setNum;
            $more .= "&nbsp;<a class=\"links\" href=\"" . $url . "&limit=$new\">Next Page</a>";
            $more .= "&nbsp;<a class=\"links\" href=\"" . $url . "&limit=$end\">>></a>";
        }

        $more = "<td colspan=\"" . $this->colCount . "\">" . $showing . " " . $more . "</td>";


        return $more;


    }    
}
?>
