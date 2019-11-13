<?php

/**
 * Get database from alias.
 */

namespace Camaleaun;

use WP_CLI;
use WP_CLI\Utils;

/**
 * Get database from alias.
 *
 * ## OPTIONS
 *
 * <alias>
 * : Key of the alias.
 *
 * ## EXAMPLES
 *
 *     $ wp db get @prod
 *     Success: Database received.
 *
 * @when before_wp_load
 */
class DB_Get_Command {

	/**
	 * Get database from alias.
	 *
	 * ## OPTIONS
	 *
	 * <alias>
	 * : The alias of remote.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp db get @prod
	 *     Success: Database received.
	 *
	 * @when before_wp_load
	 */
	public function __invoke( $args, $assoc_args ) {
		list( $alias ) = $args;

		$from_dir = trim( WP_CLI\Utils\get_flag_value( $args, 1, '..' ), '/' );
		$to_dir   = $from_dir;
		if ( preg_match( '/^\./', $to_dir ) ) {
			$to_dir = $this->site_dir();
		} else {
			$to_dir = WP_CLI\Utils\trailingslashit( $this->site_dir() ) . $to_dir;
		}
		$from_dir = WP_CLI\Utils\trailingslashit( $from_dir );

		$filename = 'mysql.sql';

		$ssh = (object) $this->get_alias_ssh( $alias );

		$scp = sprintf(
			'scp -P %4$s %2$s@%3$s:%5$s%6$s%1$s %7$s 2>&',
			$filename,
			$ssh->user,
			$ssh->host,
			isset( $ssh->port ) ? (int) $ssh->port : 22,
			$ssh->path ? $ssh->path . '/' : '',
			$from_dir,
			$to_dir
		);

		WP_CLI::log( $scp );

		WP_CLI::launch( $scp );

		WP_CLI::success( "Exported to '$filename'." );

		$file_path = WP_CLI\Utils\trailingslashit( $to_dir ) . basename( $filename );

		$progress = \WP_CLI\Utils\make_progress_bar( WP_CLI::colorize( "\n%GImporting database...%n%_\n" ), 101, 100 );
		$progress->tick();
		WP_CLI::log( "\n" );
		WP_CLI::run_command( array( 'db', 'import', $file_path ) );

		ob_start();
		WP_CLI::run_command( array( 'db', 'local-url' ) );
		ob_clean();

		$progress->finish();
	}
}
