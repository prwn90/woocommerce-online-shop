jQuery("body").on("change","#wplc_environment", function() {
  var selection = jQuery(this).val();
  jQuery("#wplc_iterations").attr('readonly', selection>0);
  jQuery("#wplc_delay_between_loops").attr('readonly', selection>0);
       
  if (selection === '1') {
      /* Shared hosting - low level plan */
      jQuery("#wplc_iterations").val(20);
      jQuery("#wplc_delay_between_loops").val(1000);
  }
  else if (selection === '2') {
      /* Shared hosting - normal plan */
      jQuery("#wplc_iterations").val(55);
      jQuery("#wplc_delay_between_loops").val(500);
  }
  else if (selection === '3') {
      /* VPS */
      jQuery("#wplc_iterations").val(60);
      jQuery("#wplc_delay_between_loops").val(400);
  }
  else if (selection === '4') {
      /* Dedicated server */
      jQuery("#wplc_iterations").val(200);
      jQuery("#wplc_delay_between_loops").val(250);
  }
})


jQuery(document).ready(function($) {


  $("#wplc_chatbox_height").change(function(){
    $("#wplc_chatbox_absolute_height_span").hide();
    if ($(this).val()==0) {
      $("#wplc_chatbox_absolute_height_span").show();
    }
  });
	$("#wplc_new_server_token_btn").on("click",function(){
		$(this).addClass("disabled");
		var extText = $(this).text();
		$(this).text("Generating...");
		var data = {
			'action' : 'wplc_generate_new_node_token',
			'nonce': $("#wplc_new_server_token_nonce").val()      
    };
    
		jQuery.post(ajax_object.ajax_url, data, function(response) {
      if (response) {
        try {
          var res=JSON.parse(response);
          if (res.error) {
            $("#wplc_server_token_error").text(res.error);
          } else {
            $("#wplc_node_token_input").val(res.key);
          }
        } catch (error) {
          $("#wplc_server_token_error").text(error.message);
        }
      } else {
        $("#wplc_server_token_error").text('Request failed');
      }
			$("#wplc_new_server_token_btn").removeClass("disabled");
			$("#wplc_new_server_token_btn").text(extText);
		});    
  });
  
	$("#wplc_new_encryption_key_btn").on("click",function(){
		$(this).addClass("disabled");
		var extText = $(this).text();
    $(this).text("Generating...");
    $("#wplc_new_encryption_key_error").text('');
		var data = {
			'action' : 'wplc_generate_new_encryption_key',
			'nonce': $("#wplc_encryption_key_nonce").val()      
    };
    jQuery.post(ajax_object.ajax_url, data, function(response) {
      if (response) {
        try {
          var res=JSON.parse(response);
          if (res.error) {
            $("#wplc_new_encryption_key_error").text(res.error);
          } else {
            $("#wplc_encryption_key").val(res.key);
          }
        } catch (error) {
          $("#wplc_new_encryption_key_error").text(error.message);
        }
      } else {
        $("#wplc_new_encryption_key_error").text('Request failed');
      }
			$("#wplc_new_encryption_key_btn").removeClass("disabled");
			$("#wplc_new_encryption_key_btn").text(extText);
		});
	});  

	$("#wplc_new_secret_token_btn").on("click",function(){
		$(this).addClass("disabled");
		var extText = $(this).text();
    $(this).text("Generating...");
    $("#wplc_secret_token_error").text('');
		var data = {
			'action' : 'wplc_new_secret_key',
			'nonce': $("#wplc_new_secret_token_nonce").val()      
		};

		jQuery.post(ajax_object.ajax_url, data, function(response) {
      if (response) {
        try {
          var res=JSON.parse(response);
          if (res.error) {
            $("#wplc_secret_token_error").text(res.error);
          } else {
            $("#wplc_secret_token_input").val(res.key);
          }
        } catch (error) {
          $("#wplc_secret_token_error").text(error.message);
        }
      } else {
        $("#wplc_secret_token_error").text('Request failed');
      }
			$("#wplc_new_secret_token_btn").removeClass("disabled");
			$("#wplc_new_secret_token_btn").text(extText);
		});
	});
	
	
});