/* BLOCK: WP Live Chat by 3CX - Inline Chat Box */

( function() {
	var __ = wp.i18n.__;
	var el = wp.element.createElement;
	var Editable = wp.blocks.Editable;
	var children = wp.blocks.children;
	var registerBlockType = wp.blocks.registerBlockType;
	
	var chat_preview_loader = '<span><small><em>Your chat box will show up here when page is viewed</em></small></span>';

	registerBlockType( 'wp-live-chat-support/wplc-inline-chat-box', {
		title: __( 'WP Live Chat Box', 'WPLC' ),
		icon: 'format-chat',
		category: 'common',

		edit: function( props ) {

			var content = typeof props.attributes.content !== 'undefined' ? props.attributes.content : 'normal' ;
			var focus = props.focus;

			function onChangeContent( newContent ) {
				props.setAttributes( { content: newContent } );
			}

			/*jQuery(document).on('change', '.wplc_select_theme', function(){
				var id = $(this).val();
				onChangeContent( id );
			});*/
			
			theme_classes = 'wplc_' + content ;

			element = el(
				'h4',
				{  },
				'Live Chat Box'
			);

			/*select_text = el(
				'span',
				{  },
				'Select a style: '
			);*/

			icon = el(
				'span',
				{ className: "wplc_gutenberg_icon dashicons dashicons-format-chat" }
			);

			chat_box_preview = el(
				'div',
				{ 
					className: 'wplc_gutenberg_preview', 
					dangerouslySetInnerHTML: { __html: chat_preview_loader }
				}
			);

			/*var option_1 = el(
				'option',
				{ value: 'normal', selected: 'selected' },
				'Normal'
			);
			var option_2 = el(
				'option',
				{ value: 'center' },
				'Center'
			);
			var option_3 = el(
				'option',
				{ value: 'wide' },
				'Wide'
			);

			var select = el(
				'select',
				{ className: 'wplc_select_theme' },
				option_1,
				option_2,
				option_3
			);*/

			var style = {
				display: 'none'
			}

			/*var editable_content = el(
				Editable,
				{ 	
					tagName: 'p',
					className: 'wplc_selected_theme',
					value: content,
					onChange: onChangeContent,
					focus: focus,
					onFocus: props.setFocus,
					style: style
				},
			);*/
			
			return el(
				'div',
				{ 	id: 'wplc-inline-chat-box',
					className: theme_classes
				},
				element,
				chat_box_preview
			);
		},

		save: function( props ) {
			var style_class = props.attributes.content;
			var content = '[wplc_live_chat style="' + style_class + '"]';

			return el(
				'div',
				{ 	id: 'wplc-inline-chat-box',
					dangerouslySetInnerHTML: { __html: content }
				}
			);
		},
	} );
})();
