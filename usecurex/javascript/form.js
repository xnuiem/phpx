function addressValidToggle(){
    if (document.getElementById('sas') != null){
        if (document.getElementById('sas').checked == true){
            document.getElementById('billing_line_1').value     = document.getElementById('line_1').value; 
            document.getElementById('billing_line_2').value     = document.getElementById('line_2').value;
            document.getElementById('billing_line_3').value     = document.getElementById('line_3').value;
            document.getElementById('billing_city').value       = document.getElementById('city').value;
            document.getElementById('billing_state').value      = document.getElementById('state').value;
            document.getElementById('billing_zip').value        = document.getElementById('zip').value;
            document.getElementById('billing_country').value    = document.getElementById('country').value;
        
            document.getElementById('billing_line_1').disabled = true;
            document.getElementById('billing_line_2').disabled = true;
            document.getElementById('billing_line_3').disabled = true;
            document.getElementById('billing_city').disabled = true;
            document.getElementById('billing_state').disabled = true;
            document.getElementById('billing_zip').disabled = true;
            document.getElementById('billing_country').disabled = true;
        }
        else {
            document.getElementById('billing_line_1').disabled = false;
            document.getElementById('billing_line_2').disabled = false;
            document.getElementById('billing_line_3').disabled = false;
            document.getElementById('billing_city').disabled = false;
            document.getElementById('billing_state').disabled = false;
            document.getElementById('billing_zip').disabled = false;
            document.getElementById('billing_country').disabled = false;        
        }
    }
}

function changeAmount(field){
    var value = field[field.selectedIndex].value;
    var amt = amountArray[value];
    document.getElementById('amountDisplay').innerHTML = "$ " + amt;
    document.getElementById('amount').value = amt;
}

function validateForm(form){
    var showText = '';
    for(i=0;i<validFields.length;i++){
        if (document.getElementById(validFields[i]).value == ''){
            showText += validLabels[i] + ' is a required field<br />';
            document.getElementById(validFields[i]).style.background = '#ffd2d2';
        } 
    }
    
    if (document.getElementById('ccnumber')){
        if (!Mod10(document.getElementById('ccnumber').value)){
            showText += "Invalid CC Number<br />";
            document.getElementById('ccnumber').style.background = '#ffd2d2';
        }
    }
    
    if (typeof(phoneFields) != 'undefined'){
        for(i=0;i<phoneFields.length;i++){
            var fld = document.getElementById(phoneFields[i]);
    
            var stripped = fld.value.replace(/[\(\)\.\-\ ]/g, '');    
    
            if (isNaN(parseInt(stripped))) {
                showText += phoneLabels[i] + ' contains illegal characters<br />'; 
                document.getElementById(phoneFields[i]).style.background = '#ffd2d2';
            } 
            else if (!(stripped.length == 10)) {
                showText += phoneLabels[i] + ' is the wrong length.<br />'; 
                document.getElementById(phoneFields[i]).style.background = '#ffd2d2';        
            }
        }
    }
    
    if (showText){
        toggleLayer('response');
        document.getElementById('response').innerHTML = '<b>Form Submission Error</b><br /><br />' + showText + "<br /><a href=\"#\" onclick=\"return toggleLayer('response');\">Close Window</a>";
        return false;
    }
    return true;
}

function toggleLayer(layerName){
    if (document.getElementById(layerName).style.display == '' || document.getElementById(layerName).style.display == 'none'){
        document.getElementById(layerName).style.display = 'block';
        document.getElementById('dimmer').style.display = 'block';
        return false;
        
    }
    else {
        document.getElementById(layerName).style.display = 'none';
        document.getElementById('dimmer').style.display = 'none';
        return false;
    }
}

function fillInCCName(field, send){
    document.getElementById(send).value = field.value;
}