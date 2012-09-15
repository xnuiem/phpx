

function themexMove(x, direction){
	if (x == 1 && direction == 'up'){ return false; }
	else if (x == 12 && direction == 'down'){ return false; }

        var fieldName = "theme" + x;
        var monthName = "theme" + x + "-month";
        var dayName   = "theme" + x + "-day";
        var yearName  = "theme" + x + "-year";
        var timeName  = "theme" + x + "-time";

        if (direction == 'up'){
        	var newFieldName = "theme" + (x-1);
        	var newMonthName = "theme" + (x-1) + "-month";
        	var newDayName   = "theme" + (x-1) + "-day";
        	var newYearName  = "theme" + (x-1) + "-year";
        	var newTimeName  = "theme" + (x-1) + "-time";
        }
        else {
        	var newFieldName = "theme" + (parseInt(x) + 1);
        	var newMonthName = "theme" + (parseInt(x) + 1) + "-month";
        	var newDayName   = "theme" + (parseInt(x) + 1) + "-day";
        	var newYearName  = "theme" + (parseInt(x) + 1) + "-year";
        	var newTimeName  = "theme" + (parseInt(x) + 1) + "-time";
        }

	var saveField = document.getElementById(newFieldName).value;
	var saveMonth = document.getElementById(newMonthName).value;
	var saveDay   = document.getElementById(newDayName).value;
	var saveYear  = document.getElementById(newYearName).value;
	var saveTime  = document.getElementById(newTimeName).value;

	document.getElementById(newFieldName).value = document.getElementById(fieldName).value;
	document.getElementById(newMonthName).value = document.getElementById(monthName).value;
	document.getElementById(newDayName).value   = document.getElementById(dayName).value;
	document.getElementById(newYearName).value  = document.getElementById(yearName).value;
	document.getElementById(newTimeName).value  = document.getElementById(timeName).value;

	document.getElementById(fieldName).value = saveField;
	document.getElementById(monthName).value = saveMonth;
	document.getElementById(dayName).value   = saveDay;
	document.getElementById(yearName).value  = saveYear;
	document.getElementById(timeName).value  = saveTime;
}


