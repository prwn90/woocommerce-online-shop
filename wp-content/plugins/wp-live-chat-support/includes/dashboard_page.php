<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}
?>

<script>
  var nifty_api_key = '<?php echo wplc_node_server_token_get(); ?>';
</script>

<?php
  $user = wp_get_current_user();
  $total_count =  $total_2day_chat_count = $total_30day_chat_count = $total_60day_chat_count = $total_90day_chat_count = $total_2day_missed_chat_count =  $total_30day_missed_chat_count = $total_60day_missed_chat_count = $total_90day_missed_chat_count = 0;

  global $wpdb;
  global $wplc_tblname_chats;
  $sql = "SELECT  COUNT(IF(`timestamp` > CURDATE() AND `agent_id` <> 0 AND `status` != 0, 1, null)) AS today,
                  COUNT(IF(`timestamp` > DATE_SUB(NOW(), INTERVAL 30 DAY) AND `agent_id` <> 0 AND `status` != 0, 1, null)) AS day30,
	              COUNT(IF(`timestamp` > DATE_SUB(NOW(), INTERVAL 60 DAY) AND `agent_id` <> 0 AND `status` != 0, 1, null)) AS day60,
                  COUNT(IF(`timestamp` > DATE_SUB(NOW(), INTERVAL 90 DAY) AND `agent_id` <> 0 AND `status` != 0, 1, null)) AS day90,
				  COUNT(IF(`timestamp` > CURDATE() AND `status` = 0, 1, null)) AS missedtoday,
                  COUNT(IF(`timestamp` > DATE_SUB(NOW(), INTERVAL 30 DAY) AND `status` = 0, 1, null)) AS missed30,
	   			  COUNT(IF(`timestamp` > DATE_SUB(NOW(), INTERVAL 60 DAY) AND `status` = 0, 1, null)) AS missed60,
        		  COUNT(IF(`timestamp` > DATE_SUB(NOW(), INTERVAL 90 DAY) AND `status` = 0, 1, null)) AS missed90 
          FROM `$wplc_tblname_chats`";
  $results = $wpdb->get_row( $sql );

  if ($results) {
	$total_2day_chat_count = $results->today;
	$total_30day_chat_count = $results->day30;
	$total_60day_chat_count = $results->day60;
	$total_90day_chat_count = $results->day90;
	$total_2day_missed_chat_count = $results->missedtoday;
	$total_30day_missed_chat_count = $results->missed30;
	$total_60day_missed_chat_count = $results->missed60;
	$total_90day_missed_chat_count = $results->missed90;
  } 

?>

<div class="wrap wplc_wrap">
  <div class="wplc_dashboard_container">
    <div class="wplc_dashboard_row">
      <div class="wplc_panel_col wplc_col_12">
        <div class="wplc_panel">
			<div class="wplc_panel_heading"><i class="fa fa-tasks" aria-hidden="true"></i> <?php _e("Dashboard",'wp-live-chat-support'); ?></div>
          <div class="wplc_material_panel">
			  <div class="wplc_dashboard_activity">
              <h3><?php printf( __( 'Hi %s! Current activity: %s Active Visitor(s) and %s Active Agent(s).', 'wp-live-chat-support'), $user->display_name, '<span id=\'totalVisitors\' class="wplc_dashboard_activity_numbers">...</span>', '<span class="wplc_dashboard_activity_numbers">'.wplc_get_online_agent_users_count().'</span>'); ?></h3>
			  </div>
			<div class="wplc_panel_col wplc_dashboard_buttons_outer">
				<div class="wplc_dashboard_button_item">
					<a href="<?php echo admin_url('admin.php?page=wplivechat-menu');?>"><i class="fa fa-comments" aria-hidden="true"></i> <?php _e("Chats",'wp-live-chat-support'); ?></a>
					<a href="<?php echo admin_url('admin.php?page=wplivechat-menu-missed-chats');?>"><i class="fa fa-user-times" aria-hidden="true"></i> <?php _e("Missed",'wp-live-chat-support'); ?></a>
					<a href="<?php echo admin_url('admin.php?page=wplivechat-menu-history');?>"><i class="far fa-clock" aria-hidden="true"></i> <?php _e("History",'wp-live-chat-support'); ?></a>
					<a href="<?php echo admin_url('admin.php?page=wplivechat-menu-reporting');?>"><i class="fa fa-chart-area" aria-hidden="true"></i> <?php _e("Reports",'wp-live-chat-support'); ?></a>
					<a href="<?php echo admin_url('admin.php?page=wplivechat-menu-offline-messages');?>"><i class="fa fa-ellipsis-h" aria-hidden="true"></i> <?php _e("Offline Messages",'wp-live-chat-support'); ?></a>
					<a href="<?php echo admin_url('edit.php?post_type=wplc_quick_response');?>"><i class="fa fa-paper-plane" aria-hidden="true"></i> <?php _e("Quick Responses",'wp-live-chat-support'); ?></a>
					<a href="<?php echo admin_url('admin.php?page=wplivechat-menu-at');?>"><i class="fa fa-code-branch" aria-hidden="true"></i> <?php _e("Tools",'wp-live-chat-support'); ?></a>
					<a href="<?php echo admin_url('admin.php?page=wplivechat-menu-settings');?>"><i class="fa fa-cog" aria-hidden="true"></i> <?php _e("Settings",'wp-live-chat-support'); ?></a>
				</div>
			</div>
            <div class="wplc_panel_col wplc-center wplc-dashboard-stats">
				<table width="100%" border="0" cellspacing="0" cellpadding="0" class="wplc_dashboard_stats_table">
				  <tbody>
					<tr>
					  <th width="18%"><div class="wplc-dashboard-stats-title"><?php _e("Chats",'wp-live-chat-support'); ?></div></th>
					  <th width="19%" align="center"><div class="wplc-dashboard-stats-sub-title"><?php _e("Missed",'wp-live-chat-support'); ?></div></th>
					  <th width="19%" align="center"><div class="wplc-dashboard-stats-sub-title"><?php _e("Engaged",'wp-live-chat-support'); ?></div></th>
					  <th width="19%" align="center"><div class="wplc-dashboard-stats-sub-title"><?php _e("Total",'wp-live-chat-support'); ?></div></th>
					</tr>
					<tr>
						<td height="20" align="right"><div class="wplc-dashboard-stats-side-title"><?php _e("Today",'wp-live-chat-support'); ?></div></td>
					  <td align="center"><?php echo $total_2day_missed_chat_count; ?></td>
					  <td align="center"><?php echo $total_2day_chat_count; ?></td>
					  <td align="center"><?php echo ($total_2day_chat_count+$total_2day_missed_chat_count); ?></td>
					</tr>
					<tr>
					  <td align="right"><div class="wplc-dashboard-stats-side-title"><?php _e("Last 30 days",'wp-live-chat-support'); ?></div></td>
					  <td align="center"><?php echo $total_30day_missed_chat_count; ?></td>
					  <td align="center"><?php echo $total_30day_chat_count; ?></td>
					  <td align="center"><?php echo ($total_30day_missed_chat_count+$total_30day_chat_count); ?></td>
					</tr>
					<tr>
					  <td align="right"><div class="wplc-dashboard-stats-side-title"><?php _e("Last 60 days",'wp-live-chat-support'); ?></div></td>
					  <td align="center"><?php echo $total_60day_missed_chat_count; ?></td>
					  <td align="center"><?php echo $total_60day_chat_count; ?></td>
					  <td align="center"><?php echo ($total_60day_missed_chat_count+$total_60day_chat_count); ?></td>
					</tr>
					<tr>
					  <td align="right"><div class="wplc-dashboard-stats-side-title"><?php _e("Last 90 days",'wp-live-chat-support'); ?></div></td>
					  <td align="center"><?php echo $total_90day_missed_chat_count; ?></td>
					  <td align="center"><?php echo $total_90day_chat_count; ?></td>
					  <td align="center"><?php echo ($total_90day_chat_count+$total_90day_missed_chat_count); ?></td>
					</tr>
				  </tbody>
				</table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="wplc_dashboard_row">
      <div class="wplc_panel_col wplc_col_12">
        <div class="wplc_panel">
		  <div class="wplc_panel_heading"><i class="far fa-newspaper" aria-hidden="true"></i> <?php _e("Latest News",'wp-live-chat-support'); ?></div>
          <div id="wplc_blog_posts" class="wplc_material_panel">
			<?php
			  	wplc_fetch_feed();
			 ?>
		  </div>
        </div>
      </div>
    </div>
    
  </div>
</div>