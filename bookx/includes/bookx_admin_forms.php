<?php
class bookx_admin_forms  {
    
    
    /**
    * Imports the imported file created in bookx_export()
    * 
    */
    
    function bookx_import(){
        
        if ($_POST["action"] == "import" && $_FILES["import"]["tmp_name"] != ''){        
            $file = file($_FILES["import"]["tmp_name"]);
            foreach($file as $f){
                $r = explode("\"|\"", $f);
                $insertArray = array();
                foreach($r as $e){
                    $e = trim(rtrim($e), '"');
                    $e = trim(rtrim($e));
                    $insertArray[] = $e;
                }
                
                $count = $this->parent->wpdb->get_var("select count(*) from " . $this->parent->wpdb->prefix . "bx_item where bx_item_isbn = '" . $insertArray[0] . "' limit 1");
                if ($count == 0){
                    $sql = $this->parent->wpdb->prepare("insert into " . $this->parent->wpdb->prefix . "bx_item (bx_item_isbn, bx_item_comments, bx_item_sidebar, bx_item_summary, bx_item_no_update_desc) values (%s, %s, %d, %s, %d)", $insertArray);

                    $this->parent->wpdb->query($sql);
                    
                }
            }
            $this->parent->bookx_refreshAll(true);
        }
        else {

            
            
        
            if (!is_writable(BOOKX_DIR . "export/")){
                $noExport = true;
            }        
         
            $text = "<br /><br /><div class=\"wrap\">";
            $text .= "<form method=\"post\" action=\"" . $this->parent->baseURL . "&sub=admin\" enctype=\"multipart/form-data\">";
            $text .= "<fieldset class=\"bookx\"><legend>Import\Export Booklist</legend>";

            if ($_POST["action"] == "import"){
                $this->parent->bookx_checkCode("f");
                $text .= $this->parent->status;
            }
            if ($_GET["export"] == $this->parent->var->options["export"] && $this->parent->var->options["export"] != ''){
                $text .= "<div class=\"bookxMessage\"><strong>Your export file has been created.</strong><br /><br /> ";
                $text .= "<a href=\"" . BOOKX_URL . "includes/bookx_export.php?file=" . $this->parent->var->options["export"] . "\">Download Book List</a>";
                $text .= "</div>";
            }  

            if ($noExport){
                $text .= "<div class=\"bookxMessage\">In order to create an export file, the directory <strong>" . BOOKX_DIR . "export/</strong> must be writable by the webserver.</div>";
            }
            else {
            
                $text .= "<a name=\"export\"></a><div><a href=\"" . $this->parent->baseURL . "&sub=export&_wpnonce=" . $this->nonce . "\">Click Here to Export Booklist</a></div>";
            }

          
        
            
            

            $text .= "<input type=\"hidden\" name=\"action\" value=\"import\" />";

            
        
            $text .= "<table class=\"form-table\">";    
            $text .= "<tr class=\"form-field form-required\">";
            $text .= "<th scope=\"row\" valign=\"top\"><label for=\"image_size\">Import Book List File:</label></th>";
            $text .= "<td><input type=\"file\" name=\"import\" class=\"file\" />";
            $text .= "</td></tr>";     
            $text .= "</table>";
            $text .= "<p class=\"submit\"><input type=\"submit\" name=\"Submit\" value=\"Import Booklist\" />";
        
            $text .= "</p></form></fieldset></div>";
            return $text;
        }
    }   
    
    /**
    * DEPRICATED
    * 
    */
    
    function bookx_deleteForm(){
            $text = "<br /><br /><div class=\"wrap\">";

            $text .= "<fieldset class=\"bookx\"><legend>UnInstall BookX</legend>";


            
        
            $text .= "<table class=\"form-table\">";    
            $text .= "<tr class=\"form-field form-required\">";
            $text .= "<td>This will remove BookX completely, including all your books.  If you are just wanting to refresh, use the Plugin page to Deactivate and reactivate the plugin.";
            $text .= "<br /><br /><input type=\"button\" id=\"deleteButton\" value=\"Uninstall BookX\" onClick=\"confirmAction('Are you sure you want to completely uninstall BookX?', '" . $this->parent->baseURL . "&sub=delete&_wpnonce=" . $this->nonce . "');\" />";
            $text .= "</td></tr>";     
            $text .= "</table>";
        
            $text .= "</p></fieldset></div>";
            return $text;        
    }
    
    /**
    * The administration page for updating options
    *
    * @param NULL
    * @return NULL
    */    
    
    function bookx_adminPage(){
        $this->nonce = wp_create_nonce();
        $alignArray['left']     = "Left";
        $alignArray['right']    = "Right";
        $alignArray['']         = '';
        
        if ($_POST['action'] == "update"){
            $this->parent->var->options['per_page']              = $_POST['per_page'];
            $this->parent->var->options['per_widget']            = $_POST['per_widget'];
            $this->parent->var->options['listTemplate']          = $_POST['list'];
            $this->parent->var->options['widgetTemplate']        = $_POST['widget'];
            $this->parent->var->options['list_image_height']     = $_POST['list_image_height'];
            $this->parent->var->options['list_image_width']      = $_POST['list_image_width'];
            $this->parent->var->options['detail_image_height']   = $_POST['detail_image_height'];
            $this->parent->var->options['detail_image_width']    = $_POST['detail_image_width'];
            $this->parent->var->options['detailTemplate']        = $_POST['detail'];
            $this->parent->var->options['list_characters']       = $_POST['list_characters'];
            $this->parent->var->options['css']                   = $_POST['css'];
            $this->parent->var->options['list_image_align']      = $_POST['list_image_align'];
            $this->parent->var->options['detail_image_align']    = $_POST['detail_image_align'];
            $this->parent->var->options['list_search']           = $_POST['list_search'];
            $this->parent->var->options['list_filter']           = $_POST['list_filter'];
            $this->parent->var->options['list_order_default']    = $_POST['list_order_default'];
            $this->parent->var->options['list_sort_default']     = $_POST['list_sort_default'];
            $this->parent->var->options['fetch']                 = $_POST['fetch'];
            $this->parent->var->options['failover']              = $_POST['failover'];
            
            update_option('bookx_options', $this->parent->var->options);
            
            
            
                        

        }
        

        
        

        $text = "<div class=\"wrap\"><h2>BookX</h2>";
        
        $text .= "<form method=\"post\" action=\"" . $this->parent->baseURL . "&sub=admin\">";
        $text .= "<fieldset class=\"bookx\"><legend>General Options</legend>";
        $text .= "<input type=\"hidden\" name=\"action\" value=\"update\" />";
        if ($_POST['action'] == "update"){
            $this->parent->bookx_checkCode("c");
            $text .= $this->parent->status;
        }

        
        $text .= "<table class=\"form-table\">";
        
        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"fetch\">Source:</label></th>";
        $text .= "<td><select name=\"fetch\">";
        foreach(array_keys($this->parent->var->fetchSourceArray) as $f){
            if ($f == $this->parent->var->options["fetch"]){ $s = "selected"; }
            else { $s = ''; }
            $text .= "<option value=\"$f\" $s>" . $this->parent->var->fetchSourceArray[$f] . "&nbsp;&nbsp;&nbsp;&nbsp;</option>";
        }
        $text .= "</select></td></tr>";  
        
        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"fetch\">If ISBN not found, try the other sources:</label></th>";
        $text .= "<td><select name=\"failover\">";
        foreach(array_keys($this->parent->var->filter) as $f){
            if ($f == $this->parent->var->options["failover"]){ $s = "selected"; }
            else { $s = ''; }
            $text .= "<option value=\"$f\" $s>" . $this->parent->var->filter[$f] . "&nbsp;&nbsp;&nbsp;&nbsp;</option>";
        }
        $text .= "</select></td></tr>";          
                
        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"per_page\"># per page:</label></th>";
        $text .= "<td><input type=\"text\" name=\"per_page\" value=\"" . $this->parent->var->options['per_page'] . "\" />";
        $text .= "</td></tr>";
        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"image_size\">List Default Order Field:</label></th>";
        $text .= "<td><select name=\"list_order_default\">";
        foreach(array_keys($this->parent->var->fieldArray) as $f){
            if ($f == $this->parent->var->options["list_order_default"]){ $s = "selected"; }
            else { $s = ''; }
            $text .= "<option value=\"$f\" $s>" . $this->parent->var->fieldArray[$f] . "</option>";
        }
        $text .= "</select></td></tr>";  
        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"image_size\">List Sort Default:</label></th>";
        $text .= "<td><select name=\"list_sort_default\">";
        foreach(array_keys($this->parent->var->sortArray) as $sort){
            if ($sort == $this->parent->var->options["list_sort_default"]){ $s = "selected"; }
            else { $s = ''; }
            $text .= "<option value=\"$sort\" $s>" . $this->parent->var->sortArray[$sort] . "</option>";
        }
        $text .= "</select></td></tr>";          

        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"image_size\">Allow Users to Change Order Field:</label></th>";
        $text .= "<td><select name=\"list_filter\">";
        foreach(array_keys($this->parent->var->filter) as $f){
            if ($f == $this->parent->var->options['list_filter']){ $s = "selected"; }
            else { $s = ''; }
            $text .= "<option value=\"$f\" $s>" . $this->parent->var->filter[$f] . "</option>";
        }
        $text .= "</select></td></tr>";  

        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"image_size\">Enable Search:</label></th>";
        $text .= "<td><select name=\"list_search\">";
        foreach(array_keys($this->parent->var->filter) as $f){
            if ($f == $this->parent->var->options['list_search']){ $s = "selected"; }
            else { $s = ''; }
            $text .= "<option value=\"$f\" $s>" . $this->parent->var->filter[$f] . "</option>";
        }
        $text .= "</select></td></tr>";          
        
        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"image_size\">List Image Width:</label></th>";
        $text .= "<td><input type=\"text\" name=\"list_image_width\" value=\"" . $this->parent->var->options['list_image_width'] . "\" />";
        $text .= "</td></tr>";
        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"image_size\">List Image Height:</label></th>";
        $text .= "<td><input type=\"text\" name=\"list_image_height\" value=\"" . $this->parent->var->options['list_image_height'] . "\" />";
        $text .= "</td></tr>"; 
        
        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"image_size\">List Image Align:</label></th>";
        $text .= "<td><select name=\"list_image_align\">";
        foreach(array_keys($alignArray) as $a){
            if ($a == $this->parent->var->options['list_image_align']){ $s = "selected"; }
            else { $s = ''; }
            $text .= "<option value=\"$a\" $s>" . $alignArray[$a] . "</option>";
        }
        $text .= "</select></td></tr>";           
        
        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"image_size\">Detail Image Width:</label></th>";
        $text .= "<td><input type=\"text\" name=\"detail_image_width\" value=\"" . $this->parent->var->options['detail_image_width'] . "\" />";
        $text .= "</td></tr>";
        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"image_size\">Detail Image Height:</label></th>";
        $text .= "<td><input type=\"text\" name=\"detail_image_height\" value=\"" . $this->parent->var->options['detail_image_height'] . "\" />";
        $text .= "</td></tr>";         

        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"image_size\">Detail Image Align:</label></th>";
        $text .= "<td><select name=\"detail_image_align\">";
        foreach(array_keys($alignArray) as $a){
            if ($a == $this->parent->var->options['detail_image_align']){ $s = "selected"; }
            else { $s = ''; }
            $text .= "<option value=\"$a\" $s>" . $alignArray[$a] . "</option>";
        }
        
        $text .= "</select></td></tr>";   


        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"image_size\"># Characters for Summary & Comments in List View:</label></th>";
        $text .= "<td><input type=\"text\" name=\"list_characters\" value=\"" . $this->parent->var->options['list_characters'] . "\" />";
        $text .= "</td></tr>";                 
        
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>CSS:</strong></td>";
        $text .= "<td><textarea name=\"css\">" . stripslashes($this->parent->var->options['css']) . "</textarea>";
        $text .= "</td></tr>"; 
        $text .= "<tr><td colspan=\"2\">";
        $text .= "The following fields are to create the look & field for three display areas, the Widget, List, and the Detail.<br /><br />";
        $text .= "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Widget Template:</strong></td>";
        $text .= "<td><textarea name=\"widget\">" . stripslashes($this->parent->var->options["widgetTemplate"]) . "</textarea>";
        $text .= "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>List Template:</strong></td>";
        $text .= "<td><textarea name=\"list\">" . stripslashes($this->parent->var->options['listTemplate']) . "</textarea>";
        $text .= "</td></tr>";  
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Detail Template:</strong></td>";
        $text .= "<td><textarea name=\"detail\">" . stripslashes($this->parent->var->options['detailTemplate']) . "</textarea>";
        $text .= "</td></tr>";         
        $text .= "<tr><td colspan=\"2\">";  
        $text .= "In addition to HTML, the three template fields will accept the following field subsitution tags: <ul>";
        $text .= "<li>::TITLE:: - The title of the book</li>";
        $text .= "<li>::AUTHOR:: - The author(s)</li>";
        $text .= "<li>::ISBN:: - The ISBN (13)</li>";
        $text .= "<li>::PUBLISHER:: - The Publisher</li>";
        $text .= "<li>::DATE:: - The publish date</li>";
        $text .= "<li>::PAGES:: - Number of pages</li>";
        $text .= "<li>::FORMAT:: - Publish Format</li>";
        $text .= "<li>::ELINK:: - External Link</li>";
        $text .= "<li>::LINK:: - Link to the Detail view of Book.  Not available in Detail View.</li>";
        $text .= "<li>::IMAGE:: - Image of Cover (scaled)</li>";
        $text .= "<li>::PRICE:: - Price</li>";
        $text .= "<li>::SUMMARY:: - Summary from external source.  Not available in the Widget.</li>";
        $text .= "<li>::COMMENTS:: - Your comments.  Not available in the Widget.</li>";
        $text .= "<li>::MORE:: - More link to detail view.  Only available in List View.</li>";
        $text .= "</ul>";
        $text .= "</td></tr>";
        $text .= "</table>";
        $text .= "<p class=\"submit\"><input type=\"submit\" name=\"Submit\" value=\"Save Changes\" />";
        $text .= "</p></fieldset></form></div>";
        
        
        
        $text .= $this->bookx_import();
        //$text .= $this->bookx_deleteForm();
        
        
        
        $this->parent->bookx_stroke($text);
    }   
    
    /**
    * The form to add or modify a book.
    * 
    */
    
    function bookx_form($code=''){
       
        if ($code != ''){

            
            if ($_POST["id"]){
                $query = "select bx_item_name as name from " . $this->parent->wpdb->prefix . "bx_item where bx_item_id = %d limit 1";
                $row = $this->parent->wpdb->get_row($this->parent->wpdb->prepare($query, $_POST["id"]));
                $row->isbn = $_POST["isbn"];
                $row->sidebar = $_POST["sidebar"];
                $row->comments = $_POST["comments"];
                $row->summary  = $_POST["summary"];
                $row->no_update = $_POST["no_update"];                
                $_GET["id"] = $_POST["id"];
                $label = "Modify Book : " . $row->name; 
                $action = "modify";
            }
            else {
                $row->isbn = $_POST["isbn"];
                $row->sidebar = $_POST["sidebar"];
                $row->comments = $_POST["comments"];
                $row->summary  = $_POST["summary"];
                $row->no_update = $_POST["no_update"];
                $label = "Add Book";
                $action = "add";                 
            }

            $status = "<span style=\"font-weight: bold; color: #FF0000;\">" . $code . "</span><br />";            
        }
        else if ($_GET["id"]){
            $query = "select bx_item_name as name, bx_item_isbn as isbn, bx_item_comments as comments, bx_item_sidebar as sidebar, bx_item_summary as summary, bx_item_no_update_desc as no_update from " . $this->parent->wpdb->prefix . "bx_item where bx_item_id = %d limit 1";
            $row = $this->parent->wpdb->get_row($this->parent->wpdb->prepare($query, $_GET["id"]));
            
        
            $label = "Modify Book : " . $row->name;            
            $action = "modify";
            
        }
        else {
            $label = "Add Book";
            $action = "add";    
        }
        
        
        
        
        $this->nonce = wp_create_nonce();
        
        $text = "<div class=\"wrap\">";
        $text .= "<h2>BookX - Books</h2>";
        $text .= "<br />";


        $text .= "<div id=\"poststuff\" class=\"metabox-holder\">";
        $text .= "<div id=\"post-body\" class=\"has-sidebar\">";
        $text .= "<div id=\"post-body-content\" class=\"has-sidebar-content\">";
        $text .= "<div class=\"postbox\">";
        $text .= "<h3><label>$label</label></h3>";
        $text .= $status;
        
        $text .= "<div class=\"inside\">";
        $text .= "<form method=\"post\" action=\"" . $this->parent->baseURL . "&sub=submit\">";
        $text .= "<input type=\"hidden\" name=\"_wpnonce\" value=\"" . $this->nonce . "\" />";
        $text .= "<input type=\"hidden\" name=\"action\" value=\"$action\" />";
        
        if ($_GET["id"]){
            $text .= "<input type=\"hidden\" name=\"id\" value=\"" . $_GET["id"] . "\" />";
        }
        $text .= "<table class=\"form-table\">";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>ISBN:</strong></td>";
        $text .= "<td><input type=\"text\" name=\"isbn\" value=\"" . $row->isbn . "\" />";
        $text .= "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Add to Sidebar:</strong></td>";
        $text .= "<td><select name=\"sidebar\">";
        foreach(array_keys($this->parent->var->filter) as $f){
            if ($f == $row->sidebar){ $s = "selected"; }
            else { $s = ''; }
            $text .= "<option value=\"$f\" $s>" . $this->parent->var->filter[$f] . "</option>";
        }
        
        
        $text .= "</select></td></tr>";        
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Comments:</strong></td>";
        $text .= "<td><textarea name=\"comments\">" . $row->comments . "</textarea>";
        $text .= "</td></tr>";
        
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Summary from Source:</strong></td>";
        $text .= "<td><textarea name=\"summary\">" . $row->summary . "</textarea>";
        $text .= "</td></tr>";            
        
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Protect Summary from Updating:</strong></td>";
        $text .= "<td><select name=\"no_update\">";
        foreach(array_keys($this->parent->var->filter) as $f){
            if ($f == $row->no_update){ $s = "selected"; }
            else { $s = ''; }
            $text .= "<option value=\"$f\" $s>" . $this->parent->var->filter[$f] . "</option>";
        }
        
        
        $text .= "</select></td></tr>";            
        
        
        $text .= "</table>";
        $text .= "<p class=\"submit\"><input type=\"submit\" name=\"Submit\" value=\"Save Changes\" />";
        if ($action == "modify"){
            $deleteURL = $this->parent->baseURL . "&sub=submit&id=" . $_GET["id"] . "&_wpnonce=" . $this->nonce;
            $text .= "&nbsp;<input type=\"button\" value=\"Delete\" onClick=\"confirmAction('Are you sure you want to delete this book?', '$deleteURL');\" />";
        }
        $text .= "</p></form>";
        $text .= "</div></div>";
        
        
        if ($action == "add"){
            $text .= "<div class=\"postbox\">";
            $text .= "<h3><label>Add Books</label></h3>";
        
            $text .= "<div class=\"inside\">";
            $text .= "<form method=\"post\" action=\"" . $this->parent->baseURL . "&sub=submit\">";
            $text .= "<input type=\"hidden\" name=\"_wpnonce\" value=\"" . $this->nonce . "\" />";
            $text .= "<input type=\"hidden\" name=\"action\" value=\"adds\" />";
            $text .= "<table class=\"form-table\">";
      
            $text .= "<tr class=\"form-field\">";
            $text .= "<td><strong>Multiple ISBNs (one per line):</strong></td>";
            $text .= "<td><textarea name=\"books\" rows=\"20\"></textarea>";
            $text .= "</td></tr>";
            $text .= "</table>";
            $text .= "<p class=\"submit\"><input type=\"submit\" name=\"Submit\" value=\"Save Changes\" />";
            $text .= "</p></form>";
            $text .= "</div></div>";        
        }    
        $text .= "</div></div>";
        $text .= "</div></div>";
        $this->parent->bookx_stroke($text);
    }         
    
    
    
    
    
}
?>
