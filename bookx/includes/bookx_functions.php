<?php

/**
 * The functions for bookX, both admin and front-end
 *
 * @package WordPress
 * @author  Xnuiem
 */

class bookx_functions {
    var $basePath;
    var $options;
    var $wpdb;
    var $text;
    

    /**
    * The construct function for the bookX class.  It adds a path variable to class.
    *
    * @param NULL
    * @return NULL
    */

    function __construct(){
        global $wpdb;
        $this->wpdb = $wpdb;
        
    }
    
    /**
    * Currently not used.
    * 
    */

    function bookx_search(){
 
        $link = get_page_link($this->var->options["page_id"]);
        
        /*if ($_POST["order"]){ $order = $_POST["order"]; }
        else if ($_GET["order"]){ $order = $_GET["order"]; }
        else { $order = $this->var->options['list_order_default']; }
        
        if ($_POST["sort"]){ $sort = $_POST["sort"]; }
        else if ($_GET["sort"]){ $sort = $_GET["sort"]; }
        else { $sort = $this->var->options['list_sort_default']; }
        */
        $search = "%" . addslashes(strtolower($_POST["bookxsearch"])) . "%";
        
        $text = "<div id=\"bookx_content\">";
        
        /*if ($this->var->options["list_filter"] == 1){
            $text .= "<div id=\"bookx_filter\">";
            $text .= "<form id=\"bookx_filter\" action=\"$link\" method=\"post\">";
            $text .= "Field: <select name=\"order\">";
            foreach(array_keys($this->fieldArray) as $f){
                if ($f == $order){ $s = "selected"; }
                else { $s = ''; }
                $text .= "<option value=\"$f\" $s>" . $this->fieldArray[$f] . "</option>";
            }
            $text .= "</select>";
            
            $text .= "&nbsp;&nbsp;Order: <select name=\"sort\">";
            foreach(array_keys($this->sortArray) as $f){
                if ($f == $sort){ $s = "selected"; }
                else { $s = ''; }
                $text .= "<option value=\"$f\" $s>" . $this->sortArray[$f] . "</option>";
            }            
            $text .= "</select>";
            $text .= "<input type=\"submit\" value=\"Set\" />";
            $text .= "</form>";
            $text .= "</div>";
        }
        if ($_GET["limit"]){ $limit = $_GET["limit"]; }
        else { $limit = 0; }        
        $count = $this->wpdb->get_var("select count(bx_item_id) from " . $this->wpdb->prefix . "bx_item where lower(bx_item_name) like '$search' or lower(bx_item_author) like '$search' or lower(bx_item_publisher) like '$search'");  
        $setnum = $this->var->options["per_page"];   
        if ($this->var->options["per_page"] != 0 && $count > $setnum){
            $paging = true;
            
            require_once(BOOKX_DIR . 'suitex_list.php'); 
            $listObj = new suitex_list();
            $listObj->setNum = $setnum;
            $url = $link . "?&order=$order&sort=$sort";
            
            
            $pager = $listObj->paging($limit, $count, $url);
            $text .= "<div id=\"bookx_pager\">$pager</div>";
        }
        */
        
        
       
        $sql = "select bx_item_publisher, bx_item_price, bx_item_date, bx_item_summary, bx_item_comments, bx_item_link, ";
        $sql .= "bx_item_id, bx_item_name, bx_item_isbn, bx_item_format, bx_item_pages, bx_item_author, bx_item_image";
        $sql .= " from " . $this->wpdb->prefix . "bx_item where lower(bx_item_name) like '$search' or lower(bx_item_author) like '$search'";
        $sql .= " or lower(bx_item_publisher) like '$search'";
        $sql .= " order by bx_item_name";

        
        $results = $this->wpdb->get_results($sql);
        
        
        if (substr_count($this->var->options["listTemplate"], "::IMAGE::")){
            $doImage = true;            
        }
        
        foreach($results as $row){
            if ($doImage){
                $image = $row->bx_item_image;
            
                $sourceWidth = substr($image, strpos($image, "width=\"") + 7);
                $sourceWidth = substr($sourceWidth, 0, strpos($sourceWidth, "\""));

                $sourceHeight = substr($image, strpos($image, "height=\"") + 8);
                $sourceHeight = substr($sourceHeight, 0, strpos($sourceHeight, "\""));            
            
                if ($sourceWidth > $this->var->options["list_image_width"]) {
                    $newWidth  = $this->var->options["list_image_width"];
                    $newHeight = (INTEGER) ($sourceHeight * ($this->var->options["list_image_width"] / $sourceWidth));
                } 
                else if ($sourceHeight > $this->var->options["list_image_height"]) {
                    $newWidth  = (INTEGER) ($sourceWidth * ($this->var->options["list_image_height"] / $sourceHeight));
                    $newHeight = $this->var->options["list_image_height"];
                } 
                else {
                    $newWidth  = $sourceWidth;
                    $newHeight = $sourceHeight;
                }
                $image = str_replace('width="' . $sourceWidth . '"', 'width="' . $newWidth . '"', $image);
                $image = str_replace('height="' .$sourceHeight . '"', 'height="' . $newHeight . '"', $image);
                if ($this->var->options["list_image_align"] != ''){
                    $image = str_replace("src", "align=\"" . $this->var->options['list_image_align'] . "\" src", $image);       
                }
                
            }
            
            
            
            $trans["::ELINK::"]     = $row->bx_item_link;
            $trans["::TITLE::"]     = $row->bx_item_name;
            $trans["::AUTHOR::"]    = $row->bx_item_author;            
            $trans["::ISBN::"]      = $row->bx_item_isbn;             
            $trans["::PUBLISHER::"] = $row->bx_item_publisher;             
            $trans["::DATE::"]      = $row->bx_item_date;             
            $trans["::PAGES::"]     = $row->bx_item_pages;             
            $trans["::FORMAT::"]    = $row->bx_item_format;             
            $trans["::LINK::"]      = $link . "?&book_id=" . $row->bx_item_id;            
            $trans["::IMAGE::"]     = $image;            
            $trans["::PRICE::"]     = $row->bx_item_price;            
            $trans["::SUMMARY::"]   = substr(strip_tags($row->bx_item_summary), 0, $this->var->options["list_characters"]) . "...";            
            $trans["::COMMENTS::"]  = substr(strip_tags($row->bx_item_comments), 0, $this->var->options["list_characters"]) . "...";
            $trans["::MORE::"]      = " <a href=\"" . $link . "?&book_id=" . $row->bx_item_id . "\">More</a>";
            
             
            $text .= "<div class=\"bookx_list_entry\">" . strtr(stripslashes($this->var->options["listTemplate"]), $trans) . "</div>";
            
            
  
            
        }
        if ($pager){ $text .= "<div id=\"bookx_pager\">$pager</div>"; }
        $text .= "</div>"; 
        $this->text = $text;
        
    }
    
    /**
    * Shows a single book
    * 
    */

    
    function bookx_showItem(){
        
        $link = get_page_link($this->var->options["page_id"]);
        $text = "<div id=\"bookx_content\">";
       
        $sql = "select bx_item_publisher, bx_item_price, bx_item_date, bx_item_summary, bx_item_comments, bx_item_link, ";
        $sql .= "bx_item_id, bx_item_name, bx_item_isbn, bx_item_format, bx_item_pages, bx_item_author, bx_item_image";
        $sql .= " from " . $this->wpdb->prefix . "bx_item where bx_item_id = %d limit 1";

        $row = $this->wpdb->get_row($this->wpdb->prepare($sql, $_GET["book_id"]));
        
        
        if (substr_count($this->var->options["detailTemplate"], "::IMAGE::")){
            $doImage = true;            
        }
        
        $image = $row->bx_item_image;
        if ($doImage && $image != ''){
            
            
            $sourceWidth = substr($image, strpos($image, "width=\"") + 7);
            $sourceWidth = substr($sourceWidth, 0, strpos($sourceWidth, "\""));

            $sourceHeight = substr($image, strpos($image, "height=\"") + 8);
            $sourceHeight = substr($sourceHeight, 0, strpos($sourceHeight, "\""));            
            
            if ($sourceWidth > $this->var->options["detail_image_width"]) {
                $newWidth  = $this->var->options["detail_image_width"];
                $newHeight = (INTEGER) ($sourceHeight * ($this->var->options["detail_image_width"] / $sourceWidth));
            } 
            else if ($sourceHeight > $this->var->options["detail_image_height"]) {
                $newWidth  = (INTEGER) ($sourceWidth * ($this->var->options["detail_image_height"] / $sourceHeight));
                $newHeight = $this->var->options["detail_image_height"];
            } 
            else {
                $newWidth  = $sourceWidth;
                $newHeight = $sourceHeight;
            }
            $image = str_replace('width="' . $sourceWidth . '"', 'width="' . $newWidth . '"', $image);
            $image = str_replace('height="' .$sourceHeight . '"', 'height="' . $newHeight . '"', $image);
            if ($this->var->options["detail_image_align"] != ''){
                $image = str_replace("src", "align=\"" . $this->var->options['list_image_align'] . "\" src", $image);       
            }
            $trans["::IMAGE::"]     = $image;     
        }
        else {
            $trans["::IMAGE::"] = '';
        }

        $trans["::ELINK::"]     = $row->bx_item_link;
        $trans["::TITLE::"]     = $row->bx_item_name;
        $trans["::AUTHOR::"]    = $row->bx_item_author;            
        $trans["::ISBN::"]      = $row->bx_item_isbn;             
        $trans["::PUBLISHER::"] = $row->bx_item_publisher;             
        $trans["::DATE::"]      = date(get_option("date_format"), $row->bx_item_date);             
        $trans["::PAGES::"]     = $row->bx_item_pages;             
        $trans["::FORMAT::"]    = $row->bx_item_format;             
        $trans["::PRICE::"]     = $row->bx_item_price;            
        $trans["::SUMMARY::"]   = $row->bx_item_summary;            
        $trans["::COMMENTS::"]  = $row->bx_item_comments;
             
        $text .= "<div class=\"bookx_detail_entry\">" . strtr(stripslashes($this->var->options["detailTemplate"]), $trans) . "</div>";        
        
        $text .= "</div>"; 
        $this->text = $text;        
        
    }
    
    /**
    * Shows a list of books
    * 
    */

    function bookx_listItems(){
    
        
        $link = get_page_link($this->var->options["page_id"]);
        if ($_POST["order"]){ $order = $_POST["order"]; }
        else if ($_GET["order"]){ $order = $_GET["order"]; }
        else { $order = $this->var->options['list_order_default']; }
        
        if ($_POST["sort"]){ $sort = $_POST["sort"]; }
        else if ($_GET["sort"]){ $sort = $_GET["sort"]; }
        else { $sort = $this->var->options['list_sort_default']; }
        
        $text = "<div id=\"bookx_content\">";
        
        if ($this->var->options["list_filter"] == 1){
            $text .= "<div id=\"bookx_filter\">";
            $text .= "<form id=\"bookx_filter\" action=\"$link\" method=\"post\">";
            $text .= "Field: <select name=\"order\">";
            foreach(array_keys($this->var->fieldArray) as $f){
                if ($f == $order){ $s = "selected"; }
                else { $s = ''; }
                $text .= "<option value=\"$f\" $s>" . $this->var->fieldArray[$f] . "</option>";
            }
            $text .= "</select>";
            
            $text .= "&nbsp;&nbsp;Order: <select name=\"sort\">";
            foreach(array_keys($this->var->sortArray) as $f){
                if ($f == $sort){ $s = "selected"; }
                else { $s = ''; }
                $text .= "<option value=\"$f\" $s>" . $this->var->sortArray[$f] . "</option>";
            }            
            $text .= "</select>";
            $text .= "<input type=\"submit\" value=\"Set\" />";
            $text .= "</form>";
            $text .= "</div>";
        }
        if ($_GET["limit"]){ $limit = $_GET["limit"]; }
        else { $limit = 0; }        
        $count = $this->wpdb->get_var("select count(bx_item_id) from " . $this->wpdb->prefix . "bx_item");  
        $setnum = $this->var->options["per_page"];   
        if ($this->var->options["per_page"] != 0 && $count > $setnum){
            $paging = true;
            
            require_once(BOOKX_DIR . 'suitex/suitex_list.php'); 
            $listObj = new suitex_list();
            $listObj->setNum = $setnum;
            $url = $link . "?&order=$order&sort=$sort";
            
            
            $pager = $listObj->createPaging($limit, $count, $url);
            $text .= "<div id=\"bookx_pager\">$pager</div>";
        }
        
        
        
       
        $sql = "select bx_item_publisher, bx_item_price, bx_item_date, bx_item_summary, bx_item_comments, ";
        $sql .= "bx_item_id, bx_item_name, bx_item_isbn, bx_item_format, bx_item_pages, bx_item_author, bx_item_image";
        $sql .= " from " . $this->wpdb->prefix . "bx_item ";
        $sql .= "order by $order $sort";
        $sql .= " limit $limit, $setnum";
        
        
        $results = $this->wpdb->get_results($sql);
        
        
        if (substr_count($this->var->options["listTemplate"], "::IMAGE::")){
            $doImage = true;            
        }
        
        foreach($results as $row){
            $image = $row->bx_item_image;
            if ($doImage && $image != ''){
                               
                
            
                $sourceWidth = substr($image, strpos($image, "width=\"") + 7);
                $sourceWidth = substr($sourceWidth, 0, strpos($sourceWidth, "\""));
                
                

                $sourceHeight = substr($image, strpos($image, "height=\"") + 8);
                $sourceHeight = substr($sourceHeight, 0, strpos($sourceHeight, "\""));            
            
                if ($sourceWidth > $this->var->options["list_image_width"]) {
                    $newWidth  = $this->var->options["list_image_width"];
                    $newHeight = (INTEGER) ($sourceHeight * ($this->var->options["list_image_width"] / $sourceWidth));
                } 
                else if ($sourceHeight > $this->var->options["list_image_height"]) {
                    $newWidth  = (INTEGER) ($sourceWidth * ($this->var->options["list_image_height"] / $sourceHeight));
                    $newHeight = $this->var->options["list_image_height"];
                } 
                else {
                    $newWidth  = $sourceWidth;
                    $newHeight = $sourceHeight;
                }
                $image = str_replace('width="' . $sourceWidth . '"', 'width="' . $newWidth . '"', $image);
                $image = str_replace('height="' .$sourceHeight . '"', 'height="' . $newHeight . '"', $image);
                if ($this->var->options["list_image_align"] != ''){
                    $image = str_replace("src", "align=\"" . $this->var->options['list_image_align'] . "\" src", $image);       
                }
                
            }
            
            
            
            $trans["::ELINK::"]     = $row->bx_item_link;
            $trans["::TITLE::"]     = $row->bx_item_name;
            $trans["::AUTHOR::"]    = $row->bx_item_author;            
            $trans["::ISBN::"]      = $row->bx_item_isbn;             
            $trans["::PUBLISHER::"] = $row->bx_item_publisher;             
            $trans["::DATE::"]      = $row->bx_item_date;             
            $trans["::PAGES::"]     = $row->bx_item_pages;             
            $trans["::FORMAT::"]    = $row->bx_item_format;             
            $trans["::LINK::"]      = $link . "?&book_id=" . $row->bx_item_id;            
            $trans["::IMAGE::"]     = $image;            
            $trans["::PRICE::"]     = $row->bx_item_price;            
            $trans["::SUMMARY::"]   = substr(strip_tags($row->bx_item_summary), 0, $this->var->options["list_characters"]) . "...";            
            $trans["::COMMENTS::"]  = substr(strip_tags($row->bx_item_comments), 0, $this->var->options["list_characters"]) . "...";
            $trans["::MORE::"]      = " <a href=\"" . $link . "?&book_id=" . $row->bx_item_id . "\">More</a>";
            
             
            $text .= "<div class=\"bookx_list_entry\">" . strtr(stripslashes($this->var->options["listTemplate"]), $trans) . "</div>";
            
            
  
            
        }
        if ($pager){ $text .= "<div id=\"bookx_pager\">$pager</div>"; }
        $text .= "</div>"; 
        $this->text = $text;
    }
    
    /** 
    * Starts the execution of the class
    * 
    */
   
    function bookx_init(){
        global $post;
        if ($post->ID == $this->var->options["page_id"]){
            if ($_GET["book_id"]){
                $this->bookx_showItem();
            }
            else if ($_POST["bookxsearch"]){
                $this->bookx_search();
            }
            else {
                $this->bookx_listItems();
            }
            if ($this->text){
                add_filter('the_content', array($this, 'bookx_stroke'));   
            }            
        }
        else if ($_GET['regenbookx'] == 1){
            $this->bookx_regenPage();
        }
    }
    
    /**
    * returns the page after prepending the CSS
    * 
    */
    
    function bookx_stroke(){
        $this->text = "<style type=\"text/css\">" . $this->var->options["css"] . "</style>"  . $this->text;
        return $this->text;
    }
    
    function bookx_regenPage(){
        if (!get_post($this->var->options['page_id'])){
            $page                   = array();
            $page['post_type']      = 'page';                                       
            $page['post_title']     = 'Recommended Books';
            $page['post_name']      = 'booklist';
            $page['post_status']    = 'publish';
            $page['comment_status'] = 'closed';
            $page['post_content']   = 'This page displays your BookX front end.';
            $this->var->options['page_id'] = wp_insert_post($page);
            update_option('bookx_options', $this->var->options);
        }
    }
}

?>
