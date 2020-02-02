jQuery('body').on('click', '#chatTranscriptTitle, #wplc_admin_email_transcript', sendTranscript);

function sendTranscript(event) {
  var data = {};
  if (jQuery('#chatTranscriptTitle').length) {
    var cur_id = jQuery('#chatCloseTitle').attr('cid');
    data.action = 'wplc_et_admin_email_transcript';
    data.security = wplc_transcript_nonce.ajax_nonce;
    data.cid = active_chatid;
    jQuery.post(ajaxurl, data, function(response) {
      if (typeof response === 'string' && response.length < 1) {
        return;
      }
      returned_data = JSON.parse(response);
      if (returned_data.constructor === Object) {
        jQuery('.nifty_admin_chat_prompt, .nifty_admin_overlay').css('display', 'block');
        jQuery('#nifty_admin_chat_prompt_confirm').css('display', 'none');
        jQuery('.nifty_admin_chat_prompt_title').html(wplc_transcript_nonce.string_title);
        jQuery('#nifty_admin_chat_prompt_cancel').html(wplc_transcript_nonce.string_close);
        if (returned_data.errorstring) {
          jQuery('.nifty_admin_chat_prompt_message').html(wplc_transcript_nonce.string_error1);
        } else {
          jQuery('.nifty_admin_chat_prompt_message').html(wplc_transcript_nonce.string_chat_emailed);
        }
      }
    });
  } else {
    jQuery(".wplc_admin_email_transcript").hide();
    html = "<span class='wplc_et_loading' style='color:#000'><em>" + wplc_transcript_nonce.string_loading + "</em></span>";
    jQuery(".wplc_admin_email_transcript").after(html);
    var cur_id = jQuery(this).attr("cid");
    data.action = 'wplc_et_admin_email_transcript';
    data.security = wplc_transcript_nonce.ajax_nonce;
    data.cid = cur_id;
    jQuery.post(ajaxurl, data, function (response) {
      returned_data = JSON.parse(response);
      if (returned_data.constructor === Object) {
        if (returned_data.errorstring) {
          jQuery(".wplc_admin_email_transcript").after("<p><strong>" + wplc_transcript_nonce.string_error1 + "</strong></p>");
        } else {
          jQuery(".wplc_et_loading").hide();
          html = "<span class='' style='color:#000'>The chat transcript has been emailed.</span>";
          jQuery("#wplc_admin_email_transcript").after(html);
          jQuery("#wplc_admin_email_transcript").hide();
        }
      }
    });
  }
}