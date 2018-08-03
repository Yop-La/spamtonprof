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

function redirect(slug ,info = "" ){
	info = htmlentities.encode(info);
	$("#hidden-form").attr("action", homeUrl.concat("/",slug) );
	$("#info").val($("<div>").html(info).text());
	$("#hidden-form").submit();
}

function showMessage(message){
	$("#band-message").text(message);
	$("#top-message").removeClass("hide");
	window.scrollTo(0, 0);
}

function hideMessage(){

	if(!$("#top-message").hasClass('hide')){

		$("#top-message").addClass("hide");

	}
}

function isPositiveInteger(n) {
	return n >>> 0 === parseFloat(n);
}

function toFieldId(id){

	return("#nf-field-".concat(id));

}

(function(window){
	window.htmlentities = {
			/**
			 * Converts a string to its html characters completely.
			 *
			 * @param {String} str String with unescaped HTML characters
			 **/
			encode : function(str) {
				var buf = [];

				for (var i=str.length-1;i>=0;i--) {
					buf.unshift(['&#', str[i].charCodeAt(), ';'].join(''));
				}

				return buf.join('');
			},
			/**
			 * Converts an html characterSet into its original character.
			 *
			 * @param {String} str htmlSet entities
			 **/
			decode : function(str) {
				return str.replace(/&#(\d+);/g, function(match, dec) {
					return String.fromCharCode(dec);
				});
			}
	};
})(window);