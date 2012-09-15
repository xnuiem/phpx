function goToURL(url){
    window.location = url;
}

//function confirmAction(actionText, url){
//    if (confirm(actionText)){
//        window.location = url;
//    }
//}


function confirmAction(actionText, url){
    var confirmButton = "<input class=\"button\" type=\"button\" value=\"Cancel\" onClick=\"toggleAlert();\" />&nbsp;&nbsp;<input class=\"button\" type=\"button\" value=\"Confirm\" onClick=\"goToURL('" + url + "');\" />&nbsp;&nbsp;";
    //document.getElementById('dimmer').style.display = 'block';
    jQuery('#dimmer').show();
    jQuery('#confirmWinText').html(actionText);
    jQuery('#buttonArea').html(confirmButton);
    //document.getElementById('confirmWin').innerHTML = "<p class=\"submit\">" + actionText + "<br /><br />" + confirmButton + "<br /><br /><a href=\"javascript:toggleAlert();\">Cancel</a></p>";
    jQuery('#confirmWin').show();
    //document.getElementById('confirmWin').style.display = 'block';
}

function toggleAlert(){
    jQuery('#dimmer').hide();
    jQuery('#confirmWin').hide();
}

var suiteXLoaded = true;