<?php

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

/**
 * Get database from remote.
 *
 * @when before_wp_load
 */
$wpcli_db_get_command = dirname( __FILE__ ) . '/src/DB_Get_Command.php';
if ( file_exists( $wpcli_db_get_command ) ) {
	require_once $wpcli_db_get_command;
}
WP_CLI::add_command( 'db get', 'Camaleaun\DB_Get_Command' );
