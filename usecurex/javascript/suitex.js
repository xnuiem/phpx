function goToURL(url){
    window.location = url;
}

function confirmAction(actionText, url){
    if (confirm(actionText)){
        window.location = url;
    }
}

function filterForm(url){
    if (document.getElementById('camp') != null){
        var camp = document.getElementById('camp')[document.getElementById('camp').selectedIndex].value;
        url += '&camp=' + camp;
    }

    if (document.getElementById('center') != null){
        var center = document.getElementById('center')[document.getElementById('center').selectedIndex].value;  
        url += '&center=' + center;      
    } 
    
    if (document.getElementById('dateStart') != null){
        var startDate = document.getElementById('dateStart').value; 
        url += "&start=" + startDate;  
    }
    
    if (document.getElementById('dateEnd') != null){
        var endDate = document.getElementById('dateEnd').value; 
        url += "&end=" + endDate;  
    }
    
    if (document.getElementById('offer') != null){
        var offer = document.getElementById('offer')[document.getElementById('offer').selectedIndex].value;
        url += "&offer=" + offer;
    }
    
    if (document.getElementById('type') != null){
        var type = document.getElementById('type')[document.getElementById('type').selectedIndex].value;
        url += "&type=" + type;
    }

    //$.get(url, function(data){ $(tab).html(data);  });      
    window.location = url;
}
