function goToURL(url){
	window.location = url;
}

function confirmAction(actionText, url){
	if (confirm(actionText)){
		window.location = url;
	}
}

function openSub(subName, imageName, imagePath){
	var sub   = document.getElementById(subName);
	var image = document.getElementById(imageName);

	var plus  = imagePath + "/" + "plus.gif";
	var minus = imagePath + "/" + "minus.gif";


	if (sub){
		if (sub.style.display == 'none'){
			sub.style.display = '';
			image.src = minus;
		}
		else {
			sub.style.display = 'none';
			image.src = plus;
		}
	}
}

function showTab(boxNum) {
//alert(document.getElementById("tab").length);
    for (i=1;i<5;i++) {
        if (document.getElementById("tab"+i) && document.getElementById("vtab"+i)){
            document.getElementById("tab"+i).className = "";
            document.getElementById("vtab"+i).style.display = "none";
        }
    }
    //if (!document.getElementById("tab"+boxNum)){ boxNum = 1; }
    //if (!document.getElementById("tab"+boxNum)){ boxNum = 2; }

    // added the if statements to avoid anoying javascript error messages
    if ( document.getElementById("tab"+boxNum) ) {
        document.getElementById("tab"+boxNum).className="selected";
    }
    if ( document.getElementById("vtab"+boxNum) ) {
        document.getElementById("vtab"+boxNum).style.display = 'block';
    }
    //document.cookie =  page + "=" + escape(boxNum);
}
