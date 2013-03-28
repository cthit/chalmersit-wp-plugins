/*
	Simple jQuery tabs plugin
*/
(function($){

	$.fn.bookingtabs = function(options){
		if(options.tabContainer === undefined){
			return false;
		}

		var nav = $(this),
			settings = {
				activeClass: "current",
				useHash: true,
				hashPrefix: "tab-",
				el: "a"
			};

		settings = $.extend({}, settings, options);

		return this.each(function(){
			var $tabContainers = $(settings.tabContainer),
				hash = location.hash && ("#"+ settings.hashPrefix + location.hash.replace("#","")),
				which = (settings.useHash && hash) || ":first";

			$tabContainers.hide().filter(which).show();

			$(this).find(settings.el).on("click", function(evt){
				evt.preventDefault();
				var tab = $tabContainers.filter(this.hash);

				$tabContainers.hide();
				tab.show();

				nav.find(settings.el).removeClass(settings.activeClass);
				$(this).addClass(settings.activeClass);
				location.hash = tab.attr("id").replace(settings.hashPrefix, "");

			});

			if(which == ":first")
				$(this).find(settings.el).filter(which).click();
			else
				$(this).find(settings.el).filter('[href="'+which+'"]').click();

		});
	};

})(jQuery);

/*
	Javascript functions for the Bookings plugin
 */

/**
 * Correct date parsing
 * 
 * @param Date on the format YYYY-mm-dd
 * @return Date object from param
 */
function parseDateFromString(input) {
	var parts = input.match(/(\d+)/g);
	return new Date(parts[0], parts[1]-1, parts[2]);
}


function formatDate(date) {
	var month = date.getMonth() + 1;
	if(month < 10) {
		month = "0"+month;
	}

	return date.getFullYear()+"-"+ month +"-"+date.getDate();
}

function formatDateFromString(date) {
	// Strip non-numeric stuff and build new string (YYYY-mm-dd)
	var d = date.replace(/[^0-9]/g, ""),
		year = d.substr(0, 4),
		month = d.substr(4, 2),
		day = d.substr(6, 2);

	return year+"-"+month+"-"+day;
}

$(function() {

	// Set up tabs
	$(".bookingtabs").tabs({
		tabContainer: ".tab-container > div",
		activeClass: "tab-current"
	});

	$(".booking-list > li").on("click", function() {
		var self = $(this),
			others = $(".booking-list > li");

		others.fadeTo(0, 1.0);

		self.toggleClass("collapsed").find(".booking-details").slideToggle(200);
		
		var opacity = self.hasClass("collapsed") ? .6 : 1.0;
		others.not(self).fadeTo(200, opacity);
	});

	if(! $(".location-container input[value='Grupprummet']").is(":checked") ) {
		$(".hide").hide();
	}

	// Toggle repeat checkbox when room is 'Grupprummet'
	$(".location-container input").on("change", function(evt) {
		$(".booking-repeat-container").toggle();

		if($(this).val() !== "Grupprummet") {
			$("#booking-repeat-check").attr("checked", false);
			$(".booking-repeat-info").toggle();	
		}
	});

	/*
		Format the dates correctly (Ex: YYYY-mmdd => YYYY-mm-dd)
	 */
	$(".booking-dates-container input").on("blur", function(evt) {
		var date = $(this).val();
		if(!date.match(/^\d{4}-\d{1,2}-\d{1,2}$/)) {
			$(this).val(formatDateFromString(date));
		}
	});

	$("#booking-repeat-check").on("change", function(evt) {
		$(".booking-repeat-info").slideToggle(200);
	});

	$("#is-party-booking").on("change", function(evt) {
		
	});

	/*
		Prevent overlapping dates. I.e. don't let users input
		start: 2013-02-23 and end: 2013-02-20 and vice versa.
	 */
	$(".booking-dates-container input").on("change", function(evt) {
		var $this = $(this),
			$other = $(".booking-dates-container").find("input").not($this),
			newDate, start_date, end_date, date_src;

		if($this.is("#start-date")) {
			start_date = parseDateFromString($this.val());
			end_date = parseDateFromString($other.val());
			date_src = start_date;
		}
		else {
			start_date = parseDateFromString($other.val());
			end_date = parseDateFromString($this.val());
			date_src = end_date;
		}

		if(start_date > end_date) {
			newDate = formatDate(date_src);
			$other.val(newDate);
		}
	});

});