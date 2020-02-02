jQuery(function(){
	jQuery(document).on("tcx_connect", function(e) {
		setTimeout(function(){
			var wplc_offline_prompt_title = jQuery("#wplc_offline_mode_prompt").text();
			var wplc_offline_prompt_body = jQuery("#wplc_offline_mode_prompt_container").html();
			jQuery("#wplc_offline_mode_prompt_container").hide();
			jQuery(".nifty_admin_overlay").fadeIn();
			niftyShowDialog(wplc_offline_prompt_title, "", "", function(){}, function(){});

			jQuery(".nifty_admin_chat_prompt_message").html(wplc_offline_prompt_body);
			jQuery('.nifty_admin_chat_prompt_actions').hide();

			jQuery('.nifty_admin_chat_prompt').css("width", "50%");
		}, 1500);
	})
});