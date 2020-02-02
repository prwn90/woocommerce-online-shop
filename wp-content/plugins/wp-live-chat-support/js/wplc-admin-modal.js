//Handles admin modals
var wplc_modal_init_callback = null;
var wplc_modal_confirm_callback = null;
var wplc_modal_cancel_callback = null;
var wplc_modal_confirm_closes_modal = true;

jQuery(function(){
	jQuery(function(){
		jQuery("body").on("click", ".wplc_modal_trigger_open", function(){
			var current_modal_id = jQuery(this).attr("modal_id");
			jQuery("#wplc_modal_" + current_modal_id).fadeIn();

			if(wplc_modal_init_callback !== null && typeof wplc_modal_init_callback === "function"){
				wplc_modal_init_callback();
			}
		});

		jQuery("body").on("click", ".wplc_modal_cancel", function(){
			var current_modal_id = jQuery(this).attr("modal_id");
			jQuery("#wplc_modal_" + current_modal_id).fadeOut();

			if(wplc_modal_cancel_callback !== null && typeof wplc_modal_cancel_callback === "function"){
				wplc_modal_cancel_callback();
			}
		});

		jQuery("body").on("click", ".wplc_modal_confirm", function(){
			var current_modal_id = jQuery(this).attr("modal_id");
			if(wplc_modal_confirm_closes_modal){
				jQuery("#wplc_modal_" + current_modal_id).fadeOut();
			}

			if(wplc_modal_confirm_callback !== null && typeof wplc_modal_confirm_callback === "function"){
				wplc_modal_confirm_callback();
			}
		});

	});
});

function wplc_modal_initialize(init_callback, confirm_callback, cancel_callback, confirm_closes_modal){
	if(typeof init_callback === "function"){
		wplc_modal_init_callback = init_callback;
	}

	if(typeof confirm_callback === "function"){
		wplc_modal_confirm_callback = confirm_callback;
	}

	if(typeof cancel_callback === "function"){
		wplc_modal_cancel_callback = cancel_callback;
	}

	wplc_modal_confirm_closes_modal = confirm_closes_modal;
}

function wplc_modal_remove_callback(){
	wplc_modal_confirm_callback = null;
	wplc_modal_cancel_callback = null;
}

(function($) {
	
	$(function(event) {
		if(!window.wdtEmojiBundle)
			return;

		$(document.body).on("click", function(event) {
			
			// If click event isn't on the emoji window, or the emoji open button, close the emoji window
			if($(event.target).closest(".wdt-emoji-picker, .wdt-emoji-popup").length == 0 && !(
				event.target.parentNode == null && $(event.target).hasClass("fa-smile")
				))
				wdtEmojiBundle.close();
			
		});
		
		// Close emoji window on scroll
		$(window).scroll(function(event) {
			wdtEmojiBundle.close();
		});
	});
	
})(jQuery);