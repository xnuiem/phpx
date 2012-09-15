<?php

/**
 * The functions for bookX, both admin and front-end
 *
 * @package WordPress
 * @author  Xnuiem
 */

class svnx_functions {
    var $basePath;
    var $options;
    var $wpdb;
    var $text;
    var $post;
    

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


    
    /**
    * Shows a single book
    * 
    */

    
 
    
    /** 
    * Starts the execution of the class
    * 
    */
   
    function svnx_init(){
        global $post;
        $this->post = $post;
        $meta = get_post_custom($this->post->ID);
        if ($meta["svn"][0] != ''){
            $repoID = $meta["svn"][0];
            $opt = $this->options["repo"][$repoID];  
            
            //print_r($opt);  
            //print_r($_GET);

            if (!$_GET["svn"]){
                $url = SVNX_URL . "websvn/listing.php";
            }
            else {
                $page = substr($_GET["svn"], 0, strpos($_GET["svn"], "?"));
                $repname = urlencode($opt["name"]);
                
                
                
                
                $url = SVNX_URL . "websvn/" . $page . "?repname=$repname";
                $omitArray = array("page_id", "svn");
                foreach(array_keys($_GET) as $g){
                    if (!in_array($g, $omitArray) && !is_array($_GET[$g])){
                        $url .= "&" . $g . "=" . $_GET[$g];
                    }
                    else if (!in_array($g, $omitArray)){
                        foreach($_GET[$g] as $v){
                            $url .= "&" . $g . "[]=" . $v;
                        }
                    }
                }
                
                
                //print($url);
                //print("<br><br><br>");
            }
            //print($path);
            //$file = file_get_contents($path);
            //print($file);
            //die();
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

            $this->svnx_prepLines($lines, $page, $repname);
            $css = str_replace("url(images", "url(" . SVNX_URL . "templates", $this->options["css"]);

            $this->text = "<style type=\"text/css\">" . $css . "</style>\r\n" . $this->text;
            add_filter('the_content', array($this, 'svnx_stroke'));   

        }
    }
    
    function svnx_headerLinks($page, $repname){
        $linkArray = array();
        if ($_GET["rev"] != ''){
            $url = SVNX_URL . "websvn/" . $page . "?repname=$repname";
            $omitArray = array("page_id", "svn", "rev");
            foreach(array_keys($_GET) as $g){
                if (!in_array($g, $omitArray)){
                    $recent .= "&" . $g . "=" . $_GET[$g];
                }
            }
            $linkArray[] = "<span class=\"goyoungest\"><a href=\"listing.php?repname=$repname" . "$recent\">Go to most recent revision</a></span>";
        }
    
        $x = 0;
        $text = "<div id=\"svnx_headerLinks\">";
        foreach($linkArray as $l){
            if ($x != 0){ $text .= "&nbsp;&nbsp;|&nbsp;&nbsp;"; }
            $text .= $l;
            $x++;
        }        
        $text .= "</div>";
        return $text;
    }
    
    function svnx_prepLines($text, $page, $repname){


        
        if ($page != "comp.php"){
            $breadcrumb = substr($text, strpos($text, '<h2 id="pathlinks">'));
            $breadcrumb = substr($breadcrumb, 0, strpos($breadcrumb, "</h2>"));            
            $headerLinks = substr($text, strpos($text, '<p>'));
            $headerLinks = substr($headerLinks, 0, strpos($headerLinks, "</p>"));
        }
        //$headerLinks = $this->svnx_headerLinks($page, $repname);
        
        $text = substr($text, strpos($text, "</h2>"));
        if ($page == "listing.php" || $page == "blame.php" || $page == ''){ 
            $text = substr($text, strpos($text, "<table>")); 
            $text = substr($text, 0, strpos($text, '<div id="footer">'));            
            $text = substr($text, 0, -30);  
            
        }
        else if ($page == "filedetails.php"){
            $text = substr($text, strpos($text, "<div class=\"listing\">")); 
            $text = substr($text, 0, strpos($text, '<div id="footer">'));            
            $text = substr($text, 0, -8);  
            
        }
        else if ($page == "log.php" || $page == "revision.php" || $page == "comp.php"){
            $text = substr($text, strpos($text, "<div id=\"wrap\">")); 
            $text = substr($text, 0, strpos($text, '<div id="footer">'));            
            $text = substr($text, 0, -8);  
          
        }
        else if ($page == "diff.php"){
            $text = substr($text, strpos($text, "<table>")); 
            $text = substr($text, 0, strpos($text, '<div id="footer">'));            
            $text = substr($text, 0, -15);             
        }
        else {
            
        }

        
        
        $link = get_page_link($this->post->ID); 
         
        $this->text = $breadcrumb;
        if ($headerLinks != ''){
            $this->text .= "<br /><br /><div id=\"svnxHeaderLinks\">$headerLinks</div>";
        }
        $this->text .= "<br /><br />" . $text;
        $text = str_replace("./templates/calm/images/", SVNX_URL . "templates/", $this->text);
        $text = str_replace("href=\"?", "href=\"$page?", $text);
        $text = str_replace("href=\"", "href=\"$link?&svn=", $text);
        $text = str_replace("action=\"\"", "action=\"$link?&svn=$page\"", $text);
        $text = str_replace("/*", '', $text);
        $text = str_replace("*/", '', $text);
        $text = trim(rtrim($text));
        
        $text = strip_tags($text, "<table><tr><td><b><i><strong><u><a><img><thead><tbody><th><span><div><br><p><pre>");
 
        //print("<br><br><br><br>" . $text);
        //die();
 
 
        $this->text = $text;
    }
    
    /**
    * returns the page after prepending the CSS
    * 
    */
    
    function svnx_stroke(){
        //$this->text = "<style type=\"text/css\">" . $this->options["css"] . "</style>"  . $this->text;
        return $this->text;
    }
}

?>
