<?php

/**
* The class that creates and admins the bookx widget
* 
* @global   array   $options
* @global   object  $wpdb
* @global   array   $fieldArray
* @global   array   $sortArray
*/
class bookx_widget {
    
    var $options    = array();
    var $wpdb;
    var $fieldArray = array();
    var $sortArray  = array();
    
    /**
    * The construct function, just sets a variable
    * 
    */
    
    function __construct(){
        global $wpdb;
        $this->wpdb = $wpdb;
    }
    
    /**
    * Adds the widget to WP using the hooks.
    * 
    */
    
    function bookx_widget_init(){
        if (!function_exists("register_sidebar_widget")){ return; }
    
        register_sidebar_widget('BookX', array($this, 'bookx_widget_sidebar'));
        register_sidebar_widget('BookX Search', array($this, 'bookx_search_widget_sidebar'));
        register_widget_control('BookX', array($this, 'bookx_widget_admin')); 
        register_widget_control('BookX Search', array($this, 'bookx_search_widget_admin'));       
        
    }
    
    function bookx_search_widget_sidebar($args){
        $link = get_page_link($this->var->options["page_id"]);
        extract($args);
        $text = $before_widget . $before_title;
        $text .= $this->var->options["search_widget_title"] . $after_title . "<ul>";  
        $text .= "<li>";
        $text .= "<form method=\"post\" name=\"bookxSearch\" action=\"$link\">";
        $text .= "<input id=\"bookxSearchField\" type=\"text\" name=\"bookxsearch\" value=\"" . $_POST["bookxsearch"] . "\" />";
        $text .= "<input type=\"submit\" value=\"Search\" id=\"bookxSearchButton\" />";    
        $text .= "</form>";    
            
        $text .= "</li>";          
        $text .= "</ul>" . $after_widget; 
        print($text);              
    }
    
    function bookx_search_widget_admin(){
        if ($_POST["bookx_search_submit"]){
            $this->var->options["search_widget_title"] = $_POST["search_widget_title"];
            update_option('bookx_options', $this->var->options);  
        }
        
        if (!$this->var->options["search_widget_title"]){
            $this->var->options["search_widget_title"] = "BookX Search";
        }
        
        $text  = "<strong>Title:</strong> <input type=\"text\" name=\"search_widget_title\" value=\"" . $this->var->options["search_widget_title"] . "\" /><br />";                                     
        $text .= "<input type=\"hidden\" name=\"bookx_search_submit\" value=\"1\" />";
        print($text);
        
    }
    
    /**
    * The sidebar widget creation function
    * 
    * @param    array   $args
    * @return   string  $bookx_widget
    */
    
    function bookx_widget_sidebar($args){
        extract($args);
        $text = $before_widget . $before_title;
        $text .= $this->var->options["widget_title"] . $after_title . "<ul>";
       
        $sql = "select bx_item_publisher, bx_item_price, bx_item_date, bx_item_link, ";
        $sql .= "bx_item_id, bx_item_name, bx_item_isbn, bx_item_format, bx_item_pages, bx_item_author, bx_item_image";
        $sql .= " from " . $this->wpdb->prefix . "bx_item where bx_item_sidebar = '1' order by " . $this->var->options["widget_order"];
        $sql .= " " . $this->var->options["widget_sort"];
        
        
        
        $results = $this->wpdb->get_results($sql);
        
        if (substr_count($this->var->options["widgetTemplate"], "::IMAGE::")){
            $doImage = true;            
        }
        
        
        foreach($results as $row){
            if ($doImage){
                $image = $row->bx_item_image;
            
                $sourceWidth = substr($image, strpos($image, "width=\"") + 7);
                $sourceWidth = substr($sourceWidth, 0, strpos($sourceWidth, "\""));

                $sourceHeight = substr($image, strpos($image, "height=\"") + 8);
                $sourceHeight = substr($sourceHeight, 0, strpos($sourceHeight, "\""));            
            
                if ($sourceWidth > $this->var->options["widget_image_width"]) {
                    $newWidth  = $this->var->options["widget_image_width"];
                    $newHeight = (INTEGER) ($sourceHeight * ($this->var->options["widget_image_width"] / $sourceWidth));
                } 
                else if ($sourceHeight > $this->var->options["widget_image_height"]) {
                    $newWidth  = (INTEGER) ($sourceWidth * ($this->var->options["widget_image_height"] / $sourceHeight));
                    $newHeight = $this->var->options["widget_image_height"];
                } 
                else {
                    $newWidth  = $sourceWidth;
                    $newHeight = $sourceHeight;
                }
                $image = str_replace('width="' . $sourceWidth . '"', 'width="' . $newWidth . '"', $image);
                $image = str_replace('height="' .$sourceHeight . '"', 'height="' . $newHeight . '"', $image);
            }
            $link = get_page_link($this->var->options["page_id"]);
            
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

            
            $text .= "<li>";
            $text .= strtr(stripslashes($this->var->options["widgetTemplate"]), $trans);
            
            
            $text .= "</li>";          
            
        }

        $text .= "</ul>" . $after_widget; 
        print($text);
        
    }
    
    /**
    * Creates the admin form for the widget.
    * 
    */
      
    function bookx_widget_admin(){
        if ($_POST["bookx_submit"]){
            $this->var->options["widget_title"]          = $_POST["bookx_title"]; 
            $this->var->options["widget_image_height"]   = $_POST["bookx_height"];
            $this->var->options["widget_image_width"]    = $_POST["bookx_width"];
            $this->var->options["widget_sort"]           = $_POST["bookx_sort"];
            $this->var->options["widget_order"]          = $_POST["bookx_order"];
            update_option('bookx_options', $this->var->options);    
        }
        
        
        $text  = "<strong>Title:</strong> <input type=\"text\" name=\"bookx_title\" value=\"" . $this->var->options["widget_title"] . "\" /><br />";
        $text .= "<strong>Max Image Size:</strong> <input type=\"text\" name=\"bookx_height\" size=\"3\" value=\"" . $this->var->options["widget_image_height"] . "\" /> x ";
        $text .= "<input type=\"text\" name=\"bookx_width\" size=\"3\" value=\"" . $this->var->options["widget_image_width"] . "\" /><br />";
        $text .= "<strong>Order on:</strong> <select name=\"bookx_order\">";
        foreach(array_keys($this->var->fieldArray) as $f){
            if ($f == $this->var->options["widget_order"]){ $s = "selected"; }
            else { $s = ''; }
            $text .= "<option value=\"$f\" $s>" . $this->var->fieldArray[$f] . "</option>";
        }
        $text .= "</select><br />";
        $text .= "<strong>Direction:</strong> <select name=\"bookx_sort\">";
        foreach(array_keys($this->var->sortArray) as $sort){
            if ($sort == $this->var->options["widget_sort"]){ $s = "selected"; }
            else { $s = ''; }
            $text .= "<option value=\"$sort\" $s>" . $this->var->sortArray[$sort] . "</option>";
        }
        $text .= "</select><br />";
        $text .= "<input type=\"hidden\" name=\"bookx_submit\" value=\"1\" />";
        print($text);
          
    }
}
?>
