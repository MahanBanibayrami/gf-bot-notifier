<?php
/**
 * Uninstall: remove all plugin data (options, log table, scheduled events).
 *
 * @package GF_Bot_Notifier
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$gfbn_cleanup = function () {
	global $wpdb;
	foreach ( array( 'gfbn_settings', 'gfbn_conn_status', 'gfbn_db_version', 'gfbn_log', 'gfbn_queue' ) as $option ) {
		delete_option( $option );
	}
	delete_transient( 'gfbn_rl_telegram' );
	delete_transient( 'gfbn_rl_rubika' );
	wp_clear_scheduled_hook( 'gfbn_housekeeping' );
	wp_unschedule_hook( 'gfbn_process_job' );
	wp_unschedule_hook( 'gfbn_retry' );
	$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'gfbn_logs' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared
};

if ( is_multisite() ) {
	foreach ( get_sites( array( 'fields' => 'ids', 'number' => 0 ) ) as $gfbn_blog_id ) {
		switch_to_blog( $gfbn_blog_id );
		$gfbn_cleanup();
		restore_current_blog();
	}
} else {
	$gfbn_cleanup();
}
