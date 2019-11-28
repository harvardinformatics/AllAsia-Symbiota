$(document).ready(function() {
	if(!navigator.cookieEnabled){
		alert("Your browser cookies are disabled. To be able to login and access your profile, they must be enabled for this domain.");
	}
	$('#tabs').tabs({ active: tabIndex });
});

function selectAll(cb){
	boxesChecked = true;
	if(!cb.checked){
		boxesChecked = false;
	}
	var dbElements = document.getElementsByName("occid[]");
	for(i = 0; i < dbElements.length; i++){
		var dbElement = dbElements[i];
		dbElement.checked = boxesChecked;
	}
}

function toggle(target){
	var objDiv = document.getElementById(target);
	if(objDiv){
		if(objDiv.style.display=="none"){
			objDiv.style.display = "block";
		}
		else{
			objDiv.style.display = "none";
		}
	}
	else{
	  	var divs = document.getElementsByTagName("div");
	  	for (var h = 0; h < divs.length; h++) {
	  	var divObj = divs[h];
			if(divObj.className == target){
				if(divObj.style.display=="none"){
					divObj.style.display="block";
				}
			 	else {
			 		divObj.style.display="none";
			 	}
			}
		}
	}
}

function ProcessReport(){
	if(document.pressed == 'invoice'){
		document.reportsform.action ="reports/defaultinvoice.php";
	}
	else if(document.pressed == 'spec'){
		document.reportsform.action ="reports/defaultspecimenlist.php";
	}
	else if(document.pressed == 'label'){
		document.reportsform.action ="reports/defaultmailinglabel.php";
	}
	else if(document.pressed == 'envelope'){
		document.reportsform.action ="reports/defaultenvelope.php";
	}
	if(document.getElementById("printbrowser").checked){
		document.reportsform.target = "_blank";
	}
	if(document.getElementById("printdoc").checked){
		document.reportsform.target = "_self";
	}
	return true;
}

function displayNewLoanOut(){
	if(document.getElementById("loanoutToggle")){
		toggle('newloanoutdiv');
	}
	var f = document.newloanoutform;
	if(f.loanidentifierown.value == ""){
		generateNewId(f.collid.value,f.loanidentifierown,"out");
	}
}

function displayNewLoanIn(){
	if(document.getElementById("loaninToggle")){
		toggle('newloanindiv');
	}
	var f = document.newloaninform;
	if(f.loanidentifierborr.value == ""){
		generateNewId(f.collid.value,f.loanidentifierborr,"in");
	}
}

function displayNewExchange(){
	if(document.getElementById("exchangeToggle")){
		toggle('newexchangediv');
	}
	var f = document.newexchangegiftform;
	if(f.identifier.value == ""){
		generateNewId(f.collid.value,f.identifier,"ex");
	}
}

function generateNewId(collId,targetObj,idType){
	$.ajax({
		method: "POST",
		data: { idtype: idType, collid: collId },
		dataType: "text",
		url: "rpc/generateNextID.php"
	})
	.done(function(retID) {
		targetObj.value = retID;
	})
	.fail(function() {
		alert("Generation of new ID failed");
	});
}

function verfifyLoanOutAddForm(f){
	if(f.reqinstitution.options[f.reqinstitution.selectedIndex].value == 0){
		alert("Select an institution");
		return false;
	}
	if(f.loanidentifierown.value == ""){
		alert("Enter a loan identifier");
		return false;
	}
	$.ajax({
		method: "POST",
		data: { ident: f.loanidentifierown.value, collid: f.collid.value, type: "out" },
		dataType: "text",
		url: "rpc/identifierCheck.php"
	})
	.done(function(retCode) {
		if(retCode == 1) alert("There is already a transaction with that identifier, please enter a different one.");
		else f.submit();
	});
	return false;
}

function verifyLoanInAddForm(f){
	if(f.iidowner.options[f.iidowner.selectedIndex].value == 0){
		alert("Select an institution");
		return false;
	}
	if(f.loanidentifierborr.value == ""){
		alert("Enter a loan identifier");
		return false;
	}
	$.ajax({
		method: "POST",
		data: { ident: f.loanidentifierborr.value, collid: f.collid.value, type: "in" },
		dataType: "text",
		url: "rpc/identifierCheck.php"
	})
	.done(function(retCode) {
		if(retCode == 1) alert("There is already a transaction with that identifier, please enter a different one.");
		else f.submit();
	});
	return false;
}

function verifyLoanInEditForm(f){
	if(f.iidowner.options[f.iidowner.selectedIndex].value == 0){
		alert("Select an institution");
		return false;
	}
	if(f.loanidentifierown.value == ""){
		alert("Enter the sender's loan number");
		return false;
	}
	return true;
}

function verfifyExchangeAddForm(f){
	if(f.iid.options[f.iid.selectedIndex].value == 0){
		alert("Select an institution");
		return false;
	}
	if(f.identifier.value == ""){
		alert("Enter an exchange identifier");
		return false;
	}
	$.ajax({
		method: "POST",
		data: { ident: f.identifier.value, collid: f.collid.value, type: "ex" },
		dataType: "text",
		url: "rpc/identifierCheck.php"
	})
	.done(function(retCode) {
		if(retCode == 1) alert("There is already a transaction with that identifier, please enter a different one.");
		else f.submit();
	});
	return false;
}

function verifySpecEditForm(f){
	//Make sure at least on specimen checkbox is checked
	var cbChecked = false;
	var dbElements = document.getElementsByName("occid[]");
	for(i = 0; i < dbElements.length; i++){
		var dbElement = dbElements[i];
		if(dbElement.checked){
			cbChecked = true;
			break;
		}
	}
	if(!cbChecked){
		alert("Please select specimens to which you wish to apply the action");
		return false;
	}

	//If task equals delete, confirm action
	var applyTaskObj = f.applytask;
	var l = applyTaskObj.length;
	var applyTaskValue = "";
	for(var i = 0; i < l; i++) {
		if(applyTaskObj[i].checked) {
			applyTaskValue = applyTaskObj[i].value;
		}
	}
	if(applyTaskValue == "delete"){
		return confirm("Are you sure you want to remove selected specimens from this loan?");
	}
	return true;
}

function addSpecimen(f,splist){ 
	if(!f.catalognumber.value){
		alert("Please enter a catalog number!");
		return false;
	}
	else{
		//alert("rpc/insertLoanSpecimens.php?loanid="+f.loanid.value+"&catalognumber="+f.catalognumber.value+"&collid="+f.collid.value);
		$.ajax({
			method: "POST",
			data: { loanid: f.loanid.value, catalognumber: f.catalognumber.value, collid: f.collid.value },
			dataType: "text",
			url: "rpc/insertLoanSpecimens.php"
		})
		.done(function(retStr) {
			if(retStr == "0"){
				document.getElementById("addspecsuccess").style.display = "none";
				document.getElementById("addspecerr1").style.display = "block";
				document.getElementById("addspecerr2").style.display = "none";
				document.getElementById("addspecerr3").style.display = "none";
				setTimeout(function () { 
					document.getElementById("addspecerr1").style.display = "none";
				}, 4000);
				//alert("ERROR: Specimen record not found in database.");
			}
			else if(retStr == "1"){
				f.catalognumber.value = '';
				document.getElementById("addspecsuccess").style.display = "block";
				document.getElementById("addspecerr1").style.display = "none";
				document.getElementById("addspecerr2").style.display = "none";
				document.getElementById("addspecerr3").style.display = "none";
				setTimeout(function () { 
					document.getElementById("addspecsuccess").style.display = "none";
				}, 4000);
				//alert("SUCCESS: Specimen record added to loan.");
				if(splist == 0){
					document.getElementById("speclistdiv").style.display = "block";
					document.getElementById("nospecdiv").style.display = "none";
				}
			}
			else if(retStr == "2"){
				document.getElementById("addspecsuccess").style.display = "none";
				document.getElementById("addspecerr1").style.display = "none";
				document.getElementById("addspecerr2").style.display = "block";
				document.getElementById("addspecerr3").style.display = "none";
				setTimeout(function () { 
					document.getElementById("addspecerr2").style.display = "none";
				}, 4000);
				//alert("ERROR: More than one specimen with that catalog number.");
			}
			else if(retStr == "3"){
				document.getElementById("addspecsuccess").style.display = "none";
				document.getElementById("addspecerr1").style.display = "none";
				document.getElementById("addspecerr2").style.display = "none";
				document.getElementById("addspecerr3").style.display = "block";
				setTimeout(function () { 
					document.getElementById("addspecerr3").style.display = "none";
				}, 4000);
				//alert("ERROR: More than one specimen with that catalog number.");
			}
			else{
				f.catalognumber.value = "";
				document.refreshspeclist.emode.value = 1;
				document.refreshspeclist.submit();
				/*
				document.getElementById("addspecsuccess").style.display = "block";
				document.getElementById("addspecerr1").style.display = "none";
				document.getElementById("addspecerr2").style.display = "none";
				document.getElementById("addspecerr3").style.display = "none";
				setTimeout(function () { 
					document.getElementById("addspecsuccess").style.display = "none";
					}, 5000);
				alert("SUCCESS: Specimen added to loan.");
				*/
			}
		})
		.fail(function() {
			alert("Generation of new ID failed");
		});
	}
	return false;
}

function openIndPopup(occid){
	openPopup('../individual/index.php?occid=' + occid);
}

function openEditorPopup(occid){
	openPopup('../editor/occurrenceeditor.php?occid=' + occid);
}

function openPopup(urlStr){
	var wWidth = 900;
	if(document.body.offsetWidth) wWidth = document.body.offsetWidth*0.9;
	if(wWidth > 1400) wWidth = 1400;
	newWindow = window.open(urlStr,'popup','scrollbars=1,toolbar=0,resizable=1,width='+(wWidth)+',height=600,left=20,top=20');
	if (newWindow.opener == null) newWindow.opener = self;
	return false;
}

function verifyDate(eventDateInput){
	//test date and return mysqlformat
	var dateStr = eventDateInput.value;
	if(dateStr == "") return true;

	var dateArr = parseDate(dateStr);
	if(dateArr['y'] == 0){
		alert("Unable to interpret Date. Please use the following formats: yyyy-mm-dd, mm/dd/yyyy, or dd mmm yyyy");
		return false;
	}
	else{
		//Check to see if date is in the future 
		try{
			var testDate = new Date(dateArr['y'],dateArr['m']-1,dateArr['d']);
			var today = new Date();
			if(testDate > today){
				alert("The date you entered has not happened yet. Please revise.");
				return false;
			}
		}
		catch(e){
		}

		//Check to see if day is valid
		if(dateArr['d'] > 28){
			if(dateArr['d'] > 31 
				|| (dateArr['d'] == 30 && dateArr['m'] == 2) 
				|| (dateArr['d'] == 31 && (dateArr['m'] == 4 || dateArr['m'] == 6 || dateArr['m'] == 9 || dateArr['m'] == 11))){
				alert("The Day (" + dateArr['d'] + ") is invalid for that month");
				return false;
			}
		}

		//Enter date into date fields
		var mStr = dateArr['m'];
		if(mStr.length == 1){
			mStr = "0" + mStr;
		}
		var dStr = dateArr['d'];
		if(dStr.length == 1){
			dStr = "0" + dStr;
		}
		eventDateInput.value = dateArr['y'] + "-" + mStr + "-" + dStr;
	}
	return true;
}

function verifyDueDate(eventDateInput){
	//test date and return mysqlformat
	var dateStr = eventDateInput.value;
	if(dateStr == "") return true;

	var dateArr = parseDate(dateStr);
	if(dateArr['y'] == 0){
		alert("Unable to interpret Date. Please use the following formats: yyyy-mm-dd, mm/dd/yyyy, or dd mmm yyyy");
		return false;
	}
	else{
		//Check to see if date is in the future 
		try{
			var testDate = new Date(dateArr['y'],dateArr['m']-1,dateArr['d']);
			var today = new Date();
			if(testDate < today){
				alert("The due date you entered has already passed. Please revise.");
				return false;
			}
		}
		catch(e){
		}

		//Check to see if day is valid
		if(dateArr['d'] > 28){
			if(dateArr['d'] > 31 
				|| (dateArr['d'] == 30 && dateArr['m'] == 2) 
				|| (dateArr['d'] == 31 && (dateArr['m'] == 4 || dateArr['m'] == 6 || dateArr['m'] == 9 || dateArr['m'] == 11))){
				alert("The Day (" + dateArr['d'] + ") is invalid for that month");
				return false;
			}
		}

		//Enter date into date fields
		var mStr = dateArr['m'];
		if(mStr.length == 1){
			mStr = "0" + mStr;
		}
		var dStr = dateArr['d'];
		if(dStr.length == 1){
			dStr = "0" + dStr;
		}
		eventDateInput.value = dateArr['y'] + "-" + mStr + "-" + dStr;
	}
	return true;
}

function parseDate(dateStr){
	var y = 0;
	var m = 0;
	var d = 0;
	try{
		var validformat1 = /^\d{4}-\d{1,2}-\d{1,2}$/ //Format: yyyy-mm-dd
		var validformat2 = /^\d{1,2}\/\d{1,2}\/\d{2,4}$/ //Format: mm/dd/yyyy
		var validformat3 = /^\d{1,2} \D+ \d{2,4}$/ //Format: dd mmm yyyy
		if(validformat1.test(dateStr)){
			var dateTokens = dateStr.split("-");
			y = dateTokens[0];
			m = dateTokens[1];
			d = dateTokens[2];
		}
		else if(validformat2.test(dateStr)){
			var dateTokens = dateStr.split("/");
			m = dateTokens[0];
			d = dateTokens[1];
			y = dateTokens[2];
			if(y.length == 2){
				if(y < 20){
					y = "20" + y;
				}
				else{
					y = "19" + y;
				}
			}
		}
		else if(validformat3.test(dateStr)){
			var dateTokens = dateStr.split(" ");
			d = dateTokens[0];
			mText = dateTokens[1];
			y = dateTokens[2];
			if(y.length == 2){
				if(y < 15){
					y = "20" + y;
				}
				else{
					y = "19" + y;
				}
			}
			mText = mText.substring(0,3);
			mText = mText.toLowerCase();
			var mNames = new Array("jan","feb","mar","apr","may","jun","jul","aug","sep","oct","nov","dec");
			m = mNames.indexOf(mText)+1;
		}
		else if(dateObj instanceof Date && dateObj != "Invalid Date"){
			var dateObj = new Date(dateStr);
			y = dateObj.getFullYear();
			m = dateObj.getMonth() + 1;
			d = dateObj.getDate();
		}
	}
	catch(ex){
	}
	var retArr = new Array();
	retArr["y"] = y.toString();
	retArr["m"] = m.toString();
	retArr["d"] = d.toString();
	return retArr;
}

function verifyLoanDet(){
	if(document.getElementById('dafsciname').value == ""){
		alert("Scientific Name field must have a value");
		return false;
	}
	if(document.getElementById('identifiedby').value == ""){
		alert("Determiner field must have a value (enter 'unknown' if not defined)");
		return false;
	}
	if(document.getElementById('dateidentified').value == ""){
		alert("Determination Date field must have a value (enter 's.d.' if not defined)");
		return false;
	}
	//If sciname was changed and submit was clicked immediately afterward, wait 5 seconds so that name can be verified 
	if(pauseSubmit){
		var date = new Date();
		var curDate = null;
		do{ 
			curDate = new Date(); 
		}while(curDate - date < 5000 && pauseSubmit);
	}
	return true;
}

//Determination form methods 
function initLoanDetAutocomplete(f){
	$( f.sciname ).autocomplete({ 
		source: "../editor/rpc/getspeciessuggest.php", 
		minLength: 3,
		change: function(event, ui) {
			if(f.sciname.value){
				pauseSubmit = true;
				verifyLoanDetSciName(f);
			}
			else{
				f.scientificnameauthorship.value = "";
				f.family.value = "";
				f.tidtoadd.value = "";
			}				
		}
	});
}

function verifyLoanDetSciName(f){
	$.ajax({
		type: "POST",
		url: "../editor/rpc/verifysciname.php",
		dataType: "json",
		data: { term: f.sciname.value }
	}).done(function( data ) {
		if(data){
			f.scientificnameauthorship.value = data.author;
			f.family.value = data.family;
			f.tidtoadd.value = data.tid;
		}
		else{
            alert("WARNING: Taxon not found. It may be misspelled or needs to be added to taxonomic thesaurus by a taxonomic editor.");
			f.scientificnameauthorship.value = "";
			f.family.value = "";
			f.tidtoadd.value = "";
		}
	});
}