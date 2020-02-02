/**
 * wplc_u_admin_chatbox_ui_events.js is a controller for the UI related actions that happen 
 * inside the chatbox of the admin area.
 */

function WPLC_U_Admin_Chatbox_UI_Events() {
	this._initialized = false;
}

WPLC_U_Admin_Chatbox_UI_Events.prototype = {

    TIMESTAMP_HTML_ATTR: "mid",

	SELECTORS : {
        wplc_u_admin_chatbox_msg_body: " .wplc-msg-content",
        wplc_u_admin_chatbox_user_msg: " .wplc-user-message .wplc-msg-content",
        wplc_u_admin_chatbox_admin_msg: " .wplc-admin-message .wplc-msg-content",
        wplc_u_admin_chatbox_msg: " .wplc-admin-message, .wplc-user-message",
        wplc_u_admin_chatbox_msg_btn_edit: " .tcx-edit-message"
    },
    
    CLASSES: {
        POPUP_TIMESTAMP: "wplc-chatbox-msg-timestamp",
        ONLY_HOURS: "only-hours"
    },

    showDate: false,
    showTime: false,
    showName: false,
    showAvatar: false,
    userName: "",

    getClassSelector: function(className) {
        try {
            return "." + this.CLASSES.POPUP_TIMESTAMP;
        } catch(err) {
            //console.log("Failed to getSelectorFromClass" + err);
        }
    }, 

    getTimestampPop: function(dateTime) {
        try {
            
            if (this.showDate || this.showTime) {

                if (this.showDate && this.showTime) {
                    return '<span class="'+ this.CLASSES.POPUP_TIMESTAMP +'">' + jQuery(dateTime).text() + '</span>';
                } else {
                    return '<span class="'+ this.CLASSES.POPUP_TIMESTAMP +' '+ this.CLASSES.ONLY_HOURS +'">' + jQuery(dateTime).text() + '</span>';
                }
            }
        } catch(err) {
			//console.log("Failed to getTimestampPop " + err.message);
		}
    },

    /**
     * This method append and displays the Timestamp of a message sent by a Chat Agent or a User.
     * 
     * @param {*} message HTML object that contains a msg from a User or a Chat Agent
     */
	displayMsgTimeStamp: function(message) {
		try {
            var msgTimeStamp = jQuery(message).parent().next();

            if (msgTimeStamp !== undefined && msgTimeStamp !== '') {
                var popupTimeStamp = this.getTimestampPop(msgTimeStamp);
                jQuery(message).prepend(popupTimeStamp);
            }
		} catch(err) {
			//console.log("Failed to displayMsgTimeStamp " + err);
		}
    },
    
    /**
     * This method simply displays the edit button which is within a message.
     * 
     * @param {*} message HTML object that contains a msg from a User or a Chat Agent
     */
	displayEditBtn: function(message) {
		try {
            
            var editMsgBtn = jQuery(message).parent().find(this.SELECTORS.wplc_u_admin_chatbox_msg_btn_edit)



                if (editMsgBtn !== undefined && editMsgBtn !== '') {
                    jQuery(editMsgBtn).css("display", "inline-block");
                }

            
		} catch(err) {
			//console.log("Failed to displayEditBtn " + err);
		}
	},

    /**
     * This method removes the timestapm
     * 
     * @param {*} message HTML object that contains a msg from a User or a Chat Agent
     */
    removeMsgTimeStamp: function(message) {
        try {
            jQuery(message).find(this.getClassSelector(this.CLASSES.POPUP_TIMESTAMP)).remove();
		} catch(err) {
			//console.log("Failed to removeMsgTimeStamp " + err);
		}
    },

    /**
     * This method hides the edit button which is within a message
     * 
     * @param {*} message HTML object that contains a msg from a User or a Chat Agent
     */
    hideEditBtn: function(message) {
        try {
            jQuery(message).find(this.SELECTORS.wplc_u_admin_chatbox_msg_btn_edit).css("display", "none");
		} catch(err) {
			//console.log("Failed to removeMsgTimeStamp " + err);
		}
    },

	initEvents: function() {
		try {
			var ctx = this;

            // Add and remove the edit button and the msg timestamp by toggling the hover event over a message
            jQuery(document).on("mouseover", ctx.SELECTORS.wplc_u_admin_chatbox_user_msg, function() {
                ctx.displayMsgTimeStamp(jQuery(this));
                ctx.displayEditBtn(jQuery(this));
            });
            jQuery(document).on("mouseover", ctx.SELECTORS.wplc_u_admin_chatbox_admin_msg, function() {
                ctx.displayMsgTimeStamp(jQuery(this));
                ctx.displayEditBtn(jQuery(this));
            });
            jQuery(document).on("mouseout", ctx.SELECTORS.wplc_u_admin_chatbox_msg, function() {
                ctx.removeMsgTimeStamp(jQuery(this));
                ctx.hideEditBtn(jQuery(this));
            });

		} catch(err) {
			//console.log("Failed to initEvents " + err);
		}
	},
    
    initVars: function() {
        try {
          if (typeof wplc_show_chat_detail != "undefined") {
            this.showDate = wplc_show_chat_detail.date;
            this.showTime = wplc_show_chat_detail.time;
            this.showName = wplc_show_chat_detail.name;
            this.showAvatar = wplc_show_chat_detail.avatar;
          }
        } catch(err) {
            //console.log("Failed to initVars " + err);
        }
    },

	init: function() {
		try {
			var ctx = this;

            jQuery(function() {
                ctx.initEvents();
                ctx.initVars();
			});
		} catch(err) {
			//console.log("Failed to init " + err);
		}
	}

}

var wplcAdminChatboxUi = new WPLC_U_Admin_Chatbox_UI_Events();
wplcAdminChatboxUi.init();