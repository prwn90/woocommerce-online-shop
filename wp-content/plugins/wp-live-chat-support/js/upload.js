jQuery(function() {

var clicked_on_imgbtn = false;
var clicked_on_logobtn = false;


jQuery('#wplc_btn_upload_pic').click(function() {
    
 formfield = jQuery('#wplc_upload_pic').attr('name');
 clicked_on_imgbtn = true;
 tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
 return false;
});
jQuery('#wplc_btn_upload_logo').click(function() {
    
 formfield = jQuery('#wplc_upload_logo').attr('name');
 clicked_on_logobtn = true;
 tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
 return false;
});
jQuery('#wplc_btn_upload_icon').click(function() {

    formfield = jQuery('#wplc_upload_icon').attr('name');
    clicked_on_logobtn = true;
    tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
    return false;
});

window.send_to_editor = function(html) {
 imgurl = jQuery('img',html).attr('src');
 if (clicked_on_imgbtn) { jQuery('#wplc_upload_pic').val(imgurl); jQuery("#wplc_pic_area").html("<img src=\""+imgurl+"\"  />"); }
 if (clicked_on_logobtn) { jQuery('#wplc_upload_logo').val(imgurl); jQuery("#wplc_logo_area").html("<img src=\""+imgurl+"\" />"); }
 
 tb_remove();
}

});