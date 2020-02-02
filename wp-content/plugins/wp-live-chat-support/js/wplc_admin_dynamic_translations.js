jQuery(function(){
	jQuery(document).on("tcx_dom_ready", function(e) {
		if(typeof wplc_dynamic_translation_array !== "undefined"){
			for(var index in wplc_dynamic_translation_array){
				jQuery("#" + index).html(wplc_dynamic_translation_array[index]);
			}
		}
	});
});