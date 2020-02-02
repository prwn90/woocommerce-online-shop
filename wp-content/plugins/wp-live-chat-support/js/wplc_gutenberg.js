// Gutenberg functions
jQuery(function($){

	var sizes = ['sm','md','lg'];

	// Initate settings
	initiate_gutenberg_settings();

	$('#wplc_gutenberg_size').on('change', function() {
		$('.wplc_block').removeClass('sm').removeClass('md').removeClass('lg');
		$('.wplc_block').addClass(sizes[$(this).val()-1]);
	});

	$('#wplc_gutenberg_icon').on('change', function() {
		var fontawesome_class = 'fa ' + $(this).val().replace(/\./g, "");
		$('.wplc_block_icon i').removeClass().addClass(fontawesome_class);
	});

	$('.iconpicker-item').on('click', function() {
		var fontawesome_class = 'fa ' + $(this).attr('title').replace(/\./g, "");
		$('.wplc_block_icon i').removeClass().addClass(fontawesome_class);
	});

	$('#wplc_gutenberg_text').on('change', function() {
		var text = $(this).val();
		$('.wplc_block_text').html(text);
	});

	$('#activate_block').on('change', function() {
		if ($(this).is(':checked')) {
			$('.wplc_block').removeClass('disabled');
		} else {
			$('.wplc_block').addClass('disabled');
		}
	});

	$('#wplc_gutenberg_enable_icon').on('change', function() {
		if ($(this).is(':checked')) {
			$('.wplc_block_icon').show();
			$('#wplc_gutenberg_icon').removeAttr('disabled');
		} else {
			$('.wplc_block_icon').hide();
			$('#wplc_gutenberg_icon').attr('disabled', 'disabled');
		}
	});

	$('.wplc_code').on('click', function(){
		var $temp = $('<input>');
		$('body').append($temp);
		$temp.val($(this).text()).select();
		document.execCommand('copy');
		$temp.remove();
		$(this).closest('p').append('<p class="wplc_copied_message">'+ $(this).text() + ' copied to clipboard.</p>').find('.wplc_copied_message').fadeOut( 'slow' );
	});

	// Allow users to upload a logo image
    var media_uploader;
	$('#wplc_gutenberg_upload_logo').on( 'click', function(e) {
		
		e.preventDefault();

		if ( media_uploader ) {
			media_uploader.open();
			return;
		}

		media_uploader = wp.media.frames.file_frame = wp.media({
			title: 'Select a Logo',
			button: {
				text: 'Select Logo'
			},
			multiple: false
		});

		media_uploader.on( 'select', function() {
			attachment = media_uploader.state().get( 'selection' ).first().toJSON();
			$('#wplc_gutenberg_logo').val(attachment.url);
			$('.wplc_block_logo').css('background-image', 'url(' + attachment.url + ')');
		});

		media_uploader.open();

	});

	$('#wplc_gutenberg_remove_logo').on('click', function(e) {
		e.preventDefault();
		if (confirm("Are you sure you want to reset the logo to the default logo?")) {
			$('#wplc_gutenberg_logo').val($('#wplc_gutenberg_default_logo').val());
			$('.wplc_block_logo').css('background-image', 'url("' + $('#wplc_gutenberg_default_logo').val() + '")');
		}
		return;
	});

	function initiate_gutenberg_settings() {
		// Editor
		// var editor_js = ace.edit( 'wplc_custom_js_editor' );
		// editor_js.setTheme( 'ace/theme/monokai' );
		// editor_js.getSession().setMode( 'ace/mode/javascript' );
		// editor_js.setValue( $('#wplc_custom_js').val(), 1 );
		// editor_js.getSession().on("change", function () {
		// 	$('#wplc_custom_js').val( editor_js.getSession().getValue() );
		// });

		// var editor_css = ace.edit( 'wplc_custom_css_editor' );
		// editor_css.setTheme( 'ace/theme/monokai' );
		// editor_css.getSession().setMode( 'ace/mode/css' );
		// editor_css.setValue( $('#wplc_custom_css').val(), 1 );
		// editor_css.getSession().on("change", function () {
		// 	$('#wplc_custom_css').val( editor_css.getSession().getValue() );
		// });

		var editor_html = ace.edit( 'wplc_custom_html_editor' );
		editor_html.$blockScrolling = Infinity;
		editor_html.setTheme( 'ace/theme/monokai' );
		editor_html.getSession().setMode( 'ace/mode/html' );
		editor_html.setValue( $.trim($('#wplc_custom_html').val()), 1 );
		editor_html.getSession().on("change", function () {
			$('#wplc_custom_html').val( editor_html.getSession().getValue() );
		});

		if ($.trim(editor_html.getSession().getValue()) == '') {
			editor_html.setValue( $.trim(default_html), 1 );
		}
		
		$('#wplc_gutenberg_reset_html').on('click', function() {
			editor_html.setValue( $.trim(default_html), 1 );
		});

		$('#wplc_settings').on('submit', function(){
			if ($.trim(editor_html.getSession().getValue()) == '' ) {
				editor_html.setValue( default_html, 1 );
			}
		});

		// Predefined Templates
		wplc_gutenberg_templates( editor_html );

		// Initiate FontAwesome Icon Picker
		$('#wplc_gutenberg_icon').iconpicker({
			placement: 'bottomRight'
		});

		$('.wplc_block').removeClass('sm').removeClass('md').removeClass('lg');
		$('.wplc_block').addClass(sizes[$('#wplc_gutenberg_size').val()-1]);

		if ($('#wplc_gutenberg_enable_icon').is(':checked')) {
			$('.wplc_block_icon').show();
			$('#wplc_gutenberg_icon').removeAttr('disabled');
		} else {
			$('.wplc_block_icon').hide();
			$('#wplc_gutenberg_icon').attr('disabled', 'disabled');
		}

		if ($('#activate_block').is(':checked')) {
			$('.wplc_block').removeClass('disabled');
		} else {
			$('.wplc_block').addClass('disabled');
		}
	}

	function wplc_gutenberg_templates( editor ) {

		var templates = new Array();
		templates['template_default'] 			= '<!-- Default Template - Dark -->\n<div class="wplc_block">\n\t<span class="wplc_block_logo">{wplc_logo}</span>\n\t<span class="wplc_block_text">{wplc_text}</span>\n\t<span class="wplc_block_icon">{wplc_icon}</span>\n</div>';
		templates['template_default_tooltip'] 	= '<!-- Default - Tooltip -->\n<!-- Hover over the block to see the tooltip -->\n<div class="wplc_block">\n\t<span class="wplc_block_tooltip">Want to chat?</span>\n\t<span class="wplc_block_logo">{wplc_logo}</span>\n\t<span class="wplc_block_text">{wplc_text}</span>\n\t<span class="wplc_block_icon">{wplc_icon}</span>\n</div>';
		templates['template_default_light'] 	= '<!-- Default - Light Template -->\n<div class="wplc_block light">\n\t<span class="wplc_block_logo">{wplc_logo}</span>\n\t<span class="wplc_block_text">{wplc_text}</span>\n\t<span class="wplc_block_icon">{wplc_icon}</span>\n</div>';
		templates['template_tooltip'] 			= '<!-- Circle - Tooltip Template -->\n<!-- Hover over the block to see the tooltip -->\n<div class="wplc_block circle">\n\t<span class="wplc_block_tooltip">Chat with us!</span>\n\t<span class="wplc_block_logo">{wplc_logo}</span>\n</div>';
		templates['template_circle'] 			= '<!-- Circle Template -->\n<div class="wplc_block circle">\n\t<span class="wplc_block_logo">{wplc_logo}</span>\n</div>';
		templates['template_chat_bubble'] 		= '<!-- Chat Bubble Template -->\n<div class="wplc_block chat_bubble">\n\t<span class="wplc_block_logo">{wplc_logo}</span>\n\t<span class="wplc_block_text">{wplc_text}</span>\n</div>';
		templates['template_circle_rotate'] 	= '<!-- Circle - Rotating Template -->\n<!-- Hover over the icon to see it rotate -->\n<div class="wplc_block circle rotate">\n\t<span class="wplc_block_logo">{wplc_logo}</span>\n\t<span class="wplc_block_icon">{wplc_icon}</span>\n</div>';
		
		var current_template = wplc_gutenberg_filter_template( editor.getSession().getValue() );

		$(document).find('.wplc_gutenberg_preview').html(current_template);
		var sizes = ['sm','md','lg'];

		$('#wplc_custom_templates').on('change', function(){
			if ($(this).val() !== '0') {
				var template = wplc_gutenberg_filter_template( $.trim(templates[$(this).val()]) );
				template = template.replace('wplc_block', 'wplc_block ' + sizes[$(document).find('#wplc_gutenberg_size').val()-1]);
				editor.setValue( template, 1 );
				$(document).find('.wplc_gutenberg_preview').html(template);
			}
		});
	}

	function wplc_gutenberg_filter_template( template ) {
		var logo = $(document).find('#wplc_gutenberg_logo').val();
		var text = $(document).find('#wplc_gutenberg_text').val();
		var icon = $(document).find('#wplc_gutenberg_icon').val();
		var placeholder_codes = ['wplc_logo', 'wplc_text', 'wplc_icon'],
	    placeholder_values = ['<span class="wplc_block_logo" style="background-image: url(\'' + logo + '\');"></span>', text, '<i class="fa ' + icon + '"></i>'];
	    
	    for( var i = 0; i < placeholder_codes.length; i++ ) {
		    template = template.replace(new RegExp('{' + placeholder_codes[i] + '}', 'gi'), placeholder_values[i]);
		}

		return template;
	}

});