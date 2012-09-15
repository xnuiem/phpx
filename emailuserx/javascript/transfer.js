          
function moveSelectedOptions(from,to,save,saveResult) {
    // Unselect matching options, if required
    if (arguments.length>3) {
        var regex = arguments[3];
        if (regex != "") {
            unSelectMatchingOptions(from,regex);
            }
        }
    // Move them over
    for (var i=0; i<from.options.length; i++) {
        var o = from.options[i];
        if (o.selected) {
            to.options[to.options.length] = new Option( o.text, o.value, false, false);
            }
        }
    // Delete them from original
    for (var i=(from.options.length-1); i>=0; i--) {
        var o = from.options[i];
        if (o.selected) {
            from.options[i] = null;
            }
        }
    if ((arguments.length<3) || (arguments[2]==true)) {
        sortSelect(from);
        sortSelect(to);
        }
    from.selectedIndex = -1;
    to.selectedIndex = -1;
    
    if (saveResult != false){
        selectAllOptions(to);
        save.value = getSelectedValues(to);    
    }
    else {
        selectAllOptions(from);
        save.value = getSelectedValues(from);          
    }
    
    
    } 
    
      function unSelectMatchingOptions(obj,regex) {
    selectUnselectMatchingOptions(obj,regex,"unselect",false);
    }

function selectAllOptions(obj) {
    for (var i=0; i<obj.options.length; i++) {
        obj.options[i].selected = true;
        }
    }
    
function getSelectedValues (select) {
  var r = new Array();
  for (var i = 0; i < select.options.length; i++)
    if (select.options[i].selected)
      r[r.length] = select.options[i].value;
  return r;
}

    
function sortSelect(obj) {
    var o = new Array();
    if (obj.options==null) { return; }
    for (var i=0; i<obj.options.length; i++) {
        o[o.length] = new Option( obj.options[i].text, obj.options[i].value, obj.options[i].defaultSelected, obj.options[i].selected) ;
        }
    if (o.length==0) { return; }
    o = o.sort(
        function(a,b) {
            if ((a.text+"") < (b.text+"")) { return -1; }
            if ((a.text+"") > (b.text+"")) { return 1; }
            return 0;
            }
        );

    for (var i=0; i<o.length; i++) {
        obj.options[i] = new Option(o[i].text, o[i].value, o[i].defaultSelected, o[i].selected);
        }
    }           
