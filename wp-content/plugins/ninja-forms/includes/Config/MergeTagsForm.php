<?php if ( ! defined( 'ABSPATH' ) ) exit;

return apply_filters( 'ninja_forms_merge_tags_form', array(

   /*
   |--------------------------------------------------------------------------
   | Form ID
   |--------------------------------------------------------------------------
   */

	'form_id' => array(
		'id' => 'form_id',
		'tag' => '{form:id}',
		'label' => __( 'Form ID', 'ninja_forms' ),
		'callback' => 'get_form_id',
	),

    /*
    |--------------------------------------------------------------------------
    | Form Title
    |--------------------------------------------------------------------------
    */

    'form_title' => array(
        'id' => 'form_title',
        'tag' => '{form:title}',
        'label' => __( 'Form Title', 'ninja_forms' ),
        'callback' => 'get_form_title',
    ),

    /*
    |--------------------------------------------------------------------------
    | Submission Sequence Number
    |--------------------------------------------------------------------------
    */

    'sub_seq' => array(
        'id' => 'sub_seq',
        'tag' => '{submission:sequence}',
        'label' => __( 'Sub Sequence', 'ninja_forms' ),
        'callback' => 'getSubSeq',
    ),

    /*
    |--------------------------------------------------------------------------
    | Submission Count
    |--------------------------------------------------------------------------
    */

    'sub_count' => array(
        'id' => 'sub_count',
        'tag' => '{submission:count}',
        'label' => __( 'Submission Count', 'ninja_forms' ),
        'callback' => 'get_sub_count',
    ),

    /*
     |--------------------------------------------------------------------------
     | Display ONLY
     |--------------------------------------------------------------------------
     | These merge tags are for display only and are processed elsewhere.
     */

    'all_fields_table' => array(
        'id' => 'all_fields_table',
        'tag' => '{all_fields_table}',
        'label' => __( 'All Fields Table', 'ninja_forms' ),
        'callback' => '',
    ),

    'fields_table' => array(
        'id' => 'fields_table',
        'tag' => '{fields_table}',
        'label' => __( 'Fields Table', 'ninja_forms' ),
        'callback' => '',
    ),

));