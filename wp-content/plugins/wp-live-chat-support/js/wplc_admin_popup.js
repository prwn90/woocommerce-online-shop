(function($) {
	
	$(function(event) {
		var container = "#wpbody";

		if($("#editor.gutenberg__editor").length)
			container = "#editor.gutenberg__editor";

		$(".floating-right-toolbar i").draggable({
			snap: container,
			containment: container // This prevents the icon from being dragged into the header, where it becomes invisible
		});
	});
	
})(jQuery);