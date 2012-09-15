<?php
class suitex_form {
    
    var $idSet = 0;
    var $fieldset = false;
    var $required = false;
    var $formId;
    
    function startForm($action, $id="theForm", $method="post", $files=false){
        $this->text .= "<form method=\"" . $method . "\" action=\"" . $action . "\" id=\"" . $id . "\"";
        if ($files == true){
            $this->text .= " enctype=\"multipart/form-data\"";
        }
        
        $this->text .= ">";
        $this->formId = $id;
        
    }
    
    function endForm($buttonText="Submit"){
        if ($this->fieldset == true){ $this->endFieldSet(); }
        $this->text .= "<p class=\"submit\"><input class=\"submit\" name=\"submit\" type=\"submit\" value=\"" . $buttonText . "\" id=\"" . $this->formId . "_end\" /></p>";        
        $this->text .= "</form>";
        if ($this->required == true){
            $this->text .= "<script>\$(document).ready(function(){ \$(\"#" . $this->formId . "\").validate(); }); </script>";            
        }
        return $this->text;   
    }
    
    function startFieldSet($legend=''){
        $this->text .= "<fieldset><legend>" . $legend . "</legend>";
        $this->fieldset = true;
        
    }
       
    function endFieldSet(){
        $this->text .= "</fieldset>";
        $this->fieldset = false;
    }
    
    function fileField($label, $name, $required=false){
        
        $this->text .= "<p><label>" . $label . "</label><em>";
        if ($required == true){ 
            $this->required = true;
            $this->text .= "*"; 
        }
        else { $this->text .= "&nbsp;&nbsp;"; }
        $this->text .= "</em><input type=\"file\" id=\"" . $this->idSet . "\" name=\"" . $name . "\"";
        if ($required != false && $required != ''){ 
            $this->text .= " class=\"required\"";  
        }
        $this->text .= " value=\"" . $value . "\" /></p>";      
        $this->idSet++;
    }
    
    function freeText($text){
        $this->text .= "<p>" . $text . "</p>";
    }
    
    function phoneField($label, $name, $list, $value=array(), $required=false){
        
        $this->text .= "<p><label>" . $label . "</label><em>";
        if ($required == true){ 
            $this->required = true;
            $this->text .= "*"; 
        }
        else { $this->text .= "&nbsp;&nbsp;"; }
        $this->text .= "</em><input type=\"text\" id=\"" . $this->idSet . "\" name=\"" . $name . "_text\"";
        if ($required == true){ $this->text .= "class=\"required phone\""; }
        $this->text .= " value=\"" . $value[0] . "\" />&nbsp;";
        $this->text .= "<select name=\"" . $name . "_type\" ";
        if ($required == true){ $this->text .= "class=\"required\""; }
        $this->text .= "><option value=''></option>";
        foreach(array_keys($list) as $p){
            if ($p == $value[1] && $value[1] != ''){ $s = "selected"; }
            else { $s = ''; }
            $this->text .= "<option value=\"" . $p . "\" $s>" . $list[$p] . "</option>";
        }
        $this->text .= "</select>";
        if ($value[2] != ''){ 
            $this->text .= "<input type=\"hidden\" value=\"" . $value[2] . "\" name=\"" . $name . "_id\" />";
        }
        
        
        
        
        $this->text .= "</p>";      
        $this->idSet++;        
    }
    
    function dateField($label, $name, $value, $required=false){
        //if (!$this->date){
        //    $this->text .= "<script src=\"" .  RMX_URL . "js/jquery-widget.js\"></script>";
        //    $this->text .= "<script src=\"" .  RMX_URL . "js/jquery-datepicker.js\"></script>";
        //}   
        if ($value > 1000){ $value = date("m/d/Y", $value); }
        else { $value = ''; }
        $this->text .= "<p><label>" . $label . "</label><em>";
        if ($required == true){ 
            $this->required = true;
            $this->text .= "*"; 
        }
        else { $this->text .= "&nbsp;&nbsp;"; }
        $this->text .= "</em><input type=\"text\" id=\"" . $this->idSet . "\" name=\"" . $name . "\"";
        if ($required != false && $required != ''){ 
            $this->text .= " class=\"required date\"";      
        }
        $this->text .= " value=\"" . $value . "\" /></p>";      
        //$this->text .= "<script language=\"javascript\">\$(function() { \$(\"#" . $this->idSet . "\").datepicker(); });</script>";
        
        $this->idSet++;             
        
        

        
        
        
        
        $this->date = true;
    }
    
    function password($label, $name, $required=false, $minLength="8", $confirm=false){
        $this->text .= "<p><label>" . $label . "</label><em>";
        if ($required == true){ 
            $this->required = true;
            $this->text .= "*"; 
        }
        else { $this->text .= "&nbsp;&nbsp;"; }
        $this->text .= "</em><input type=\"password\" id=\"" . $this->idSet . "\" name=\"" . $name . "\"";
        if ($confirm != false){
            $this->text .= " equalTo=\"#" . ($this->idSet - 1) . "\"";
        }
        else if ($required != false && $required != ''){ 
            if (!is_string($required)){ 
                $this->text .= " class=\"required\" minlength=\"" . $minLength . "\"";  
            }
            else {
                $this->text .= " class=\"required " . $required . "\" minlength=\"" . $minLength . "\"";      
            }
        }
        

        $this->text .= " value=\"" . $value . "\" /></p>";      
        $this->idSet++;        
    }
    
    
    function textField($label, $name, $value='', $required=false, $minLength="3"){
        
        $this->text .= "<p><label>" . $label . "</label><em>";
        if ($required == true){ 
            $this->required = true;
            $this->text .= "*"; 
        }
        else { $this->text .= "&nbsp;&nbsp;"; }
        $this->text .= "</em><input type=\"text\" id=\"" . $this->idSet . "\" name=\"" . $name . "\"";
        if ($required != false && $required != ''){ 
            if (!is_string($required)){ 
                $this->text .= " class=\"required\" minlength=\"" . $minLength . "\"";  
            }
            else {
                $this->text .= " class=\"required " . $required . "\" minlength=\"" . $minLength . "\"";      
            }
        }
        $this->text .= " value=\"" . $value . "\" /></p>";      
        $this->idSet++;
    }
    
    function dropDown($label, $name, $value='', $list, $blank=false, $required=false, $multiple=false, $onChange=''){
        
        $this->text .= "<p><label>" . $label . "</label><em>";
        if ($required == true){
            $this->required = true;
            $this->text .= "*";
        }
        else { $this->text .= "&nbsp;&nbsp;"; }
        $this->text .= "</em><select name=\"" . $name . "\" ";
        if ($multiple == true){
            $this->text .= " multiple size=\"8\" ";
        }
        if ($required == true){ $this->text .= "class=\"required\""; }
        $this->text .= " id=\"" . $this->idSet . "\" ";
        if ($onChange != ''){
            $this->text .= "onChange=\"$onChange\"";
        }
        
        
        $this->text .= " >";
        if ($blank == true){
            $this->text .= "<option value=''></option>";
        }
        foreach(array_keys($list) as $l){
            if (is_array($value)){
                if (in_array($l, $value)){ $s = "selected"; } 
                else { $s = ''; }   
            }
            else {
                if ($l == $value && ($value != '')){ $s = "selected"; }
                else { $s = ''; }
            }
            $this->text .= "<option value=\"" . $l . "\" $s>" . $list[$l] . "</option>";
        }
        
        
        $this->text .= "</select></p>";
        $this->idSet++;    
    }
    
    function textArea($label, $name, $value, $required=false, $minLength=8){
        $this->text .= "<p><label>" . $label . "</label><em>";
        if ($required == true){ 
            $this->required = true;
            $this->text .= "*"; 
        }
        else { $this->text .= "&nbsp;&nbsp;"; }
        $this->text .= "</em><textarea id=\"" . $this->idSet . "\" name=\"" . $name . "\"";
        if ($required != false && $required != ''){ 
            if (!is_string($required)){ 
                $this->text .= " class=\"required\" minlength=\"" . $minLength . "\"";  
            }
            else {
                $this->text .= " class=\"required " . $required . "\" minlength=\"" . $minLength . "\"";      
            }
        }
        $this->text .= ">" . stripslashes($value) . "</textarea></p>";      
        $this->idSet++;        
    }
    
    function hidden($name, $value=''){
        $this->text .= "<input type=\"hidden\" name=\"" . $name . "\" value=\"" . $value . "\" />";
    }
    
    
    
    
    
    
}
?>
