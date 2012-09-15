<?php
class suitex_view {
    
    
    
    function startView($title = false, $statusLine = false){
        $text = "<div class=\"postbox\">";
        
        if ($title != false){ $text .= "<h3><label>$title</label></h3>"; }
        if ($statusLine != false){ $text .= $statusLine; }
        $text .= "<div class=\"inside\">";         
        $text .= "<div class=\"rmxView\">";
        $this->viewHeader = $text;
        
    }
    
    function endView(){
        
    }
    
    function addRow($label, $value, $type="label"){
        $this->rows[] = "<p><label>$label</label>$value</p>";
    }
    
    
}
?>
