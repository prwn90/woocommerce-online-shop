function wplc_emit_custom_data_event( action, event_object ){

    if (typeof socket !== "undefined" && socket.connected === true) {

        var a_wplc_cid = Cookies.get('wplc_cid');

        if( typeof a_wplc_cid !== 'undefined' ) {

            socket.emit('custom data',{ action: action, chatid: a_wplc_cid, ndata: event_object } );    

        }

    }

}

