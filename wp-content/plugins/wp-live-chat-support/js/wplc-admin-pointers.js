var wp_button_pointer_array = new Array();
wp_button_pointer_array[1] = {
  'element': 'wplc_initiate_chat',
  'options': {
    'content': pointer_localize_strings["initiate"],
    'position': { 'edge': 'right', 'align': 'middle' }
  }
};
wp_button_pointer_array[2] = {
  'element': 'wplc-agent-info',
  'options': {
    'content': pointer_localize_strings["agent_info"],
    'position': { 'edge': 'right', 'align': 'middle' }
  }
};
wp_button_pointer_array[3] = {
  'element': 'wplc_second_chat_request',
  'options': {
    'content': pointer_localize_strings["chats"],
    'position': { 'edge': 'right', 'align': 'middle' }
  }
};
wp_button_pointer_array[4] = {
  'element': 'chatTransferUps',
  'options': {
    'content': pointer_localize_strings["transfer"],
    'position': { 'edge': 'right', 'align': 'middle' }
  }
};
wp_button_pointer_array[5] = {
  'element': 'chatDirectUserToPageUps',
  'options': {
    'content': pointer_localize_strings["direct_to_page"],
    'position': { 'edge': 'right', 'align': 'middle' }
  }
};


jQuery(function($) {

  jQuery('body').on("click", ".wplc_initiate_chat", function(e) {
    e.preventDefault();
    if (typeof(jQuery().pointer) != 'undefined') { // make sure the pointer class exists

      if (jQuery('.wp-pointer').is(":visible")) { // if a pointer is already open...
        var openid = jQuery('.wp-pointer:visible').attr("id").replace('wp-pointer-', ''); // ... note its id
        jQuery('#' + wp_button_pointer_array[2].element).pointer('close'); // ... and close it
        jQuery('#' + wp_button_pointer_array[3].element).pointer('close'); // ... and close it
        jQuery('#' + wp_button_pointer_array[4].element).pointer('close'); // ... and close it
        jQuery('#' + wp_button_pointer_array[5].element).pointer('close'); // ... and close it
        var pointerid = parseInt(openid) + 1;
      } else {
        var pointerid = 1; // ... otherwise we want to open the first pointer
      }

      if (wp_button_pointer_array[pointerid] != undefined) { // check if next pointer exists
        jQuery('#' + wp_button_pointer_array[1].element).pointer(wp_button_pointer_array[1].options).pointer('open');
        var nextid = pointerid + 1;
      }
    }
  });

  jQuery('body').on("click", ".wplc-agent-info", function(e) {
    e.preventDefault();
    if (typeof(jQuery().pointer) != 'undefined') { // make sure the pointer class exists

      if (jQuery('.wp-pointer').is(":visible")) { // if a pointer is already open...
        var openid = jQuery('.wp-pointer:visible').attr("id").replace('wp-pointer-', ''); // ... note its id
        jQuery('#' + wp_button_pointer_array[1].element).pointer('close'); // ... and close it
        jQuery('#' + wp_button_pointer_array[3].element).pointer('close'); // ... and close it
        jQuery('#' + wp_button_pointer_array[4].element).pointer('close'); // ... and close it
        jQuery('#' + wp_button_pointer_array[5].element).pointer('close'); // ... and close it
        var pointerid = parseInt(openid) + 1;
      } else {
        var pointerid = 1; // ... otherwise we want to open the first pointer
      }

      if (wp_button_pointer_array[pointerid] != undefined) { // check if next pointer exists
        jQuery('#' + wp_button_pointer_array[2].element).pointer(wp_button_pointer_array[2].options).pointer('open');
        var nextid = pointerid + 1;
      }
    }
  });

  jQuery('body').on("click", ".wplc_second_chat_request", function(e) {
    e.preventDefault();
    if (typeof(jQuery().pointer) != 'undefined') { // make sure the pointer class exists

      if (jQuery('.wp-pointer').is(":visible")) { // if a pointer is already open...
        var openid = jQuery('.wp-pointer:visible').attr("id").replace('wp-pointer-', ''); // ... note its id
        jQuery('#' + wp_button_pointer_array[2].element).pointer('close'); // ... and close it
        jQuery('#' + wp_button_pointer_array[1].element).pointer('close'); // ... and close it
        jQuery('#' + wp_button_pointer_array[4].element).pointer('close'); // ... and close it
        jQuery('#' + wp_button_pointer_array[5].element).pointer('close'); // ... and close it
        var pointerid = parseInt(openid) + 1;
      } else {
        var pointerid = 1; // ... otherwise we want to open the first pointer
      }

      if (wp_button_pointer_array[pointerid] != undefined) { // check if next pointer exists
        jQuery('.' + wp_button_pointer_array[3].element).pointer(wp_button_pointer_array[3].options).pointer('open');
        var nextid = pointerid + 1;
      }
    }
  });

  jQuery('body').on("mouseenter", ".chatTransferUps", function(e) {
    e.preventDefault();

    if (typeof(jQuery().pointer) != 'undefined') { // make sure the pointer class exists
      if (jQuery('.wp-pointer').is(":visible")) { // if a pointer is already open...
        jQuery('#' + wp_button_pointer_array[5].element).pointer('close'); // ... and close it
      }
    }

    if (typeof(jQuery().pointer) != 'undefined') { // make sure the pointer class exists

      if (jQuery('.wp-pointer').is(":visible")) { // if a pointer is already open...
        var openid = jQuery('.wp-pointer:visible').attr("id").replace('wp-pointer-', ''); // ... note its id
        jQuery('#' + wp_button_pointer_array[2].element).pointer('close'); // ... and close it
        jQuery('#' + wp_button_pointer_array[1].element).pointer('close'); // ... and close it
        jQuery('#' + wp_button_pointer_array[3].element).pointer('close'); // ... and close it
        jQuery('#' + wp_button_pointer_array[5].element).pointer('close'); // ... and close it
        var pointerid = parseInt(openid) + 1;
      } else {
        var pointerid = 1; // ... otherwise we want to open the first pointer
      }

      if (wp_button_pointer_array[pointerid] != undefined) { // check if next pointer exists
        jQuery('.' + wp_button_pointer_array[4].element).pointer(wp_button_pointer_array[4].options).pointer('open');
        var nextid = pointerid + 1;
      }
    }
  });

  /*jQuery('body').on("mouseleave", ".chatTransferUps", function (e) {
      e.preventDefault(); 
      if(typeof(jQuery().pointer) != 'undefined') { // make sure the pointer class exists
          if(jQuery('.wp-pointer').is(":visible")) { // if a pointer is already open...
              jQuery('#' + wp_button_pointer_array[4].element).pointer('close'); // ... and close it
          } 
      }
  });  */

  jQuery('body').on("mouseover", ".chatDirectUserToPageUps", function(e) {
    e.preventDefault();

    if (typeof(jQuery().pointer) != 'undefined') { // make sure the pointer class exists
      if (jQuery('.wp-pointer').is(":visible")) { // if a pointer is already open...
        jQuery('#' + wp_button_pointer_array[4].element).pointer('close'); // ... and close it
      }
    }

    if (typeof(jQuery().pointer) != 'undefined') { // make sure the pointer class exists

      if (jQuery('.wp-pointer').is(":visible")) { // if a pointer is already open...
        var openid = jQuery('.wp-pointer:visible').attr("id").replace('wp-pointer-', ''); // ... note its id
        jQuery('#' + wp_button_pointer_array[2].element).pointer('close'); // ... and close it
        jQuery('#' + wp_button_pointer_array[1].element).pointer('close'); // ... and close it
        jQuery('#' + wp_button_pointer_array[3].element).pointer('close'); // ... and close it
        jQuery('#' + wp_button_pointer_array[4].element).pointer('close'); // ... and close it
        var pointerid = parseInt(openid) + 1;
      } else {
        var pointerid = 1; // ... otherwise we want to open the first pointer
      }

      if (wp_button_pointer_array[pointerid] != undefined) { // check if next pointer exists
        jQuery('.' + wp_button_pointer_array[5].element).pointer(wp_button_pointer_array[5].options).pointer('open');
        var nextid = pointerid + 1;
      }
    }
  });

  jQuery('body').on("mouseleave", ".chatDirectUserToPageUps", function(e) {
    /*e.preventDefault(); 
    if(typeof(jQuery().pointer) != 'undefined') { // make sure the pointer class exists
        if(jQuery('.wp-pointer').is(":visible")) { // if a pointer is already open...
            jQuery('#' + wp_button_pointer_array[5].element).pointer('close'); // ... and close it
        } 
    }*/
  });

  (function($) {
    var MutationObserver = window.MutationObserver || window.WebKitMutationObserver || window.MozMutationObserver;
    if (MutationObserver) {
      var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
          if (!$(mutation.target).hasClass("open")) {
            for (var pointer_id in wp_button_pointer_array) {
              try {
                $('#' + wp_button_pointer_array[pointer_id].element).pointer("close");
              } catch (e) {
                // The pointer may not be initialized, fail silently if that is the case
              }
            }
          }
        });
      });

      var intervalID;
      intervalID = setInterval(function(event) {
        var elems = $("#chat_area .dropdown.inchat-menu.pull-right");
        if (elems.length) {
          observer.observe(elems[0], {
            attributes: true
          });
          clearInterval(intervalID);
        }
      }, 1000);
    }
  })(jQuery);

});