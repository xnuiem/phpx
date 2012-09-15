<?php
class bookx_fetch_ol {

    function __construct($parent){
        $this->parent = $parent;
    }
    
    
    
    /** 
    * Fetches book information based on the isbn
    * 
    * @param    mixed   $isbn
    * @return   array   $this->bookArray
    */
        
    function bookx_fetchItem($isbn){
        $url = 'http://www.openlibrary.org/search?isbn=' . $isbn;

        if (function_exists('curl_exec')){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url );
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $lines = curl_exec($ch);
            curl_close($ch);
        }
        else {
            $lines = file_get_contents($url);
        }
        
        if (!$lines || $lines == ''){ return false; }
        $price = '0.00';
        
        
        $start = "<h6 class=\"title\">Open Library</h6>";
        $lines = substr($lines, strpos($lines, $start));
        $end = "</span>";
        $lines = substr($lines, 0, strpos($lines, $end));
        $lines = strip_tags($lines);
        $objNumber = trim(rtrim(str_replace("Open Library", '', $lines)));
        
        $xmlUrl = "http://openlibrary.org/books/" . $objNumber . ".rdf";
        
        
        if (function_exists('curl_init')){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $xmlUrl );
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $xmlData = curl_exec($ch);
            curl_close($ch);
        }
        else {
            $xmlData = simplexml_load_file($xmlUrl);
        } 
        
        $replaceArray = array("rdf", "bibo", "dcterms", "dc", "dcam", "@");
        
        foreach($replaceArray as $r){
            $xmlData = str_replace($r . ":", '', $xmlData);
        }
        
        
        
        
        $xml = new SimpleXMLElement($xmlData);    
        $title = $xml->Description->title;
        $pages = $xml->Description->extent;

        if ($pages == ''){ $pages = 0; }

        $imageSrc = "http://covers.openlibrary.org/b/id/" . $isbn . "-M.jpg";
        if (!is_file($imageSrc)){
            $image = '';    
        }
        else {
            $image = "<img src=\"$imageSrc\" alt=\"$title\" border=\"0\" height=\"" . $this->var->options["list_image_height"] . "\" width=\"" . $this->var->options["list_image_width"] . "\" />";
        }
        foreach($xml->Description as $desc){
            $link = (string) $desc['about'];   
                
        }

        $date = strtotime($xml->Description->issued);

        //if ($title != ''){ print("Working on $title <br />"); }
        flush();
        
        
        $this->parent->addBookToArray("publisher", $xml->Description->publisher);
        $this->parent->addBookToArray("price", $price);
        $this->parent->addBookToArray("author", $xml->Description->authorList->Description->value);
        $this->parent->addBookToArray("name", $title);
        $this->parent->addBookToArray("date", $date);
        $this->parent->addBookToArray("pages", $pages);
        $this->parent->addBookToArray("format", $format);
        $this->parent->addBookToArray("summary", $xml->Description->description, "<br>");
        $this->parent->addBookToArray("image", $image, true);
        //$this->parent->addBookToArray("image_type", $imageType);
        $this->parent->addBookToArray("link", $link, true);
        $this->parent->addBookToArray("isbn", $isbn);
        /*
        print($xmlData);
        print("<br><br><hr><br><br>");
        print_r($xml);
        print("<br><br><hr><br><br>");
        print_r($this->parent->bookArray);
        die();
        */
        return true;
    }    
    
}  
?>
