/* BLOCK: WP Live Chat by 3CX Chat Box */

( function() {

	var __ = wp.i18n.__;
	var el = wp.element.createElement;
	var children = wp.blocks.children;
	var registerBlockType = wp.blocks.registerBlockType;

	String.prototype.stripSlashes = function(){
	    return this.replace(/\\(.)/mg, "$1");
	}

	var wplc_logo = ( wplc_settings.wplc_logo ) ? wplc_settings.wplc_logo : wplc_baseurl + 'images/wplc_loading.png';
	var wplc_text = ( wplc_settings.wplc_text ) ? wplc_settings.wplc_text : 'Live Chat';
	var wplc_icon = ( wplc_settings.wplc_icon ) ? wplc_settings.wplc_icon : 'far fa-comment-dots';
	var classes = ( wplc_settings.wplc_enabled == 0 ) ? ' disabled' : '';
	var icon_classes = ( wplc_settings.wplc_icon_enabled == 0 ) ? ' disabled' : '';
	var size_class = ' md';
	var default_template = '<!-- Default HTML --><div class="wplc_block"><span class="wplc_block_logo">{wplc_logo}</span><span class="wplc_block_text">{wplc_text}</span><span class="wplc_block_icon">{wplc_icon}</span></div>';
	var wplc_template = ( wplc_settings.wplc_custom_html ) ? wplc_settings.wplc_custom_html : default_template;
	wplc_template = wplc_template.stripSlashes();

	if ( wplc_settings.wplc_size == 1 ) {
		size_class = ' sm';
	} else if ( wplc_settings.wplc_size == 2 ) {
		size_class = ' md';
	} else if ( wplc_settings.wplc_size == 3 ) {
		size_class = ' lg';
	}
	
	var wplc_styles = {
		backgroundImage: 'url(' + wplc_logo + ')'
	}
	
	var placeholder_codes = ['wplc_logo', 'wplc_text', 'wplc_icon'],
    placeholder_values = ['<span class="wplc_block_logo" style="background-image: url(\'' + wplc_logo + '\');"></span>', wplc_text, '<i class="fa ' + wplc_icon + '"></i>'];

	for( var i = 0; i < placeholder_codes.length; i++ ) {
	    wplc_template = wplc_template.replace(new RegExp('{' + placeholder_codes[i] + '}', 'gi'), placeholder_values[i]);
	}

	registerBlockType( 'wp-live-chat-support/wplc-chat-box', {
		title: __( 'WP Live Chat Trigger', 'WPLC' ),
		icon: 'format-chat',
		category: 'common',

		edit: function( props ) {
			output_wplc_block_logo = el(
				'span',
				{ className: 'wplc_block_logo',
				  style: wplc_styles
				}
			);
			output_wplc_block_text = el(
				'span',
				{ className: 'wplc_block_text' },
				wplc_text
			);

			icon = el(
				'i',
				{ className: 'far fa-comment-dots' }
			);

			output_wplc_block_icon = el(
				'span',
				{ className: 'wplc_block_icon' + icon_classes },
				icon
			);

			output = el(
				'span',
				{ className: 'wplc_block' + classes + size_class },
				output_wplc_block_logo,
				output_wplc_block_text,
				output_wplc_block_icon
			);
			

			return el(
				'div',
				{ 	id: 'wplc-chat-box',
					className: classes,
					dangerouslySetInnerHTML: { __html: wplc_template }
				},
			);
		},

		save: function( props ) {
			output_wplc_block_logo = el(
				'span',
				{ className: 'wplc_block_logo',
				  style: wplc_styles 
				}
			);
			output_wplc_block_text = el(
				'span',
				{ className: 'wplc_block_text' },
				wplc_text
			);

			icon = el(
				'i',
				{ className: 'far fa-comment-dots' }
			);

			output_wplc_block_icon = el(
				'span',
				{ className: 'wplc_block_icon' + icon_classes },
				icon
			);

			output = el(
				'span',
				{ className: 'wplc_block' + classes + size_class },
				output_wplc_block_logo,
				output_wplc_block_text,
				output_wplc_block_icon
			);
			
			return el(
				'div',
				{ 	id: 'wplc-chat-box',
					className: classes,
					dangerouslySetInnerHTML: { __html: wplc_template }
				}
			);
		},
	} );
})();
