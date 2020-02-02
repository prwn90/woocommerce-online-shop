var wplc_online_agent_count = 0;
var wplc_switchery_init = false;

jQuery(function() {
    wplc_choose_delegate();
});

function wplc_update_agent_status(isOnline) {
  if (isOnline) {
    jQuery('#wplc_online_topbar_switch').removeClass('wplc_online_topbar_switch_offline');
    jQuery('#wplc_online_topbar_switch').addClass('wplc_online_topbar_switch_online');
    jQuery('#wplc_ma_online_agent_text').text(wplc_choose_admin_strings.accepting_chats);
    jQuery('#wplc_online_topbar_switch').attr('checked','true');          
  } else {
    jQuery('#wplc_online_topbar_switch').removeClass('wplc_online_topbar_switch_online');
    jQuery('#wplc_online_topbar_switch').addClass('wplc_online_topbar_switch_offline');
    jQuery('#wplc_ma_online_agent_text').text(wplc_choose_admin_strings.not_accepting_chats);
    jQuery('#wplc_online_topbar_switch').removeAttr('checked');
  }
}

jQuery(document).ready(function() {
  jQuery('#wplc_online_topbar_switch').on('click',function(e) {
    jQuery('#wplc_online_topbar_switch').attr('disabled', true);
    setTimeout(function() {jQuery('#wplc_online_topbar_switch').removeAttr('disabled');}, 2000);
    if (jQuery('#wplc_online_topbar_switch').attr('checked')) {
      if (jQuery('#wplc_agent_status').length) {
        jQuery('#wplc_agent_status').click();
      } else {
        wplc_set_agent_online();
      }
    } else {
      if (jQuery('#wplc_agent_status').length) {
        jQuery('#wplc_agent_status').click();
      } else {
        wplc_set_agent_offline();
      }
    }
    return true;
  });

  jQuery(document).on("wplc_agent_online_changed", function(e) {
    if (typeof e.response !== "undefined") {
      try {
        var reply = JSON.parse(e.response);
        if (!!reply && reply.agents) {
          jQuery("#wplc_ma_online_agents_label").text(wplc_choose_admin_strings.agent_online_plural);
          if (reply.agents.length>0) { // at least one agent online
            // agent count
            jQuery("#wplc_ma_online_agents_circle").removeClass("wplc_red_circle");
            jQuery("#wplc_ma_online_agents_circle").addClass("wplc_green_circle");
            if (reply.agents.length==1) {
              jQuery("#wplc_ma_online_agents_label").text(wplc_choose_admin_strings.agent_online_singular);
            }
          } else { // no agents online
            // agent count
            jQuery("#wplc_ma_online_agents_circle").removeClass("wplc_green_circle");
            jQuery("#wplc_ma_online_agents_circle").addClass("wplc_red_circle");
          }
          jQuery("#wplc_ma_online_agents_count").text(reply.agents.length);
        }
      } catch(e) {
        // probably session expired, try force reload
        window.onbeforeunload = null;
        window.location.reload();
      }
      // todo: update dropdown agent list in admin menu
    }

  });

  jQuery(document).on("wplc_switchery_changed", function(e) {
    jQuery.event.trigger({type: "wplc_agent_online_changed", response: e.response, ndata:e.ndata});
  });  

  jQuery(document).on("tcx_agent_connected", function(e) {
    wplc_ma_update_admin_bar();
  });

  jQuery(document).on("tcx_agent_disconnected", function(e) {
    wplc_ma_update_admin_bar();
  });

});

jQuery(document).on("tcx_dom_ready", function(e) {
    wplc_choose_delegate();

});

function wplc_choose_delegate() {
  if (!!wplc_choose_accept_chats.value) {
    jQuery("#wplc_agent_status").prop("checked", true);
    wplc_online_agent_count = wplc_ma_parse_active_count_from_container();
  } else {
    jQuery("#wplc_agent_status").prop("checked", false);
  }

  var wplc_agent_status = jQuery("#wplc_agent_status").attr('checked');

  if(wplc_agent_status === 'checked'){
    jQuery("#wplc_agent_status_text").html(wplc_choose_admin_strings.accepting_chats);
  } else {
    jQuery("#wplc_agent_status_text").html(wplc_choose_admin_strings.not_accepting_chats);
  }

  /* Make sure switchery has been loaded on this page */
  if (typeof Switchery !== 'undefined') {
    var wplc_switchery_element = document.querySelector('.wplc_switchery');
    /* Make sure that the switch is being displayed */
    if (wplc_switchery_element !== null) {
      if (wplc_switchery_init == false) {
        wplc_switchery_init = new Switchery(wplc_switchery_element, { color: '#0596d4', secondaryColor: '#333333', size: 'small' });
      }
      var changeCheckbox = document.querySelector('#wplc_agent_status');
      changeCheckbox.onchange = function () {
        wplc_switchery_init.disable();
        setTimeout(function() {wplc_switchery_init.enable();},3000);
        var wplc_accepting_chats = jQuery(this).attr('checked');
        if (wplc_accepting_chats === 'checked') {
          wplc_set_agent_online();
        } else {
          wplc_set_agent_offline();
        }
      };

    }
  }
}

//Transient
function wplc_ma_update_agent_transient(data) {
  jQuery.post(ajaxurl, data, function(response) {
    if (response) {
      if (!config.wplc_use_node_server) {
        window.location.reload();
      } else {
        jQuery.event.trigger({type: "wplc_switchery_changed", response:response, ndata:data});
      }
    }
  }).fail(function(e){
    window.onbeforeunload = null;
    window.location.reload();
  });
}

function wplc_set_agent_online() {
  connection_lost_type = '';
  jQuery("#wplc_agent_status_text").html(wplc_choose_admin_strings.accepting_status);
  var data = {
    action: 'wplc_choose_accepting',
    security: wplc_admin_strings.nonce,
    user_id:  wplc_admin_strings.user_id
  };
  wplc_update_agent_status(true);
  wplc_ma_update_agent_transient(data);
}

function wplc_set_agent_offline() {
  jQuery.event.trigger({type: "wplc_end_all_open_chats"});
  jQuery("#wplc_agent_status_text").html(wplc_choose_admin_strings.not_accepting_status);
  connection_lost_type = 'offline_status';
  var data = {
    action: 'wplc_choose_not_accepting',
    security: wplc_admin_strings.nonce,
    user_id:  wplc_admin_strings.user_id
  };
  wplc_update_agent_status(false);
  wplc_ma_update_agent_transient(data);
}

function wplc_ma_update_admin_bar() {
  var data = {
    action: 'wplc_agent_list',
    security: wplc_admin_strings.nonce,
    user_id:  wplc_admin_strings.user_id
  };  
  jQuery.post(ajaxurl, data, function(response) {
    setTimeout(function() {wplc_ma_update_admin_bar();}, 30000); // force update agent count - for offline agents
    jQuery.event.trigger({type: "wplc_agent_online_changed", response: response, ndata:data});
  }).fail(function(e) {
    window.onbeforeunload = null;
    window.location.reload();
  });
}

//Get the value currently stored in the admin bar
function wplc_ma_parse_active_count_from_container(){
    return parseInt(jQuery("#wplc_ma_online_agents_count").text());
}

setTimeout(function() {wplc_ma_update_admin_bar();}, 30000); // force update agent count - for offline agents