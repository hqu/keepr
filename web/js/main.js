
(function($) {
	$.timeSinceTweet = function(time) {
		var date = new Date(time);
		var diff = ((new Date()).getTime() - date.getTime()) / 1000;
		var day_diff = Math.floor(diff / 86400);
		
		if (day_diff < 0 || day_diff >= 31 || isNaN(day_diff)) {
			return "View tweet";
		}
		
		if(day_diff == 0) {
			if(diff < 60) {
				return Math.ceil(diff) + " seconds ago";
			}
			else if(diff < 120) {
				return "1 minute ago";
			}
			else if(diff < 3600) {
				return Math.floor( diff / 60 ) + " minutes ago";
			}
			else if(diff < 7200) {
				return "1 hour ago";
			}
			else if(diff < 86400) {
				return Math.floor( diff / 3600 ) + " hours ago";
			}
		}
		
		if(day_diff == 1) {
			return "Yesterday";
		}
		else if(day_diff < 7) {
			return day_diff + " days ago";
		}
		else if(day_diff < 31) {
			return Math.ceil( day_diff / 7 ) + " weeks ago";
		}
		else {
			return "View Tweet";
		}	
	}
})(jQuery);