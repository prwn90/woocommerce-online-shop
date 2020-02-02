jQuery( document ).ready( function($) {

	// dom variables
	var mainForm = $( '#mainform' );
	var saveButton = $( '.woocommerce-save-button' );
	var extraFields = $( '.wc-smart-cod-group' );
	var select2Elems = $( '.wc-smartcod-categories, .wc-smartcod-products' );
	var productSearch = $( '.wc-smartcod-products.wc-smart-cod-group' );
	var restrictionFields = $( '.wc-smart-cod-restriction' );
	var percentageFields = $( '.wc-smart-cod-percentage' );
	var body = $( 'body' );
	var restrictionSettingsInput = $( '#woocommerce_cod_restriction_settings' );
	var feeSettingsInput = $( '#woocommerce_cod_fee_settings' );
	var messageSwitcher = $( '.wsc-message-switcher' );
	var messageAreas = $( '.wsc-message' );
	var restrictionSettings = {};
	var feeSettings = {};

	if( smart_cod_variables.hasOwnProperty( 'restriction_settings' ) ) {
		restrictionSettings = smart_cod_variables.restriction_settings;
	}

	if( smart_cod_variables.hasOwnProperty( 'fee_settings' ) ) {
		feeSettings = smart_cod_variables.fee_settings;
	}

	var smartCOD = {

		init: function() {

			restrictionSettingsInput.parents( 'tr' ).addClass( 'hidden' );
			feeSettingsInput.parents( 'tr' ).addClass( 'hidden' );

			restrictionFields.each( function( index, element ) {
				smartCOD.enableConditional( $( this ) );
			})

			percentageFields.each( function( index, element ) {
				smartCOD.enablePercentage( $( this ) );
			})

			body.on( 'change', '.wc-smart-cod-percantage-switcher', smartCOD.switchFee );
			body.on( 'click', '.wc-smart-cod-mode', smartCOD.switchMode );

			saveButton.click( function(e) {
				if( ! smartCOD.validateFields( extraFields ) ) {
					e.preventDefault();
				}
			})

			select2Elems.each( function( index, element ) {
				smartCOD.initSelect2( $( this ) );
			})

			smartCOD.fixSelect2Bug( productSearch );

			messageSwitcher.on( 'change', smartCOD.changeMessageVisibility );

		},

		changeMessageVisibility: function(e) {

			var $this = $( this ),
				value = $( this ).val();

			messageAreas.addClass( 'hidden' );
			$( '.wsc-message[data-restriction="' + value + '"]' ).removeClass( 'hidden' );

		},

		isObjectEmpty: function ( obj ) {

			var name;
			for ( name in obj ) {
				return false;
			}

			return true;

		},

		switchMode: function(e) {

			e.preventDefault();

			var $this = $( this ),
				currentMode = $this.data( 'mode' ),
				newMode = currentMode === 'enable' ? 'disable' : 'enable',
				node = $this.parents( 'tr' ).find( '.wc-smart-cod-restriction' ),
				dataName = node.data( 'name' );

			if( currentMode !== 'enable' && currentMode !== 'disable' ) {
				return;
			}

			restrictionSettings[ dataName ] = newMode === 'enable' ? 1 : 0;
			restrictionSettingsInput.val( JSON.stringify( restrictionSettings ) );
			$this.html( newMode );
			$this.data( 'mode', newMode );

		},

		switchFee: function(e) {

			var $this = $( this ),
				input = $this.prev(),
				val = $this.val(),
				name = input.attr( 'name' );

			feeSettings[ name.slice( 16 ) ] = val;
			feeSettingsInput.val( JSON.stringify( feeSettings ) );
		},

		enablePercentage: function( element ) {

			var fieldset = element.parents( 'fieldset' ),
				select = $( '<select class="wc-smart-cod-percantage-switcher"><option value="fixed">Fixed</option><option value="percentage">%</option></select>' ),
				value = '0',
				name = element.attr( 'name' ).slice( 16 );

			if( feeSettings.hasOwnProperty( name ) ) {
				value = feeSettings[ name ];
			}
			else {
				feeSettings[ name ] = value = 'fixed';
			}

			select.val( value );
			fieldset.append( select );
		},

		enableConditional: function( element ) {

			var tr = element.parents( 'tr' ),
				th = tr.find( 'th' ),
				label = th.find( 'label' );

			if( label.length > 0 ) {
				var originalValue = label.contents().get( 0 ).nodeValue;
				var splitLabel = originalValue.trim().split( ' ' );
				if( splitLabel[0] ) {
					var needle = splitLabel[0].toLowerCase();
					switch ( needle ) {
						case 'enable':
						case 'disable': {
							var inputName = element.attr( 'name' );
							var dataName = element.data( 'name' );
							label.contents().get( 0 ).nodeValue = label.text().replace( originalValue, '' );
							splitLabel.shift();
							label.prepend( '<a class="wc-smart-cod-mode" href="#" data-mode="' + needle + '" data-input-name="' + inputName + '">' + needle + '</a> ' + splitLabel.join( ' ' ) );
							if( ! restrictionSettings.hasOwnProperty( dataName ) ) {
								restrictionSettings[ dataName ] = needle === 'enable' ? 1 : 0;
							}
							break;
						}
					}
				}
			}

		},

		fixSelect2Bug: function( element ) {

			var select2Instance = element.data( 'select2' );
			if( select2Instance ) {
				select2Instance.on('results:message', function(params){
					this.dropdown._resizeDropdown();
					this.dropdown._positionDropdown();
				});
			}

		},

		initSelect2: function( element ) {

			var action = element.data( 'action' ) || 'woocommerce_json_search_categories',
				security;

			if( action === 'wcsmartcod_json_search_categories' ) {
				security = smart_cod_variables.enhanced_select.search_categories_nonce;
			}
			else if( action === 'woocommerce_json_search_products_and_variations' ) {
				security = smart_cod_variables.enhanced_select.search_products_nonce;
			}

			var select2_args = {
				allowClear:  element.data( 'allow_clear' ) ? true : false,
				placeholder: element.data( 'placeholder' ),
				minimumInputLength: element.data( 'minimum_input_length' ) ? element.data( 'minimum_input_length' ) : '3',
				escapeMarkup: function( m ) {
					return m;
				},
				ajax: {
					url:         smart_cod_variables.enhanced_select.ajax_url,
					dataType:    'json',
					delay:       250,
					data:        function( params ) {
						return {
							term:     params.term,
							action:   action,
							security: security,
							exclude:  element.data( 'exclude' ),
							include:  element.data( 'include' ),
							limit:    element.data( 'limit' )
						};
					},
					processResults: function( data ) {
						var terms = [];
						if ( data ) {
							$.each( data, function( id, text ) {
								terms.push( { id: id, text: text } );
							});
						}
						return {
							results: terms
						};
					},
					cache: true
				}
			};

			select2_args = $.extend( select2_args, smartCOD.getEnhancedSelectFormatString() );
			element.select2( select2_args ).addClass( 'enhanced' );

		},

		validateFields: function( extraFields ) {

			mainForm.find( '.field-error' ).remove();

			var isValid = true;
			extraFields.each( function( index, element ) {

				if( element.id !== 'woocommerce_cod_include_restrict_postals'
				&& element.id !== 'woocommerce_cod_restrict_postals' )
					return true;

				var $this = $( this );
				var value = $this.val() ? $this.val().replace(/\s/g,'') : $this.val();

				// strip whitespace
				$this.val( value );

				// if( value && element.id !== 'woocommerce_cod_restrict_postals' ) {
				// 	// we need amounts to be numbers
				// 	if( isNaN( value ) ) {
				// 		isValid = false;
				// 		$this.parent().append( '<p class="field-error notice notice-error">' + smart_cod_variables.nan + '</p>' );
				// 	}
				//
				// }

			});

			return isValid;
		},

		getEnhancedSelectFormatString: function() {
			return {
				'language': {
					errorLoading: function() {
						// Workaround for https://github.com/select2/select2/issues/4355 instead of i18n_ajax_error.
						return smart_cod_variables.enhanced_select.i18n_searching;
					},
					inputTooLong: function( args ) {
						var overChars = args.input.length - args.maximum;

						if ( 1 === overChars ) {
							return smart_cod_variables.enhanced_select.i18n_input_too_long_1;
						}

						return smart_cod_variables.enhanced_select.i18n_input_too_long_n.replace( '%qty%', overChars );
					},
					inputTooShort: function( args ) {
						var remainingChars = args.minimum - args.input.length;

						if ( 1 === remainingChars ) {
							return smart_cod_variables.enhanced_select.i18n_input_too_short_1;
						}

						return smart_cod_variables.enhanced_select.i18n_input_too_short_n.replace( '%qty%', remainingChars );
					},
					loadingMore: function() {
						return smart_cod_variables.enhanced_select.i18n_load_more;
					},
					maximumSelected: function( args ) {
						if ( args.maximum === 1 ) {
							return smart_cod_variables.enhanced_select.i18n_selection_too_long_1;
						}

						return smart_cod_variables.enhanced_select.i18n_selection_too_long_n.replace( '%qty%', args.maximum );
					},
					noResults: function() {
						return smart_cod_variables.enhanced_select.i18n_no_matches;
					},
					searching: function() {
						return smart_cod_variables.enhanced_select.i18n_searching;
					}
				}
			};
		}

	}

	smartCOD.init();
})
