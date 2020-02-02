<?php
if (!defined('ABSPATH')) {
  exit;
}
add_filter("init", "wplc_add_shortcode", 10, 4);

function wplc_add_shortcode()
{
  add_shortcode('wplc_live_chat', 'wplc_live_chat_box_shortcode');
}

function wplc_live_chat_box_shortcode($atts, $content = null)
{
  $get_gutenberg_options = get_option('wplc_gutenberg_settings');
  $wplc_settings = wplc_get_options();
  if (isset($_COOKIE['wplc_cid']) && wplc_check_user_request($_COOKIE['wplc_cid'])) {
    $cid = intval($_COOKIE['wplc_cid']);
  } else {
    $cid = null;
  }
  $wplc_chat_box_content = wplc_theme_control_function($wplc_settings, wplc_agent_is_available(), $wplc_settings['wplc_using_localization_plugin'], $cid);
  if (!$get_gutenberg_options['wplc_gutenberg_enable']) {
    return;
  }

  // get attributes
  $atts = shortcode_atts(array('style' => 'normal'), $atts, 'wplc_live_chat');

  $output = '<div class="wplc_live_chat_support_shortcode wplc_' . esc_attr($atts['style']) . '">';
  $output  .= $wplc_chat_box_content;
  $output .= '</div>';
  return $output;
}
