var wplc_searchTimeout;
var wplc_is_typing = false;
var wplc_has_sent_auto_first_response = false;
var wplc_user_has_left_rating = false;


jQuery(document).on("wplc_animation_done", function(e) {

  jQuery("#wplc_message").on("keyup", function(){
    var wplc_char_count = jQuery('#wplc_message').val().length;
    var wplc_char_limit = 700;
    jQuery('.wplc_char_counter').text(wplc_char_count + " / " + wplc_char_limit);
  });
	

  jQuery('.nifty_rating_icon').click(function(evt){
    if(!wplc_user_has_left_rating){
      jQuery('.nifty_rating_icon').removeClass('wplc-color-1');
      jQuery(this).addClass('wplc-color-1');
    }
  });
  jQuery("#nifty_rating_pos").click(function(evt){
      if(!wplc_user_has_left_rating){
        jQuery("#nifty_rating_thanks").hide();
        // jQuery("#nifty_ratings_form").fadeIn();
        jQuery("#nifty_rating_button").attr("nifty-rating", "1");
        jQuery('#nifty_rating_button').click();
        wplc_user_has_left_rating = true;
      }

      evt.stopImmediatePropagation(); 
  });
  jQuery("#nifty_rating_neg").click(function(evt){
      if(!wplc_user_has_left_rating){
        jQuery("#nifty_rating_thanks").hide();
        // jQuery("#nifty_ratings_form").fadeIn();
        jQuery("#nifty_rating_button").attr("nifty-rating", "0");
        jQuery('#nifty_rating_button').click();
        wplc_user_has_left_rating = true;
      }

      evt.stopImmediatePropagation(); 
  });
  jQuery("#nifty_rating_button").click(function(evt){
      var nifty_rating = jQuery(this).attr("nifty-rating");
      var nifty_comment = jQuery("#nifty_ratings_comment").val() !== "" ? jQuery("#nifty_ratings_comment").val() : "No Comment...";
      jQuery("#nifty_ratings_form").hide();
      jQuery("#nifty_recording").fadeIn();

      var formData = new FormData();

      formData.append('action', 'wplc_record_chat_rating');
      formData.append('cid', Cookies.get('wplc_cid'));
      formData.append('rating', nifty_rating);
      formData.append('comment', nifty_comment);
      formData.append('security', wplc_nonce );
      formData.append('update_chat_rating', "1" );
      formData.append('wplc_extra_data[domain]', wplc_extra_data['domain'] );
      formData.append('wplc_extra_data[api_key]', wplc_extra_data['api_key'] );

      jQuery.ajax({
             url : wplc_ajaxurl,
             type : 'POST',
             data : formData,
             cache: false,
             processData: false, 
             contentType: false, 
             success : function(data) {    
                wplc_pro_rating_show_thanks_message();
             },
             error : function (){
                wplc_pro_rating_show_thanks_message();
             }
      });

      jQuery.event.trigger({type: 'wplc_send_live_rating', rating: {score: nifty_rating, comment: nifty_comment} });

      evt.stopImmediatePropagation(); 
  });

  jQuery("#nifty_rating_button_cancel").click(function(evt){
    jQuery('.nifty_rating_icon').removeClass('wplc-color-1');
    jQuery("#nifty_ratings_form").fadeOut();
  });

  function wplc_pro_rating_show_thanks_message(){
    jQuery("#nifty_recording").fadeOut();
    jQuery("#nifty_rating_thanks").fadeIn();

    setTimeout(function(){
      jQuery("#nifty_rating_thanks").fadeOut();      
    }, 2000);

    jQuery("#nifty_ratings_comment").val("");
  }
});

jQuery(function() { 


  /* this is not working properly
  jQuery(document).on("wplc_start_chat", function( e ) { 
        var data = {
          action: 'wplc_start_chat_hook',
          security: wplc_nonce,
          cid: Cookies.get('wplc_cid'),
          wplc_extra_data:{
            name: jQuery("#wplc_name").val().replace(/(<([^>]+)>)/ig,""),
            email: jQuery("#wplc_email").val().replace(/(<([^>]+)>)/ig,"")
          }
        };

        if (!!wplc_restapi_enabled.value) {
          data.security = (typeof wplc_restapi_token !== "undefined" ? wplc_restapi_token : false);
          jQuery.post(wplc_restapi_endpoint+"/new-chat/", data, function(response) {});
        }
        //jQuery.post(wplc_ajaxurl, data, function(response) {});

  });
  */

  jQuery("body").on("change", "#wplc_user_selected_department", function(){
    if(wplc_extra_data !== "undefined"){
      wplc_extra_data['wplc_user_selected_department'] = jQuery(this).val();
    }
  });
 




});

jQuery(document).on("tcx_send_message", function(e) {
  if(typeof wplc_pro_auto_resp_chat_msg !== 'undefined' && wplc_pro_auto_resp_chat_msg !== ""){
    if(wplc_has_sent_auto_first_response == false){
      if(jQuery('.wplc-admin-message').length <= 0){
        var first_response_html = "<span class='wplc-admin-message wplc-color-bg-4 wplc-color-2 wplc-color-border-4' mid='" + Date.now() + "'>";
        first_response_html += "<div class='wplc-msg-content' mid='" + Date.now() + "'>";
        first_response_html += "<span class='messageBody'>" + wplc_safe_html(wplc_pro_auto_resp_chat_msg) + "</span>";
        first_response_html += "</div></span>";

        setTimeout(function(){
          jQuery('#wplc_chatbox').append(first_response_html);

          var data = {
            relay_action: 'wplc_admin_send_msg',
            security: wplc_nonce,
            chat_id: wplc_cid,
            message: wplc_pro_auto_resp_chat_msg,
            msg_id: Date.now(),
            agent_id:0
          };

          if(typeof wplc_rest_api !== "undefined"){
            wplc_rest_api('send_message', data, 12000, null);
          }
        }, 1000);
      }
      wplc_has_sent_auto_first_response = true;
    }
  }
});

function wplc_start_chat_pro_data(data){
  if(typeof wplc_extra_data !== "undefined" && typeof wplc_extra_data['wplc_user_selected_department'] !== "undefined"){
    data['wplc_user_selected_department'] = wplc_extra_data['wplc_user_selected_department'];
  }

  return data;
}

/* Handles Uploading and sharing a file within chat*/
function wplcShareFile(fileToUpload, failedID, successID, uploadingID, originalID) {
  if (fileToUpload == undefined || fileToUpload == false || fileToUpload == null) {
    return;
  }

  var afterFailedUpload = function() {
    jQuery(uploadingID).hide();
    jQuery(failedID).show();
    setTimeout(function(){
      jQuery(failedID).hide();
      jQuery(originalID).show();
    }, 2000);
  }

  var formData = new FormData();

  formData.append('action', 'wplc_upload_file');
  formData.append('cid', Cookies.get('wplc_cid'));
  formData.append('file', fileToUpload);
  formData.append('timestamp', Date.now());
  formData.append('security', wplc_nonce );

  /*Handle jQuery Elements*/
  jQuery(uploadingID).show();
  jQuery(originalID).hide();
  jQuery(successID).hide();
  jQuery(failedID).hide();
  if (fileToUpload.name.match(new RegExp('^.*\\.(' + config.allowed_upload_extensions + ')$','i'))) {
    // Files allowed - continue
    if (fileToUpload.size < 8000000) { //Max size of 4MB
      jQuery.ajax({
        url : wplc_ajaxurl_site,
        type : 'POST',
        data : formData,
        cache: false,
        processData: false, 
        contentType: false, 
        success : function(data) {    
          if (parseInt(data) !== 0) {
            jQuery(uploadingID).hide();
            jQuery(successID).show();
            setTimeout(function(){
              jQuery(successID).hide();
              jQuery(originalID).show(); 
            }, 2000);
            if (data.substring(0,7) !== 'ERROR: ') {
              var tag='link';
              jQuery("#wplc_chatmsg").val(tag + ":" + data + ":" + tag); //Add to input field
              jQuery("#wplc_send_msg").trigger("click"); //Send message
            } else {
              alert('Upload error: ' + data.substring(7));
              afterFailedUpload();
            }
          } else {
            afterFailedUpload();
          }
        },
        error : function (){
          afterFailedUpload();
        }
      });
    } else {
      alert("File limit is 4mb");
      afterFailedUpload();
    }
  } else {
    alert("File type not supported.");
    afterFailedUpload();
  }
}

function wplc_start_chat_pro_custom_fields_filter( wplc_extra_data_tmp, rest_action_data, callback ) {

    var custom_field_array = {};

    var cnt = 0;
    jQuery('*[name^="wplc_custom_field"]').each(function(key, val) {

      var field_name = jQuery(this).attr( "fname" );
      custom_field_array[cnt] = {};
      custom_field_array[cnt][0] = field_name;
      custom_field_array[cnt][1] = jQuery(this).val().trim();
      if (custom_field_array[cnt][1]=='') {
        custom_field_array[cnt][1] = jQuery(this).attr('placeholder');
      }
      cnt++;
    });

    var custom_data = custom_field_array;
    wplc_extra_data_tmp['custom_fields'] = JSON.stringify(custom_data);

    if(typeof callback === "function"){
      rest_action_data.wplc_extra_data = wplc_extra_data_tmp;
      callback(wplc_extra_data_tmp, rest_action_data);
    }

    return wplc_extra_data_tmp;

}
