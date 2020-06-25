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

String.prototype.capitalize = function() {
	return this.charAt(0).toUpperCase() + this.slice(1);
}

function isEmail(email) {
	var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+jQuery/;
	return regex.test(email);
}

function redirectTo(slug ,info = "" ){


	if(!jQuery('#info').length){
		jQuery('body').append("<form id=\"hidden-form\" method=POST action=\"\'.get_home_url().\'\/abonnement-apres-essai\'.\'\/\"><input type=\"hidden\" id=\"info\" name=\"info\" value=\"\" \/><\/form>");
	}

	info = htmlentities.encode(info);
	waitForEl("#info",function(){
		jQuery("#hidden-form").attr("action", homeUrl.concat("/",slug) );
		jQuery("#info").val(jQuery("<div>").html(info).text());
		jQuery("#hidden-form").submit();
	});
}

function showLoader(){
	jQuery('.loader').fadeIn('slow');

}

function hideLoader(){
	jQuery('.loader').fadeOut();
}


function showMessage(message){
	waitForEl("#top-message", function() {

		if(jQuery("#band-message .elementor-heading-title").length){
			jQuery("#band-message .elementor-heading-title").html(message);
		}else{
			jQuery("#band-message").html(message);
		}
		jQuery("#top-message").removeClass("hide");
		window.scrollTo(0, 0);
	});
}

function hideMessage(){

	if(!jQuery("#top-message").hasClass('hide')){

		jQuery("#top-message").addClass("hide");

	}
}

function isPositiveInteger(n) {
	return n >>> 0 === parseFloat(n);
}

function toFieldId(id){

	return("#nf-field-".concat(id));

}

function clone(src) {
	return Object.assign({}, src);
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


function getParameterByName(name, url) {
	if (!url) url = window.location.href;
	name = name.replace(/[\[\]]/g, '\\$&');
	var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
	results = regex.exec(url);
	if (!results) return null;
	if (!results[2]) return '';
	return decodeURIComponent(results[2].replace(/\+/g, ' '));
}

function setCookie(name,value,days) {
	var expires = "";
	if (days) {
		var date = new Date();
		date.setTime(date.getTime() + (days*24*60*60*1000));
		expires = "; expires=" + date.toUTCString();
	}
	document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}

function getCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}
