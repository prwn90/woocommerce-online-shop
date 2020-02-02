<?php

if ( ! defined ( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

defined ( 'YITH_YWAR_POST_TYPE' ) || define ( 'YITH_YWAR_POST_TYPE', 'ywar_reviews' );

defined ( 'YITH_YWAR_META_REVIEW_USER_ID' ) || define ( 'YITH_YWAR_META_REVIEW_USER_ID', '_ywar_review_user_id' );
defined ( 'YITH_YWAR_META_REVIEW_AUTHOR' ) || define ( 'YITH_YWAR_META_REVIEW_AUTHOR', '_ywar_review_author' );
defined ( 'YITH_YWAR_META_REVIEW_AUTHOR_EMAIL' ) || define ( 'YITH_YWAR_META_REVIEW_AUTHOR_EMAIL', '_ywar_review_author_email' );
defined ( 'YITH_YWAR_META_REVIEW_AUTHOR_URL' ) || define ( 'YITH_YWAR_META_REVIEW_AUTHOR_URL', '_ywar_review_author_url' );
defined ( 'YITH_YWAR_META_REVIEW_AUTHOR_IP' ) || define ( 'YITH_YWAR_META_REVIEW_AUTHOR_IP', '_ywar_review_author_IP' );

if ( ! function_exists ( "yith_define" ) ) {
    /**
     * Defined a constant if not already defined
     *
     * @param string $name  The constant name
     * @param mixed  $value The value
     */
    function yith_define ( $name, $value = true ) {
        if ( ! defined ( $name ) ) {
            define ( $name, $value );
        }
    }
}
