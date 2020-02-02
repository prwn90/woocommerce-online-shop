(function($) {
  "use strict";
  $(function() {
    $(document).on('heartbeat-send', function(e, data) {
      data['client'] = 'wplc_heartbeat';
    });
    wpcl_admin_set_transient();

    function wpcl_admin_set_transient() {
      var data = {
        action: 'wplc_admin_set_transient',
        security: wplc_transient_nonce

      };
      if (typeof ajaxurl === "undefined" && typeof wplc_ajaxurl !== "undefined") { var ajaxurl = wplc_ajaxurl; }
      $.post(ajaxurl, data, function(response) {
        setTimeout(function() {
          wpcl_admin_set_transient();
        }, 60000);
      }).fail(function(e){
        window.onbeforeunload = null;
        window.location.reload();
      });
    }
  });
}(jQuery));