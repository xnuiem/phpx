function goToURL(url){
    window.location = url;
}

//function confirmAction(actionText, url){
//    if (confirm(actionText)){
//        window.location = url;
//    }
//}


function confirmAction(actionText, url){
    var confirmButton = "<input type=\"button\" value=\"Confirm\" onClick=\"goToURL('" + url + "');\" />";
    document.getElementById('dimmer').style.display = 'block';
    document.getElementById('alertWin').innerHTML = "<p class=\"submit\">" + actionText + "<br /><br />" + confirmButton + "<br /><br /><a href=\"javascript:toggleAlert();\">Cancel</a></p>";
    document.getElementById('alertWin').style.display = 'block';
}

function toggleAlert(){
    document.getElementById('dimmer').style.display = 'none';
    document.getElementById('alertWin').style.display = 'none';    
}





