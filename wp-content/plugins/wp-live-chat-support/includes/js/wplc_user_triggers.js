jQuery(function(){
	var wplc_trigger_done = false;
	jQuery(window).load(function(){
		if(typeof wplc_trigger_data !== 'undefined'){
			//Vars are here - Trigger can be handled
			if(typeof wplc_trigger_data["type"] !== 'undefined'){
				var wplc_trigger_type = parseInt(wplc_trigger_data["type"]);
				switch(wplc_trigger_type){
					case 0:
						//Don't do anything - this is handled by PHP
						break;
					case 1:
						//Time Trigger
						wplc_time_trigger();
						break;
					case 2:
						//Scroll Trigger
						
						jQuery(document).scroll(wplc_scroll_trigger);
						break;
					case 3:
						//Page Leave Trigger
						jQuery('body').on("mouseleave", wplc_page_leave_trigger);
				}
			}
		}

		function wplc_time_trigger(){
			if(typeof wplc_trigger_data !== 'undefined'){
				if(wplc_trigger_data["secs"] !== null && typeof wplc_trigger_data["secs"] !== 'undefined' && wplc_trigger_data["secs"] !== ""){
					var wplc_trigger_seconds = parseInt(wplc_trigger_data["secs"]);
					var wplc_add_seconds = 0; 
					if(wplc_misc_strings.wplc_delay){
						//Chat delay present
						 wplc_add_seconds = parseInt(wplc_misc_strings.wplc_delay);
					}
					setTimeout(function(){
						jQuery("#wplc_hovercard").fadeIn();
						jQuery("#wp-live-chat-header").addClass("active");
					}, (wplc_trigger_seconds * 1000) + wplc_add_seconds);
				}
			}
		}

		function wplc_scroll_trigger(){
			if(!wplc_trigger_done){
				var domHeight = jQuery(document).height();
				var diff = parseInt(domHeight * 0.3);
				var actualHeight = domHeight - diff;
				var pos = jQuery(document).scrollTop();

				if(typeof wplc_trigger_data !== 'undefined'){
					if(wplc_trigger_data["perc"] !== null && typeof wplc_trigger_data["perc"] !== 'undefined' && wplc_trigger_data["perc"] !== ""){
						var tmpPerc = parseInt(wplc_trigger_data["perc"]) / 100;
						var hitPos = parseInt(actualHeight * tmpPerc);
						if(pos > hitPos){
							jQuery("#wplc_hovercard").fadeIn();
							jQuery("#wp-live-chat-header").addClass("active");
							wplc_trigger_done = true;
						}
					}
				}
			}
		}

		function wplc_page_leave_trigger(){
			if(!wplc_trigger_done){
				jQuery("#wplc_hovercard").fadeIn('fast');
				jQuery("#wp-live-chat-header").addClass("active");
				wplc_trigger_done = true;
			}
		}

	});
});