/**
 * Custom data trigger
 *
 * If custom data is sent through the socket, this is where you would want to handle it
 *
 * @return void
 */
jQuery(document).on("tcx_custom_data_received", function(e) {
    if (typeof e.ndata !== "undefined") {
      
        if (e.ndata.action === "send_user_canvas") {
          if (active_chatid === e.ndata.chatid) {
            the_message = wplc_generate_system_notification_object("<img style='max-width:400px;' src='"+e.ndata.ndata+"' />", {}, 0);
            wplc_push_message_to_chatbox(the_message,'a', function() {
                  wplc_scroll_to_bottom();  
              });
          }
        }

        if(e.ndata.action === "wplc_minimized") {
          the_message = wplc_generate_system_notification_object("User minimized the chat box", {}, 0);
          tcx_add_message_to_sessionStorage(e.ndata.chatid, the_message);
        }

        if(e.ndata.action === "wplc_maximized") {
          the_message = wplc_generate_system_notification_object("User maximized the chat box", {}, 0);
          tcx_add_message_to_sessionStorage(e.ndata.chatid, the_message);
        }        

    }
});