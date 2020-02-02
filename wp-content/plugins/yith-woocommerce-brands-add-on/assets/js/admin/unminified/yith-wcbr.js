/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

jQuery( document ).ready( function( $ ){
    var file_frame = [],
        brands_taxonomy = $( '#yith_wcbr_brands_taxonomy' ),
        brands_taxonomy_rewrite = $( '#yith_wcbr_brands_taxonomy_rewrite' ),
        use_logo_default = $( '#yith_wcbr_use_logo_default' ),
        logo_default = $( '#yith_wcbr_logo_default' ),
        single_product_brands_position = $( '#yith_wcbr_single_product_brands_position' ),
        single_product_brands_content = $( '#yith_wcbr_single_product_brands_content' ),
        loop_product_brands_position = $( '#yith_wcbr_loop_product_brands_position' ),
        loop_product_brands_content = $( '#yith_wcbr_loop_product_brands_content' );

    // handles upload image
    $( '.yith_wcbr_upload_image_button').on( 'click', function( event ){
        var t = $(this),
            id = t.attr('id');

        event.preventDefault();

        // If the media frame already exists, reopen it.
        if ( file_frame[id] ) {
            file_frame[id].open();
            return;
        }

        // Create the media frame.
        file_frame[id] = wp.media.frames.downloadable_file = wp.media( {
            title: yith_wcbr.labels.upload_file_frame_title,
            button: {
                text: yith_wcbr.labels.upload_file_frame_button
            },
            multiple: false
        } );

        // When an image is selected, run a callback.
        file_frame[id].on( 'select', function() {
            attachment = file_frame[id].state().get( 'selection' ).first().toJSON();

            t.prev().val( attachment.id );
            t.parent().prev().find( 'img' ).attr( 'src', attachment.sizes.thumbnail.url );
            t.next().show();
        } );

        // Finally, open the modal.
        file_frame[id].open();
    } );

    // handles remove image
    $( '.yith_wcbr_remove_image_button').on( 'click', function( event ){
        var t = $(this);

        event.preventDefault();

        t.siblings('input').val('');
        t.parent().prev().find( 'img' ).attr( 'src', yith_wcbr.wc_placeholder_img_src );
        t.hide();
        return false;
    } );

    // hide remove button when not needed
    $( '.yith_wcbr_upload_image_id' ).each( function(){
        var t = $(this);

        if( ! t.val() || t.val() == '0' ){
            t.siblings( '.yith_wcbr_remove_image_button').hide();
        }
    } );

    // handle panel dependencies
    brands_taxonomy.on( 'change', function(){
        var t = $(this);

        if( t.val() == 'yith_product_brand' ){
            brands_taxonomy_rewrite.parents( 'tr' ).show();
        }
        else{
            brands_taxonomy_rewrite.parents( 'tr' ).hide();
        }
    }).change();

    use_logo_default.on( 'change', function(){
        var t = $(this);

        if( t.is( ':checked' ) ){
            logo_default.parents( 'tr' ).show();
        }
        else{
            logo_default.parents( 'tr' ).hide();
        }
    }).change();

    single_product_brands_position.on( 'change', function(){
        var t = $(this);

        if( t.val() == 'none' ){
            single_product_brands_content.parents('tr').hide();
        }
        else{
            single_product_brands_content.parents('tr').show();
        }
    }).change();

    loop_product_brands_position.on( 'change', function(){
        var t = $(this);

        if( t.val() == 'none' ){
            loop_product_brands_content.parents('tr').hide();
        }
        else{
            loop_product_brands_content.parents('tr').show();
        }
    }).change();

    // remove duplicated product_cat thumbnail form
    $( '#product_cat_thumbnail').parents('.form-field').remove();
} );