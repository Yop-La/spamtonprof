/**
 * fichier qui contient les fonctions js utiles aux autres fichiers js
 * 
 */


var waitForEl = function(selector, callback) {
	if (jQuery(selector).length) {
		callback();
	} else {
		setTimeout(function() {
			waitForEl(selector, callback);
		}, 100);
	}
};



function isEmail(email) {
	var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	return regex.test(email);
}

function redirect(slug ,info = ""  ){
	$("#hidden-form").attr("action", homeUrl.concat("/",slug) );
	$("#info").val(info);
	$("#hidden-form").submit();
}

function showMessage(message){
	$("#band-message").text(message);
	$("#top-message").removeClass("hide");
	window.scrollTo(0, 0);
}

function hideMessage(){
	$("#top-message").addClass("hide");
}

function isPositiveInteger(n) {
    return n >>> 0 === parseFloat(n);
}