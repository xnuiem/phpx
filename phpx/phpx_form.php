<?php
class phpx_form {
    
    var $idSet = 0;
    var $fieldset = false;
    var $required = false;
    var $formId;
    var $fieldsOnly = false;
    var $instantReturn = false;
    var $labels = true;
    var $useNameForId = false;
    
    function startForm($action='', $id="theForm", $method="post", $files=false, $onSubmit=''){
        $this->text = '';
        if ($this->fieldsOnly != true){
            $this->text .= "<form method=\"" . $method . "\" action=\"" . $action . "\" id=\"" . $id . "\"";
            if ($files == true){
                $this->text .= " enctype=\"multipart/form-data\"";
            }
            if ($onSubmit != ''){
                $this->text .= ' onSubmit="return ' . $onSubmit . '"';
            }
            $this->text .= ">";
        }
        $this->formId = $id;
        if ($this->instantReturn == true){
            $ret = $this->text;
            $this->text = '';
            return $ret;
        }
    }
    
    function endForm($buttonText="Submit"){
        if ($this->fieldset == true){ $this->endFieldSet(); }

        $this->text .= "<p class=\"submit\"><input class=\"button submit\" name=\"submit\" type=\"submit\" value=\"" . $buttonText . "\" id=\"" . $this->formId . "_end\" /></p>";        
        $this->text .= "</form>";
        $this->text .= $this->setRequired();
        return $this->text;   
    }
    
    function setRequired(){
        
        if ($this->required == true){
            $ret = "<script>jQuery(document).ready(function(){ jQuery(\"#" . $this->formId . "\").validate(); }); </script>";            
        }
        if ($this->instantReturn == true){
            return $ret;
        }        
        else { 
            $this->text .= $ret;
        }
    }
    
    function startFieldSet($legend=''){
        $this->text .= "<fieldset><legend>" . $legend . "</legend>";
        $this->fieldset = true;
        if ($this->instantReturn == true){
            $ret = $this->text;
            $this->text = '';
            return $ret;
        }          
        
    }
       
    function endFieldSet(){
        $this->text .= "</fieldset>";
        $this->fieldset = false;
        if ($this->instantReturn == true){
            $ret = $this->text;
            $this->text = '';
            return $ret;
        }          
    }
    
    function fileField($label, $name, $required=false){
        $id = ($this->useNameForId == true) ? $name : $this->idSet;
        if ($this->labels == true){ $this->text .= "<p><label>" . $label . "</label><em>"; }
        
        if ($required == true){ 
            $this->required = true;
            $this->text .= "*"; 
        }
        else { $this->text .= "&nbsp;&nbsp;"; }
        if ($this->labels == true){ $this->text .= "</em>"; }
        $this->text .= "<input type=\"file\" id=\"" . $id. "\" name=\"" . $name . "\"";
        if ($required != false && $required != ''){ 
            $this->text .= " class=\"required\"";  
        }
        $this->text .= " value=\"" . $value . "\" />";
        if ($this->labels == true){ $this->text .= "</p>";    }  
        $this->idSet++;
        if ($this->instantReturn == true){
            $ret = $this->text;
            $this->text = '';
            return $ret;
        }          
    }
    
    function freeText($text, $class=''){
        $text = '<p class="' . $class . '">' . $text . '</p>';
        $this->subText .= $text;
        $this->text .= $text;       
        if ($this->colName){ $col = $this->colName; $this->$col .= $text; } 
        if ($this->instantReturn == true){
            $ret = $this->text;
            $this->text = '';
            return $ret;
        }          
    }
    
    function reCaptcha($publicKey){
        if ($this->labels == true){ $this->text .= "<p><label>" . $label . "</label><em></em>"; }
        $this->text .= '<script type="text/javascript"
                            src="http://www.google.com/recaptcha/api/challenge?k=' . $publicKey . '">
                            </script>
                            <noscript>
                                <iframe src="http://www.google.com/recaptcha/api/noscript?k=' . $publicKey . '"
                                height="300" width="500" frameborder="0"></iframe><br>
                                <textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
                                <input type="hidden" name="recaptcha_response_field" value="manual_challenge">
                            </noscript>
        ';  
        $this->required = true;  
        if ($this->instantReturn == true){
            $ret = $this->text;
            $this->text = '';
            return $ret;
        }              
    }
    
    function phoneField($label, $name, $list, $value=array(), $required=false){
        $id = ($this->useNameForId == true) ? $name : $this->idSet;
        if ($this->labels == true){ $this->text .= "<p><label>" . $label . "</label><em>"; }
        if ($required == true){ 
            $this->required = true;
            $this->text .= "*"; 
        }
        else { $this->text .= "&nbsp;&nbsp;"; }
        if ($this->labels == true){ $this->text .= "</em>"; }
        $this->text .= "<input type=\"text\" id=\"" . $id . "\" name=\"" . $name . "_text\"";
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
        
        
        
        
        if ($this->labels == true){ $this->text .= "</p>";      }
        $this->idSet++;        
        if ($this->instantReturn == true){
            $ret = $this->text;
            $this->text = '';
            return $ret;
        }          
    }
    
    function dateField($label, $name, $value, $required=false, $useCalendar=false){
        
        if ($value > 1000){ $value = date("m/d/Y", $value); }
        else { $value = ''; }
        if ($this->labels == true){ $text .= "<p id=\"p" . $this->idSet . "\" class=\"date\"><label class=\"date\">" . $label . "</label><em>"; }
        else { 
             $text .= "<p id=\"p" . $this->idSet . "\" class=\"date\">";    
        }
        if ($required == true){ 
            $this->required = true;
            $text .= "*"; 
        }
        else { $text .= "&nbsp;&nbsp;"; }
        if ($this->labels == true){ $text .= "</em>"; }
        $text .= "<input type=\"text\" id=\"f" . $this->idSet . "\" name=\"" . $name . "\"";
        if ($required != false && $required != ''){ 
            $text .= " class=\"required date\"";      
        }
        $text .= " value=\"" . $value . "\" /></p>";  
        if ($useCalendar == true){   
            if ($value != ''){
                $defaultDate = '{ defaultDate: ' . $value . '}';
            } 
            $text .= '<script language="javascript">jQuery(function() { jQuery(\'#f' . $this->idSet . '\').datepicker(' . $defaultDate . ');});</script>';
        }
        $this->idSet++;  
        $this->subText .= $text;
        $this->text .= $text;
        if ($this->colName){ $col = $this->colName; $this->$col .= $text; }
        $this->date = true;
        if ($this->instantReturn == true){
            $ret = $this->text;
            $this->text = '';
            return $ret;
        }          
    }
    
    function dateRange($label, $name, $value=array(), $required=false, $useCalendar=true){
        if (!$this->ajax){
            $name1 = 1;
            $name2 = 2;
        }
        if ($value == ''){ $value = array(); }
        $x=1;
        foreach($value as $v){
            $fName = 'v' . $x;
            $$fName = ($v > 1000) ? date('m/d/Y', $v) : '';
            $x++;
        }    
        if ($this->labels == true){ $text .= "<p id=\"p" . $this->idSet . "\" class=\"date\"><label class=\"date\">" . $label . "</label><em>"; }
        else { 
             $text .= "<p id=\"p" . $this->idSet . "\" class=\"date\">";    
        }
        if ($required == true){ 
            $this->required = true;
            $text .= "*"; 
        }
        else { $text .= "&nbsp;&nbsp;"; }        
        if ($this->ajax){ $name1 = $this->idSet; }
        if ($this->labels == true){ $text .= "</em>"; }
        $text .= "<input type=\"text\" id=\"f" . $this->idSet . "\" name=\"$name" . "$name1\"";
        if ($required != false && $required != ''){ 
            $text .= " class=\"required date\"";      
        }
        $text .= " value=\"" . $v1 . "\" />&nbsp;&nbsp;to&nbsp;&nbsp;";  
        $cal1 = $this->idSet;        
        $this->idSet++;  
        if ($this->ajax){ $name2 = $this->idSet; }
        $text .=  "<input type=\"text\" id=\"f" . $this->idSet . "\" name=\"" . $name . "$name2\"";
        if ($required != false && $required != ''){ 
            $text .= " class=\"required date\"";      
        }
        $text .= " value=\"" . $v2 . "\" /></p>";          
        $cal2 = $this->idSet;
        $this->idSet++;  
        if ($useCalendar == true){
            
            $text .= '<script language="javascript"> 
                var dates = $("#f' . $cal1 . ', #f' . $cal2 . '").datepicker({
                    defaultDate: "+1w",
                    changeMonth: true,
                    numberOfMonths: 2,
                    onSelect: function( selectedDate ) {
                        var option = this.id == "f' . $cal1 . '" ? "minDate" : "maxDate",
                        instance = $( this ).data( "datepicker" ),
                        date = $.datepicker.parseDate(
                        instance.settings.dateFormat ||
                        $.datepicker._defaults.dateFormat,
                        selectedDate, instance.settings );
                        dates.not( this ).datepicker( "option", option, date );
                    }
                });
                </script>';
        }
        $this->subText .= $text;
        $this->text .= $text;
        if ($this->colName){ 
            $col = $this->colName; 
            $this->$col .= $text; 
        }
        $this->date = true;    
        if ($this->instantReturn == true){
            $ret = $this->text;
            $this->text = '';
            return $ret;
        }              
    }
    
    function calendarSetup(){
        
    }
    
    function password($label, $name, $required=false, $minLength="8", $confirm=false){
        
        $id = ($this->useNameForId == true) ? $name : $this->idSet;
        if ($this->labels == true){ $this->text .= "<p><label>" . $label . "</label><em>"; }
        if ($required == true){ 
            $this->required = true;
            $this->text .= "*"; 
        }
        else { $this->text .= "&nbsp;&nbsp;"; }
        if ($this->labels == true){ $this->text .= "</em>"; }
        $this->text .= "<input type=\"password\" id=\"" . $id . "\" name=\"" . $name . "\"";
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
        

        $this->text .= " />";
        if ($this->labels == true){$this->text .= "</p>";       }
        $this->idSet++;        
        
        
        
        
        if ($this->instantReturn == true){
            $ret = $this->text;
            $this->text = '';
            return $ret;
        }          
    }
    
    function checkBox($label, $name, $value=0, $required=false){
        $id = ($this->useNameForId == true) ? $name : $this->idSet;
        if ($this->labels == true){$this->text .= "<p><label>" . $label . "</label><em>";}
        if ($required == true){ 
            $this->required = true;
            $this->text .= "*"; 
        }
        else { $this->text .= "&nbsp;&nbsp;"; }
        if ($this->labels == true){$this->text .= "</em>"; }
        $this->text .= "<input type=\"checkbox\" id=\"" . $id  . "\" name=\"" . $name . "\"";
        if ($required != false && $required != ''){ 
            $this->text .= " class=\"required\"";  
        }
        $checked = ($value == 1 || $value == true) ? 'checked' : '';
        $this->text .= " $checked />";
        if ($this->labels == true){ $this->text .= "</p>";      }
        $this->idSet++;
        if ($this->instantReturn == true){
            $ret = $this->text;
            $this->text = '';
            return $ret;
        }          
    }
    
    
    function textField($label, $name, $value='', $required=false, $minLength="3"){
        $id = ($this->useNameForId == true) ? $name : $this->idSet;
        if ($this->labels == true){$this->text .= "<p><label>" . $label . "</label><em>";}
        if ($required == true){ 
            $this->required = true;
            $this->text .= "*"; 
        }
        else { $this->text .= "&nbsp;&nbsp;"; }
        if ($this->labels == true){$this->text .= "</em>";}
        $this->text .= "<input type=\"text\" id=\"" . $id . "\" name=\"" . $name . "\"";
        if ($required != false && $required != ''){ 
            if (!is_string($required)){ 
                $this->text .= " class=\"required\" minlength=\"" . $minLength . "\"";  
            }
            else {
                $this->text .= " class=\"required " . $required . "\" minlength=\"" . $minLength . "\"";      
            }
        }
        $this->text .= " value=\"" . $value . "\" />";
        if ($this->labels == true){$this->text .= "</p>";      }
        $this->idSet++;
        if ($this->instantReturn == true){
            $ret = $this->text;
            $this->text = '';
            return $ret;
        }          
    }
    
    function dropDown($label, $name, $value='', $list, $blank=false, $required=false, $multiple=false, $onChange=''){
        $id = ($this->useNameForId == true) ? $name : $this->idSet;
        if ((($label != null && $blank != 'label') || $blank == true) && $this->labels == true){
            $this->text .= "<p><label>" . $label . "</label><em>";
            if ($required == true){
                $this->required = true;
                $this->text .= "*";
            }
            else { $this->text .= "&nbsp;&nbsp;"; }
            $this->text .= '</em>';
        }
        $this->text .= "<select name=\"" . $name . "\" ";
        if ($multiple == true){
            $this->text .= " multiple size=\"8\" ";
        }
        if ($required == true){ $this->text .= "class=\"required\""; }
        $this->text .= " id=\"" . $id . "\" ";
        if ($onChange != ''){
            $this->text .= "onChange=\"$onChange\"";
        }
        
        
        $this->text .= " >";
        if ($blank == 'label' && $blank != true){
            $this->text .= '<option value="">' . $label . '</option>';
        }
        else if ($blank == true){
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
        
        
        $this->text .= "</select>";
        if (($label != null && $blank != 'label') && $this->labels == true){ $this->text .= "</p>"; }
        $this->idSet++;    
        if ($this->instantReturn == true){
            $ret = $this->text;
            $this->text = '';
            return $ret;
        }          
    }
    
    function textArea($label, $name, $value, $required=false, $minLength=8){
        $id = ($this->useNameForId == true) ? $name : $this->idSet;
        if ($this->labels == true){$this->text .= "<p><label>" . $label . "</label><em>";}
        if ($required == true){ 
            $this->required = true;
            $this->text .= "*"; 
        }
        else { $this->text .= "&nbsp;&nbsp;"; }
        if ($this->labels == true){$this->text .= "</em>";}
        $this->text .= "<textarea id=\"" . $id . "\" name=\"" . $name . "\"";
        if ($required != false && $required != ''){ 
            if (!is_string($required)){ 
                $this->text .= " class=\"required\" minlength=\"" . $minLength . "\"";  
            }
            else {
                $this->text .= " class=\"required " . $required . "\" minlength=\"" . $minLength . "\"";      
            }
        }
        $this->text .= ">" . stripslashes($value) . "</textarea>";
        if ($this->labels == true){$this->text .= "</p>";      }
        $this->idSet++;       
        if ($this->instantReturn == true){
            $ret = $this->text;
            $this->text = '';
            return $ret;
        }           
    }
    
    function hidden($name, $value=''){
        $id = ($this->useNameForId == true) ? $name : $this->idSet;
        $this->text .= "<input type=\"hidden\" name=\"" . $name . "\" value=\"" . $value . "\" id=\"" . $id . "\" />";
        $this->idSet++;
        if ($this->instantReturn == true){
            $ret = $this->text;
            $this->text = '';
            return $ret;
        }          
    }
    
    function freeField($label, $value){
       if ($this->labels == true){ $text .= '<p><label>' . $label . '</label><em>&nbsp;</em>' . $value . '</p>';}
       else { $text .= $value; }
        $this->subText .= $text;
        $this->text .= $text;   
        if ($this->colName){ $col = $this->colName; $this->$col .= $text; }     
        //$this->idSet++;    
        if ($this->instantReturn == true){
            $ret = $this->text;
            $this->text = '';
            return $ret;
        }          
    }    
}
?>