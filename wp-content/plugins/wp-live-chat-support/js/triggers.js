var wplc_rating = 0;
var wplc_comment;

jQuery(function() {

jQuery(document).on( "wplc_admin_chat_loop", function( e ) {
    if(typeof wplc_node_sockets_ready === "undefined" || wplc_node_sockets_ready === false){
        if (e.response['typing'] === "1") {
            if (jQuery("#wplc_user_typing").length>0) { } else { 
                var wplc_user_name = jQuery(".admin_chat_name").html();
                if(typeof wplc_user_name !== "undefined" && typeof wplc_localized_string_is_typing !== "undefined"){
                    jQuery(".typing_indicator").html("<span id='wplc_user_typing'>"+wplc_safe_html(wplc_user_name+" "+wplc_localized_string_is_typing)+"</span>");
                } else {
                    /* Backwards Compat */
                    jQuery("#admin_chat_box_area_" + cid).append("<img id='wplc_user_typing' src='"+wplc_misc_strings.typingimg+"' />");
                    jQuery("#wplc_user_typing").fadeIn("fast");
                    var height = jQuery('#admin_chat_box_area_' + cid)[0].scrollHeight;
                    jQuery('#admin_chat_box_area_' + cid).scrollTop(height);
                }
            }
        } else if (e.response['typing'] === "0") {
            if (jQuery("#wplc_user_typing").length>0) {
                jQuery("#wplc_user_typing").fadeOut("slow").remove();
            }
        }
    }

    if (typeof e.response['chat_rating'] !== 'undefined' && typeof e.response['chat_rating']['rating'] !== 'undefined' && e.response['chat_rating']['rating'] !== false) {
        if(parseInt(e.response['chat_rating']['rating']) !== wplc_rating || e.response['chat_rating']['comment'] !== wplc_comment){ //Has changed from default
            wplc_rating = parseInt(e.response['chat_rating']['rating']);
            wplc_comment = e.response['chat_rating']['comment'];

            jQuery(".rating").removeClass("rating-good");
            jQuery(".rating").removeClass("rating-bad");
            jQuery(".rating").addClass(wplc_rating == 1 ? "rating-good" : (wplc_rating == 0 ? "rating-bad" : "" ));
            jQuery(".rating").text(wplc_rating == 1 ? "Satisfied" : (wplc_rating == 0 ? "Unsatisfied" : "No Rating" ));
            jQuery("#rating-comment-holder").text(wplc_comment !== "" && typeof wplc_comment !== "undefined" ? "\"" +  wplc_comment + "\"" : "\"No Comment...\"");
        }
    }
});
    
});