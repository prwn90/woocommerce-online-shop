jQuery(function() {
	
	function storeActiveTab(event, ui)
	{
		if(!window.sessionStorage)
			return;
		
		sessionStorage.setItem(
			"wplc-tabs-index",
			jQuery(event.target).tabs("option", "active")
		);
	}
	
	function recallActiveTab()
	{
		if(!window.sessionStorage)
			return 0;
		
		var active = sessionStorage.getItem("wplc-tabs-index");
		
		if(isNaN(active))
			active = 0;
		
		return active;
	}

    jQuery('#wplc_agent_select').on('change', function(){
        jQuery('#wplc_add_agent').show();
    });

    jQuery('.wplc_check_url').on('change', function(){
        jQuery('.wplc_check_url').removeClass('invalid_url');
        jQuery('.invalid_url_text').remove();
        jQuery('input[name=wplc_save_settings]').removeAttr('disabled');

        if(!wplc_valid_url_checker(jQuery(this).val()) && jQuery(this).val().trim() !== ""){
            jQuery(this).addClass('invalid_url');
            jQuery('<span class="invalid_url_text">Invalid URL</span>').insertAfter(this);

            jQuery('input[name=wplc_save_settings]').attr('disabled', 'disabled');
        }
    });

    function wplc_valid_url_checker(str) {
      var pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
        '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|'+ // domain name
        '((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
        '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
        '(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
        '(\\#[-a-z\\d_]*)?$','i'); // fragment locator
      return !!pattern.test(str);
    }
	
    
	jQuery("#wplc_tabs").tabs({
		create: function(event, ui) 
		{ 
			jQuery("#wplc_settings_page_loader").remove(); 
			jQuery(".wrap").fadeIn(); 
			jQuery(".wplc_settings_save_notice").fadeIn(); 
		},
		active: recallActiveTab(),
		activate: storeActiveTab
	}).addClass( "ui-tabs-vertical ui-helper-clearfix" );
	
   jQuery( "#wplc_tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );



    jQuery(".wplc_hide_input").hide();
        
    jQuery("#wplc_animation_1").click(function() {
        jQuery("#wplc_rb_animation_1").attr('checked', true);
        jQuery("#wplc_rb_animation_2").attr('checked', false);
        jQuery("#wplc_rb_animation_3").attr('checked', false);
        jQuery("#wplc_rb_animation_4").attr('checked', false);
        jQuery("#wplc_animation_1").addClass("wplc_animation_active");
        jQuery("#wplc_animation_2").removeClass("wplc_animation_active");
        jQuery("#wplc_animation_3").removeClass("wplc_animation_active");
        jQuery("#wplc_animation_4").removeClass("wplc_animation_active");
    });
    
    jQuery("#wplc_animation_2").click(function() {
        jQuery("#wplc_rb_animation_1").attr('checked', false);
        jQuery("#wplc_rb_animation_2").attr('checked', true);
        jQuery("#wplc_rb_animation_3").attr('checked', false);
        jQuery("#wplc_rb_animation_4").attr('checked', false);
        jQuery("#wplc_animation_1").removeClass("wplc_animation_active");
        jQuery("#wplc_animation_2").addClass("wplc_animation_active");
        jQuery("#wplc_animation_3").removeClass("wplc_animation_active");
        jQuery("#wplc_animation_4").removeClass("wplc_animation_active");
    });
    
    jQuery("#wplc_animation_3").click(function() {
        jQuery("#wplc_rb_animation_1").attr('checked', false);
        jQuery("#wplc_rb_animation_2").attr('checked', false);
        jQuery("#wplc_rb_animation_3").attr('checked', true);
        jQuery("#wplc_rb_animation_4").attr('checked', false);
        jQuery("#wplc_animation_1").removeClass("wplc_animation_active");
        jQuery("#wplc_animation_2").removeClass("wplc_animation_active");
        jQuery("#wplc_animation_3").addClass("wplc_animation_active");
        jQuery("#wplc_animation_4").removeClass("wplc_animation_active");
    });
    
    jQuery("#wplc_animation_4").click(function() {
        jQuery("#wplc_rb_animation_1").attr('checked', false);
        jQuery("#wplc_rb_animation_2").attr('checked', false);
        jQuery("#wplc_rb_animation_3").attr('checked', false);
        jQuery("#wplc_rb_animation_4").attr('checked', true);
        jQuery("#wplc_animation_1").removeClass("wplc_animation_active");
        jQuery("#wplc_animation_2").removeClass("wplc_animation_active");
        jQuery("#wplc_animation_3").removeClass("wplc_animation_active");
        jQuery("#wplc_animation_4").addClass("wplc_animation_active");
    });
    
    
    /* Themes */
    jQuery("#wplc_newtheme_1").click(function() {
        jQuery("#wplc_new_rb_theme_1").attr('checked', true);
        jQuery("#wplc_new_rb_theme_2").attr('checked', false);
        jQuery("#wplc_newtheme_1").addClass("wplc_theme_active");
        jQuery("#wplc_newtheme_2").removeClass("wplc_theme_active");
    });
    
    jQuery("#wplc_newtheme_2").click(function() {
        jQuery("#wplc_new_rb_theme_1").attr('checked', false);
        jQuery("#wplc_new_rb_theme_2").attr('checked', true);
        jQuery("#wplc_newtheme_1").removeClass("wplc_theme_active");
        jQuery("#wplc_newtheme_2").addClass("wplc_theme_active");
    });


    /* Colour Schemes */
    
    jQuery("#wplc_theme_default").click(function() {
        jQuery("#wplc_rb_theme_default").attr('checked', true);
        jQuery("#wplc_rb_theme_1").attr('checked', false);
        jQuery("#wplc_rb_theme_2").attr('checked', false);
        jQuery("#wplc_rb_theme_3").attr('checked', false);
        jQuery("#wplc_rb_theme_4").attr('checked', false);
        jQuery("#wplc_rb_theme_5").attr('checked', false);
        jQuery("#wplc_rb_theme_6").attr('checked', false);
        jQuery("#wplc_theme_default").addClass("wplc_theme_active");
        jQuery("#wplc_theme_1").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_2").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_3").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_4").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_5").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_6").removeClass("wplc_theme_active");

        jQuery('.wplc_custom_pall_rows').hide();
    });
    
    jQuery("#wplc_theme_1").click(function() {
        jQuery("#wplc_rb_theme_default").attr('checked', false);
        jQuery("#wplc_rb_theme_1").attr('checked', true);
        jQuery("#wplc_rb_theme_2").attr('checked', false);
        jQuery("#wplc_rb_theme_3").attr('checked', false);
        jQuery("#wplc_rb_theme_4").attr('checked', false);
        jQuery("#wplc_rb_theme_5").attr('checked', false);
        jQuery("#wplc_rb_theme_6").attr('checked', false);
        jQuery("#wplc_theme_default").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_1").addClass("wplc_theme_active");
        jQuery("#wplc_theme_2").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_3").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_4").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_5").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_6").removeClass("wplc_theme_active");

        jQuery('.wplc_custom_pall_rows').hide();
    });
    
    jQuery("#wplc_theme_2").click(function() {
        jQuery("#wplc_rb_theme_default").attr('checked', false);
        jQuery("#wplc_rb_theme_1").attr('checked', false);
        jQuery("#wplc_rb_theme_2").attr('checked', true);
        jQuery("#wplc_rb_theme_3").attr('checked', false);
        jQuery("#wplc_rb_theme_4").attr('checked', false);
        jQuery("#wplc_rb_theme_5").attr('checked', false);
        jQuery("#wplc_rb_theme_6").attr('checked', false);
        jQuery("#wplc_theme_default").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_1").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_2").addClass("wplc_theme_active");
        jQuery("#wplc_theme_3").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_4").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_5").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_6").removeClass("wplc_theme_active");

        jQuery('.wplc_custom_pall_rows').hide();
    });
    
    jQuery("#wplc_theme_3").click(function() {
        jQuery("#wplc_rb_theme_default").attr('checked', false);
        jQuery("#wplc_rb_theme_1").attr('checked', false);
        jQuery("#wplc_rb_theme_2").attr('checked', false);
        jQuery("#wplc_rb_theme_3").attr('checked', true);
        jQuery("#wplc_rb_theme_4").attr('checked', false);
        jQuery("#wplc_rb_theme_5").attr('checked', false);
        jQuery("#wplc_rb_theme_6").attr('checked', false);
        jQuery("#wplc_theme_default").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_1").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_2").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_3").addClass("wplc_theme_active");
        jQuery("#wplc_theme_4").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_5").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_6").removeClass("wplc_theme_active");

        jQuery('.wplc_custom_pall_rows').hide();
    });
    
    jQuery("#wplc_theme_4").click(function() {
        jQuery("#wplc_rb_theme_default").attr('checked', false);
        jQuery("#wplc_rb_theme_1").attr('checked', false);
        jQuery("#wplc_rb_theme_2").attr('checked', false);
        jQuery("#wplc_rb_theme_3").attr('checked', false);
        jQuery("#wplc_rb_theme_4").attr('checked', true);
        jQuery("#wplc_rb_theme_5").attr('checked', false);
        jQuery("#wplc_rb_theme_6").attr('checked', false);
        jQuery("#wplc_theme_default").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_1").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_2").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_3").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_4").addClass("wplc_theme_active");
        jQuery("#wplc_theme_5").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_6").removeClass("wplc_theme_active");

        jQuery('.wplc_custom_pall_rows').hide();
    });
    
    jQuery("#wplc_theme_5").click(function() {
        jQuery("#wplc_rb_theme_default").attr('checked', false);
        jQuery("#wplc_rb_theme_1").attr('checked', false);
        jQuery("#wplc_rb_theme_2").attr('checked', false);
        jQuery("#wplc_rb_theme_3").attr('checked', false);
        jQuery("#wplc_rb_theme_4").attr('checked', false);
        jQuery("#wplc_rb_theme_5").attr('checked', true);
        jQuery("#wplc_rb_theme_6").attr('checked', false);
        jQuery("#wplc_theme_default").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_1").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_2").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_3").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_4").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_5").addClass("wplc_theme_active");
        jQuery("#wplc_theme_6").removeClass("wplc_theme_active");

        jQuery('.wplc_custom_pall_rows').hide();
    });
    
    jQuery("#wplc_theme_6").click(function() {
        jQuery("#wplc_rb_theme_default").attr('checked', false);
        jQuery("#wplc_rb_theme_1").attr('checked', false);
        jQuery("#wplc_rb_theme_2").attr('checked', false);
        jQuery("#wplc_rb_theme_3").attr('checked', false);
        jQuery("#wplc_rb_theme_4").attr('checked', false);
        jQuery("#wplc_rb_theme_5").attr('checked', false);
        jQuery("#wplc_rb_theme_6").attr('checked', true);
        jQuery("#wplc_theme_default").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_1").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_2").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_3").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_4").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_5").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_6").addClass("wplc_theme_active");

        jQuery('.wplc_custom_pall_rows').show();
    });
    
   
   
   
   
    jQuery(function () {
        jQuery(".wplc_settings_tooltip").tooltip({
            position: {
                my: "left+15 center", 
                at: "right center",
               
            },
            template: '<div class="tooltip wplc_tooltip_control"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
            onShow: function(){
                    var $trigger = this.getTrigger();
                    var offset = $trigger.offset();
                    this.getTip().css({
                        'top' : offset.top,
                        'left' : offset.left
                    });
            }
        });
    });

});
