function goToURL(url){
    window.location = url;
}

function confirmAction(actionText, url){
    if (confirm(actionText)){
        window.location = url;
    }
}