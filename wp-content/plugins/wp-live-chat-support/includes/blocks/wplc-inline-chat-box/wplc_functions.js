jQuery(function($){

	var chat_box_button = '<div class="wplc_gutenberg_button active" style="background-color: #' + wplc_styles.color_1 + '; background-image: url(' + wplc_images.close_icon + ');"></div>';
	var chatbox_preview_img = '<img class="wplc_gutenberg_avatar_img" style="max-width:inherit;" id="agent_grav_' + wplc_agent_info.id + '" title="' + wplc_agent_info.name + '" src="https://www.gravatar.com/avatar/' + wplc_agent_info.email + '?s=60&d=mm" />';
	var chatbox_preview_overlay_image = '<div class="wplc_gutenberg_overlay_image" style="background-image: url(\'https://www.gravatar.com/avatar/' + wplc_agent_info.email + '?s=60&d=mm\');"></div>';
	var chat_box_content = chat_box_button + '<div class="wplc_gutenberg_chat_box"><div class="wplc_gutenberg_header" style="background-color: #' + wplc_styles.color_1 + ' !important;"><span class="wplc_gutenberg_avatar">' + chatbox_preview_img + chatbox_preview_overlay_image + '</span><span class="wplc_gutenberg_name">' + wplc_agent_info.name + '</span><span class="wplc_gutenberg_chevron dashicons dashicons-arrow-up-alt2"></span></div><div class="wplc_gutenberg_body" style="background-image: url(' + wplc_images.background_image + ');"></div><div class="wplc_gutenberg_text_box"><span class="wplc_gutenberg_typing">'+wplc_settings.wplc_typing+'</span></div></div>';
	
	// Update select fields on page load
	$('#wplc-inline-chat-box .wplc_selected_theme').each(function(){
		var id = $(this).html();
		$(this).closest('#wplc-inline-chat-box').find('.wplc_select_theme').val(id);
	});

	$('.wplc_gutenberg_preview').html(chat_box_content);

	$('.wplc_gutenberg_button').css('transform', 'rotate(90deg)');

	$(document).on('click', '.wplc_gutenberg_button', function(){
		$(this).toggleClass('active');
		if ($(this).hasClass('active')) {
			wplc_gutenberg_close_chat_box(this, wplc_images.open_icon )
		} else {
			
			wplc_gutenberg_open_chat_box(this, wplc_images.close_icon)
		}
	});
});

function wplc_gutenberg_open_chat_box(button, icon) {
	jQuery(button).css('background-image', 'url(' + icon + ')');
	jQuery(button).css('transform', 'rotate(90deg)');
	jQuery(button).closest('.wplc_gutenberg_preview').find('.wplc_gutenberg_chat_box').show();
}

function wplc_gutenberg_close_chat_box(button, icon) {
	jQuery(button).css('background-image', 'url(' + icon + ')');
	jQuery(button).css('transform', 'rotate(0deg)');
	jQuery(button).closest('.wplc_gutenberg_preview').find('.wplc_gutenberg_chat_box').hide();
}