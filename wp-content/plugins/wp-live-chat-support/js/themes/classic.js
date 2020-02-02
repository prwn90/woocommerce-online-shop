jQuery(document).on("wplc_minimize_chat", function( e ) {
    jQuery('#wp-live-chat').height("");
    if(jQuery("#wp-live-chat").attr("original_pos") === "bottom_right"){
        jQuery("#wp-live-chat").css("left", "");
        jQuery("#wp-live-chat").css("bottom", "0");
        jQuery("#wp-live-chat").css("right", "20px");
    } else if(jQuery("#wp-live-chat").attr("original_pos") === "bottom_left"){
        jQuery("#wp-live-chat").css("left", "20px");
        jQuery("#wp-live-chat").css("bottom", "0");
        jQuery("#wp-live-chat").css("right", "");
    } else if(jQuery("#wp-live-chat").attr("original_pos") === "left"){
        jQuery("#wp-live-chat").css("left", "0");
        jQuery("#wp-live-chat").css("bottom", "100px");
        jQuery("#wp-live-chat").css("width", "");
        jQuery("#wp-live-chat").css("height", "");
    } else if(jQuery("#wp-live-chat").attr("original_pos") === "right"){
        jQuery("#wp-live-chat").css("left", "");
        jQuery("#wp-live-chat").css("right", "0");
        jQuery("#wp-live-chat").css("bottom", "100px");
        jQuery("#wp-live-chat").css("width", "");
        jQuery("#wp-live-chat").css("height", "");
    }
    jQuery('#wp-live-chat').addClass("wplc_close");
    jQuery('#wp-live-chat').removeClass("wplc_open");
    jQuery("#wp-live-chat").css("top", "");
    jQuery("#wp-live-chat-1").show();
    jQuery("#wp-live-chat-1").css('cursor', 'pointer');
    jQuery("#wp-live-chat-2").hide();
    jQuery("#wp-live-chat-3").hide();
    jQuery("#wp-live-chat-4").hide();
    // jQuery("#wplc_social_holder").hide();
    jQuery("#nifty_ratings_holder").hide();
    jQuery("#wp-live-chat-react").hide();
    jQuery("#wp-live-chat-minimize").hide();
});
jQuery(document).on("click", "#wp-live-chat", function( e ){
    
});

jQuery(document).on("wplc_start_chat", function( e ) { 
    jQuery("#wp-live-chat-2-inner").hide("slow");
    jQuery.event.trigger({type: "wplc_open_chat_1"});
    jQuery.event.trigger({type: "wplc_open_chat_2", wplc_online: wplc_online});
});
jQuery(document).on( "wplc_open_chat_1", function( e ) {

    jQuery('#wp-live-chat').removeClass("wplc_close");
    jQuery('#wp-live-chat').addClass("wplc_open");
    jQuery("#wp-live-chat-react").hide();
    jQuery("#wp-live-chat-header").css('cursor', 'all-scroll');
    Cookies.set('wplc_hide', "", { expires: 1, path: '/' });
    jQuery("#wp-live-chat-minimize").show();

    /* set the width again as jQuery sometimes messes this up completely. */
    if(jQuery("#wp-live-chat").attr("original_pos") === "left"){
       jQuery("#wp-live-chat").css("width", "280px");
       jQuery("#wp-live-chat").css("height", "auto !important");
    } else if(jQuery("#wp-live-chat").attr("original_pos") === "right"){
       jQuery("#wp-live-chat").css("width", "280px");
       jQuery("#wp-live-chat").css("height", "auto !important");
    }


    jQuery(function() {
        jQuery( "#wp-live-chat" ).draggable({ 
            handle: "#wp-live-chat-header",
            drag: function( event, ui ) {
                jQuery(this).css("right","");
                jQuery(this).css("bottom","inherit");
            }
        });
    });


});


jQuery(document).on( "wplc_open_chat_2", function( e ) {

    jQuery("#wp-live-chat-2").hide();

    if(!jQuery("#wp-live-chat").hasClass("wplc_open")){
       jQuery("#wplc_chatmsg").focus();
    }

    jQuery("#wp-live-chat-header").addClass("active");
    jQuery("#wp-live-chat").addClass("mobile-active");

    wplc_chat_status = Cookies.get('wplc_chat_status');
    if (typeof e.wplc_online !== "undefined" && e.wplc_online === true) {
       jQuery("#wp-live-chat-4").show();
       // jQuery("#wplc_social_holder").show();
       jQuery("#nifty_ratings_holder").show();
       jQuery("#wp-live-chat-1").css("cursor","pointer");
    } else if (e.wplc_online === false) {
       jQuery("#wp-live-chat-2").show();
       jQuery("#wp-live-chat").css("height","auto !important");
       jQuery("#wp-live-chat-4").hide();
       // jQuery("#wplc_social_holder").hide();
       jQuery("#nifty_ratings_holder").hide();
       jQuery("#wp-live-chat-1").css("cursor","pointer");
   }

    jQuery("#wp-live-chat-3").hide();
    jQuery("#wp-live-chat-close").hide();
    Cookies.set('wplc_minimize', "", { expires: 1, path: '/' });
});

jQuery(function() { 
    //opens chat when clicked on top bar
    jQuery("body").on("click", ".wplc_retry_chat", function() {
        Cookies.set('wplc_chat_status', 5);
        jQuery("#wplc_chatbox").html("");
        jQuery("#wp-live-chat-4").fadeOut();
        jQuery("#wp-live-chat-2").fadeIn();
        jQuery("#wp-live-chat-2-inner").fadeIn();
        wplc_shown_welcome = false;
    });
    jQuery("body").on("click", "#speeching_button", function() {
        jQuery("#wplc_hovercard").hide();
        wplc_is_chat_open = true;
        jQuery.event.trigger({type: "wplc_open_chat"});
        jQuery("#wp-live-chat-minimize").show();

    });

    jQuery("body").on("click", "#wplc_hovercard_min", function(){
      jQuery("#wplc_hovercard").fadeOut();
    });

    jQuery("body").on("click", "#wp-live-chat-header", function(){
        jQuery("#wplc_hovercard").hide();
        jQuery("#wp-live-chat").css("height","");
        jQuery("#wplc-chat-alert").removeClass('is-active');
        jQuery('#wplc_gdpr_end_chat_notice_container').hide();
    });

    jQuery("body").on("click", "#wp-live-chat-header", function(){

            if (jQuery(this).hasClass('active')) {
                jQuery(this).removeClass('active');
                jQuery.event.trigger({type: "wplc_minimize_chat"});

                Cookies.set('wplc_minimize', "yes", { expires: 1, path: '/' });
                
            } else {

                jQuery.event.trigger({type: "wplc_open_chat"});
                jQuery("#wp-live-chat-minimize").show();

                Cookies.set('wplc_minimize', "", { expires: 1, path: '/' });

            }


            
    });

});
