<?php
class bookx_fetch_bn {

    function __construct($parent){
        $this->parent = $parent;
    }
    
    
    
    /** 
    * Fetches book information based on the isbn
    * 
    * @param    mixed   $isbn
    * @return   array   $this->parent->bookArray
    */
        
    function bookx_fetchItem($isbn){


        $url = 'http://www.barnesandnoble.com/s/' . $isbn . '?keyword=' . $isbn . '&store=allproducts';
        
        

        if (function_exists('curl_exec')){
            print("used CURL");
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url );
            curl_setopt($ch, CURLOPT_HEADER, 1); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $lines = curl_exec($ch);
            echo curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch); 
        }
        else {
            $lines = file_get_contents($url);
        }
        print("HERE");
        print($lines);
        die();

        if (!$lines || $lines == ''){ return false; }
        $start = "<div class=\"preview\">";

        $lines = substr($lines, strpos($lines, $start));
        //print($lines); 

        if (substr_count($lines, "<div id=\"tab-edreviews\"")){
            $end = "<div id=\"tab-edreviews\"";
        }        
        //else if (substr_count($lines, "<h3 class=\"pr-selected\">")){
        //    $end = "<h3 class=\"pr-selected\">";
        //}
        
        
        
        
        $lines = substr($lines, 0, strpos($lines, $end));
        
        $lines = str_replace("\r", '', $lines);
        $lines = str_replace("\n", '', $lines);
        $lines = str_replace("\t", '', $lines);


        
        $titleLine = substr($lines, strpos($lines, "<h1>"));
        $titleLine = substr($titleLine, 0, strpos($titleLine, "</h1>"));
        $titleLine = strip_tags($titleLine);
               
        
        $title = substr($titleLine, 0, strpos($titleLine, "by"));
        $title = trim(rtrim($title));
        
        $author = substr($titleLine, strpos($titleLine, "by") + 2);
        $author = trim(rtrim($author));
        
        $price = substr($lines, strpos($lines, "\$"));
        $price = substr($price, 0, strpos($price, ".") + 3);
        $price = str_replace("\$", '', $price);
        
        
        $publisher = substr($lines, strpos($lines, "Publisher:"));
        $publisher = substr($publisher, 0, strpos($publisher, "</li>"));
        $publisher = str_replace("Publisher:", '', $publisher);
        
        $pubDate = substr($lines, strpos($lines, "Pub. Date:"));
        $pubDate = substr($pubDate, 0, strpos($pubDate, "</li>"));
        $pubDate = str_replace("Pub. Date:", '', $pubDate);
        $pubDate = strtotime($pubDate);
        
        $pages = substr($lines, strpos($lines, "Pub. Date:"));
        $pages = substr($pages, strpos($pages, "pp") - 5);

        $pages = substr($pages, 0, strpos($pages, "</li>"));
        $pages = str_replace("i", '', $pages);
        $pages = str_replace(">", '', $pages);
        $pages = str_replace("l", '', $pages);
        $pages = str_replace("<", '', $pages);
        $pages = str_replace("pp", '', $pages);
        $pages = trim(rtrim($pages));
        
        if (!is_numeric($pages)){ $pages = 0; }
        
        $format = substr($lines, strpos($lines, "<p class=\"format\">"));
        $format = substr($format, 0, strpos($format, "</p>"));
        $format = str_replace("(", '', $format);
        $format = str_replace(")", '', $format);
        //print($lines);
        //print("<br><br><br><br><br>\r\n\r\n");
        $summary = substr($lines, strpos($lines, "<h3>Synopsis</h3>"));
        //print($summary);
        //die();
        $summary = substr($summary, 0, strpos($summary, "</p>"));
        $summary = str_replace("<h3>Synopsis</h3>", '', $summary);
        $summary = str_replace("—", "-", $summary);
        //$summary = htmlentities($summary);
        
        
        $image = substr($lines, strpos($lines, "<a class=\"underline\""));
        $image = substr($image, 0, strpos($image, "</a>"));
        
        
        
        
        
        $image = strip_tags($image, "<img>");
        
        //print("IMAGE: " . $image);
        //die();
        
        /*
        $source = substr($image, strpos($image, "src=") + 5);
        $source = substr($source, 0, strpos($source, "alt"));
        $source = trim(rtrim(str_replace('"', '', $source)));
        
        $sourceTest = strtolower($source);
        
        if (substr_count($sourceTest, ".jpg") || substr_count($source, ".jpeg") || substr_count($source, ".jpe")){ 
            $imageType = "image/jpeg"; 
        }
        else if (substr_count($sourceTest, ".gif")){
            $imageType = "image/gif";
        }
        else if (substr_count($sourceTest, ".png")){
            $imageType = "image/png";
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $source );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $imageData = curl_exec($ch);
        curl_close($ch);
        */

        //if (substr_count($title, "Knuffle")){ $this->die = true; }                
        if ($title != ''){ print("Working on $title <br />"); }
        flush();
        
        
        $this->parent->addBookToArray("publisher", $publisher);
        $this->parent->addBookToArray("price", $price);
        $this->parent->addBookToArray("author", $author);
        $this->parent->addBookToArray("name", $title);
        $this->parent->addBookToArray("date", $pubDate);
        $this->parent->addBookToArray("pages", $pages);
        $this->parent->addBookToArray("format", $format);
        $this->parent->addBookToArray("summary", $summary, "<br>");
        $this->parent->addBookToArray("image", $image, true);
        //$this->parent->addBookToArray("image_type", $imageType);
        $this->parent->addBookToArray("link", $url, true);
        $this->parent->addBookToArray("isbn", $isbn);
        
        //print("<br><br><br><br><br><br>");
        //print_r($this->parent->bookArray);
        //die();
        return true;
    }    
    
}  
?>
