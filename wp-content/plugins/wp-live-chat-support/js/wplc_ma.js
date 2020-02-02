jQuery(function() {

    jQuery("body").on("click","#wplc_sample_ring_tone",function(e) {
        var v = jQuery('#wplc_ringtone option:selected').attr('playurl');
        if (typeof v !== "undefined") {
            new Audio(v).play() 
        }
        e.preventDefault();
    });
    jQuery("body").on("click","#wplc_sample_message_tone",function(e) {
        var v = jQuery('#wplc_messagetone option:selected').attr('playurl');
        if (typeof v !== "undefined") {
            new Audio(v).play()
        }
        e.preventDefault();
    });
    jQuery("body").on("click", "#wplc_add_agent", function(e) {
         e.preventDefault();

        var uid = parseInt(jQuery("#wplc_agent_select").val());
        var em = jQuery("#wplc_selected_agent_"+uid).attr('em');
        var em2 = jQuery("#wplc_selected_agent_"+uid).attr('em2');
        var name = jQuery("#wplc_selected_agent_"+uid).attr('name');
        
        if (uid) {
            var data = {
                action: 'wplc_add_agent',
                security: wplc_admin_strings.nonce,
                uid: uid
            };
            jQuery.post(ajaxurl, data, function(response) {
                if (response === "1") {
                    /* success */
                    var wplchtml = "<li id=\"wplc_agent_li_"+uid+"\"><p><img src=\"//www.gravatar.com/avatar/"+em+"?s=80&d=mm\"></p><h3>"+name+"</h3><small>"+em2+"</small><p><button class='button button-secondary' id='wplc_remove_agent' uid='"+uid+"'>"+wplc_admin_strings.remove_agent+"</button></p></li>"
                    jQuery(wplchtml).insertBefore("#wplc_add_new_agent_box").hide().fadeIn(2000);
                    jQuery("#wplc_selected_agent_"+uid).remove();
                } else {
                    /* failure */
                }
            });
        }

        jQuery(this).hide();
       


    });

     jQuery("body").on("click", ".wplc_remove_agent", function(e) {
        
        var uid = parseInt(jQuery(this).attr('uid'));
        
        if (uid) {
            var data = {
                action: 'wplc_remove_agent',
                security: wplc_admin_strings.nonce,
                uid: uid
            };
            jQuery.post(ajaxurl, data, function(response) {
                if (response === "1") {
                    /* success */
                   
                    jQuery("#wplc_agent_li_"+uid).fadeOut(500);
                } else {
                    /* failure */
                }
            });
        }
        e.preventDefault();


    });
	
	// Basic validation for GDPR search form
	jQuery("#gdprSearchForm").submit(function(e) {
		e.preventDefault();
		var valid = true;
		var termValue = jQuery("input[name='term']").val();
		if(termValue.trim().length === 0) {
				alert("The search term cannot be empty");
				valid = false;
		}else if(termValue.trim().length > 0 && termValue.trim().length < 3) {
				alert("The search term must be longer than 2 characters");
				valid = false;
		}
		if(valid) {
				jQuery(this).unbind('submit').submit();
		}
	});
	
	// Custom Fields Basic Validation
	jQuery(".wplc_custom_field_form").submit(function(e) {
		e.preventDefault();
		var valid = true;
		var validationError = "";
		var fieldTitleValue = jQuery("input[name='wplc_field_name']").val();
		var wplc_field_type_selected = jQuery("#wplc_field_type").val();
		if(fieldTitleValue.trim().length === 0) {
			validationError="Custom field name cannot be empty\n";
			valid = false;
		}
		if(wplc_field_type_selected == 0) {
			var wplc_get_text_value = jQuery("input[name='wplc_field_value']").val();
			if(wplc_get_text_value.trim().length === 0) {
				validationError+="Custom field text value cannot be empty\n";
				valid = false;
			}
		}else if(wplc_field_type_selected == 1) {
			var wplc_get_drop_down_values = jQuery("textarea[name='wplc_drop_down_values']").val();
			if(wplc_get_drop_down_values.trim().length === 0) {
				validationError+="Custom field drop down value cannot be empty\n";
				valid = false;
			}
		}
		if(valid) {
			jQuery(this).unbind('submit').submit();
		}else {
			alert(validationError);
		}
	});	
	

	// Custom Fields selection
	jQuery("#wplc_field_type").change(function(){
      var wplc_field_type_selected = jQuery(this).val();
		if(wplc_field_type_selected == 0) {
			jQuery("#wplc_field_value_row").show();
			jQuery("#wplc_field_value_dropdown_row").hide();
		}else if(wplc_field_type_selected == 1) {
			jQuery("#wplc_field_value_row").hide();
			jQuery("#wplc_field_value_dropdown_row").show();
		}
     });

 });