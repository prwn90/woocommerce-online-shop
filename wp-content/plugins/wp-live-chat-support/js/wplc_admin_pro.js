
jQuery(function() {
    
    jQuery("#wplc_localization_warning").hide()
    if(jQuery("#wplc_using_localization_plugin").is(":checked")){
        jQuery(".wplc_localization_strings").hide();
        jQuery("#wplc_localization_warning").show()
    }
    jQuery('#wplc_using_localization_plugin').click(
    function(e){
        if (jQuery(this).is(':checked')){
            jQuery(".wplc_localization_strings").hide();
            jQuery("#wplc_localization_warning").show();
        } else {
            jQuery(".wplc_localization_strings").show();
            jQuery("#wplc_localization_warning").hide();
        }
    });
    
    if(typeof tooltip != 'undefined'){
        if(typeof jQuery(".wplc_settings_tooltip") != 'undefined'){
            jQuery(function () {
                jQuery(".wplc_settings_tooltip").tooltip({
                    position: {my: "left+15 center", at: "right center"}
                });
            });
        }                
    }
        
    if(typeof jQuery("#wplc_visitor_accordion") != 'undefined'){
        jQuery("#wplc_visitor_accordion").accordion({heightStyle: "content"});
    }        
        
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
    
    jQuery("#wplc_theme_1").click(function() {
        jQuery("#wplc_rb_theme_1").attr('checked', true);
        jQuery("#wplc_rb_theme_2").attr('checked', false);
        jQuery("#wplc_rb_theme_3").attr('checked', false);
        jQuery("#wplc_rb_theme_4").attr('checked', false);
        jQuery("#wplc_rb_theme_5").attr('checked', false);
        jQuery("#wplc_rb_theme_6").attr('checked', false);
        jQuery("#wplc_theme_1").addClass("wplc_theme_active");
        jQuery("#wplc_theme_2").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_3").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_4").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_5").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_6").removeClass("wplc_theme_active");
    });
    
    jQuery("#wplc_theme_2").click(function() {
        jQuery("#wplc_rb_theme_1").attr('checked', false);
        jQuery("#wplc_rb_theme_2").attr('checked', true);
        jQuery("#wplc_rb_theme_3").attr('checked', false);
        jQuery("#wplc_rb_theme_4").attr('checked', false);
        jQuery("#wplc_rb_theme_5").attr('checked', false);
        jQuery("#wplc_rb_theme_6").attr('checked', false);
        jQuery("#wplc_theme_1").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_2").addClass("wplc_theme_active");
        jQuery("#wplc_theme_3").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_4").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_5").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_6").removeClass("wplc_theme_active");
    });
    
    jQuery("#wplc_theme_3").click(function() {
        jQuery("#wplc_rb_theme_1").attr('checked', false);
        jQuery("#wplc_rb_theme_2").attr('checked', false);
        jQuery("#wplc_rb_theme_3").attr('checked', true);
        jQuery("#wplc_rb_theme_4").attr('checked', false);
        jQuery("#wplc_rb_theme_5").attr('checked', false);
        jQuery("#wplc_rb_theme_6").attr('checked', false);
        jQuery("#wplc_theme_1").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_2").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_3").addClass("wplc_theme_active");
        jQuery("#wplc_theme_4").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_5").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_6").removeClass("wplc_theme_active");
    });
    
    jQuery("#wplc_theme_4").click(function() {
        jQuery("#wplc_rb_theme_1").attr('checked', false);
        jQuery("#wplc_rb_theme_2").attr('checked', false);
        jQuery("#wplc_rb_theme_3").attr('checked', false);
        jQuery("#wplc_rb_theme_4").attr('checked', true);
        jQuery("#wplc_rb_theme_5").attr('checked', false);
        jQuery("#wplc_rb_theme_6").attr('checked', false);
        jQuery("#wplc_theme_1").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_2").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_3").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_4").addClass("wplc_theme_active");
        jQuery("#wplc_theme_5").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_6").removeClass("wplc_theme_active");
    });
    
    jQuery("#wplc_theme_5").click(function() {
        jQuery("#wplc_rb_theme_1").attr('checked', false);
        jQuery("#wplc_rb_theme_2").attr('checked', false);
        jQuery("#wplc_rb_theme_3").attr('checked', false);
        jQuery("#wplc_rb_theme_4").attr('checked', false);
        jQuery("#wplc_rb_theme_5").attr('checked', true);
        jQuery("#wplc_rb_theme_6").attr('checked', false);
        jQuery("#wplc_theme_1").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_2").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_3").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_4").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_5").addClass("wplc_theme_active");
        jQuery("#wplc_theme_6").removeClass("wplc_theme_active");
    });
    
    jQuery("#wplc_theme_6").click(function() {
        jQuery("#wplc_rb_theme_1").attr('checked', false);
        jQuery("#wplc_rb_theme_2").attr('checked', false);
        jQuery("#wplc_rb_theme_3").attr('checked', false);
        jQuery("#wplc_rb_theme_4").attr('checked', false);
        jQuery("#wplc_rb_theme_5").attr('checked', false);
        jQuery("#wplc_rb_theme_6").attr('checked', true);
        jQuery("#wplc_theme_1").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_2").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_3").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_4").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_5").removeClass("wplc_theme_active");
        jQuery("#wplc_theme_6").addClass("wplc_theme_active");
    });
    var wplc_agent_status = jQuery("#wplc_agent_status").attr('checked');

    if(wplc_agent_status === 'checked'){
        jQuery("#wplc_agent_status_text").html(wplc_admin_strings.accepting_chats);
    } else {
        jQuery("#wplc_agent_status_text").html(wplc_admin_strings.not_accepting_chats);
    }

      
    jQuery(".wplc_history_rating").each(function() {
        var wplc_score = jQuery(this).attr('rating');
        if(wplc_score === '0'){
            jQuery(this).html(wplc_ce_rating_message);
        } else {
            jQuery(this).raty({ 
                path: wplc_ce_url,
                score: wplc_score,
                readOnly: true
            });
        }
    });

    jQuery("body").on("click", "#wplc_add_agent", function(e) {
        
        var uid = parseInt(jQuery("#wplc_agent_select").val());
        var em = jQuery("#wplc_selected_agent_"+uid).attr('em');
        var em2 = jQuery("#wplc_selected_agent_"+uid).attr('em2');
        var name = jQuery("#wplc_selected_agent_"+uid).attr('name');
        
        if (uid) {
            var data = {
                action: 'wplc_add_agent',
                security: wplc_admin_strings.nonce,
                uid: uid
            };
            jQuery.post(ajaxurl, data, function(response) {
                if (response === "1") {
                    /* success */
                    var wplchtml = "<li id=\"wplc_agent_li_"+uid+"\"><p><img src=\"//www.gravatar.com/avatar/"+em+"?s=80&d=mm\"></p><h3>"+name+"</h3><small>"+em2+"</small><p><button class='button button-secondary' id='wplc_remove_agent' uid='"+uid+"'>"+wplc_admin_strings.remove_agent+"</button></p></li>"
                    jQuery(wplchtml).insertBefore("#wplc_add_new_agent_box").hide().fadeIn(2000);
                    jQuery("#wplc_selected_agent_"+uid).remove();
                } else {
                    /* failure */
                }
            });
        }
        e.preventDefault();

        jQuery(this).hide();


    });

     jQuery("body").on("click", ".wplc_remove_agent", function(e) {
        
        var uid = parseInt(jQuery(this).attr('uid'));
        
        if (uid) {
            var data = {
                action: 'wplc_remove_agent',
                security: wplc_admin_strings.nonce,
                uid: uid
            };
            jQuery.post(ajaxurl, data, function(response) {
                if (response === "1") {
                    /* success */
                   
                    jQuery("#wplc_agent_li_"+uid).fadeOut(500);
                } else {
                    /* failure */
                }
            });
        }
        e.preventDefault();


    });

    jQuery("body").on("click", "#wplc_use_external_server", function(){
        if(jQuery("#wplc_use_external_server").is(":checked")){
            var data = {
                action: 'wplc_use_external_server',
                security: wplc_admin_strings.nonce,
                api_key:  jQuery("#wplc_api_key").val(),
                status: '1'
            };
            jQuery.post(ajaxurl, data, function(response) {
                jQuery("#external_server_message").html("<p class='description'>You are now using WP Live Chat by 3CX\'s chat server to host your chats</p>");
            });
        } else {
            var data = {
                action: 'wplc_use_external_server',
                security: wplc_admin_strings.nonce,
                api_key:  jQuery("#wplc_api_key").val(),
                status: '0'
            };
            jQuery.post(ajaxurl, data, function(response) {
                jQuery("#external_server_message").html("<p class='description'>You are now using your own website as a chat server to host your chats</p>");
            });
        }
    });     
    
});