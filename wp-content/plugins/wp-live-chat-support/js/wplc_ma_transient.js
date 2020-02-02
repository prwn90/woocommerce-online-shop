 jQuery(function() {
    /* Going online functionality used to be here */
    wplc_ma_timer_update_agent_transient();
    function wplc_ma_timer_update_agent_transient() {
        var data = {
            action: 'wplc_admin_set_transient',
            security: wplc_admin_strings.nonce,
            user_id:  wplc_admin_strings.user_id
        };
        jQuery.post(ajaxurl, data, function(response) {
          setTimeout(function() {
            wplc_ma_timer_update_agent_transient();
          }, 60000);
          jQuery.event.trigger({type: "wplc_agent_online_changed", response: response, ndata:data});
        }).fail(function(e){
          window.onbeforeunload = null;
          document.location.reload();
        });
    }
});