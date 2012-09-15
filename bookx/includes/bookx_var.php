<?php
class bookx_var {
    
    function __construct(){
        $this->fetchSourceArray["bn"] = "Barnes & Noble";
        $this->fetchSourceArray["ol"] = "Open Library";
        
        $this->sortArray  = array("asc" => "Ascending", "desc" => "Descending");

        $this->fieldArray["bx_item_id"]           = "ID";
        $this->fieldArray["bx_item_name"]         = "Title";
        $this->fieldArray["bx_item_isbn"]         = "ISBN";
        $this->fieldArray["bx_item_author"]       = "Author";
        $this->fieldArray["bx_item_publisher"]    = "Publisher";
        $this->fieldArray["bx_item_date"]         = "Publish Date";
        $this->fieldArray["bx_item_pages"]        = "Pages";
        $this->fieldArray["bx_item_format"]       = "Format";
        $this->fieldArray["bx_item_price"]        = "Price";

        $this->filter    = array("No", "Yes");  
        $this->options   = get_option('bookx_options');   
        
    }
    
    
}
?>
