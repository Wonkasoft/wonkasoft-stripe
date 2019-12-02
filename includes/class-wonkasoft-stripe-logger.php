<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Log all things!
 *
 * @since 4.0.0
 * @version 4.0.0
 */
class Wonkasoft_Stripe_Logger {

	public static $logger;
	const WONKASOFT_LOG_FILENAME = 'wonkasoft-stripe';

	/**
	 * Utilize WC logger class
	 *
	 * @since 4.0.0
	 * @version 4.0.0
	 */
	public static function log( $message, $start_time = null, $end_time = null ) {
		if ( ! class_exists( 'WC_Logger' ) ) {
			return;
		}

		if ( apply_filters( 'wonkasoft_stripe_logging', true, $message ) ) {
			if ( empty( self::$logger ) ) {
				if ( Wonkasoft_Stripe_Helper::is_wc_lt( '3.0' ) ) {
					self::$logger = new WC_Logger();
				} else {
					self::$logger = wc_get_logger();
				}
			}

			$settings = get_option( 'wonkasoft_stripe_settings' );

			if ( empty( $settings ) || isset( $settings['logging'] ) && 'yes' !== $settings['logging'] ) {
				return;
			}

			if ( ! is_null( $start_time ) ) {

				$formatted_start_time = date_i18n( get_option( 'date_format' ) . ' g:ia', $start_time );
				$end_time             = is_null( $end_time ) ? current_time( 'timestamp' ) : $end_time;
				$formatted_end_time   = date_i18n( get_option( 'date_format' ) . ' g:ia', $end_time );
				$elapsed_time         = round( abs( $end_time - $start_time ) / 60, 2 );

				$log_entry  = "\n" . '====Stripe Version: ' . WONKASOFT_STRIPE_VERSION . '====' . "\n";
				$log_entry .= '====Start Log ' . $formatted_start_time . '====' . "\n" . $message . "\n";
				$log_entry .= '====End Log ' . $formatted_end_time . ' (' . $elapsed_time . ')====' . "\n\n";

			} else {
				$log_entry  = "\n" . '====Stripe Version: ' . WONKASOFT_STRIPE_VERSION . '====' . "\n";
				$log_entry .= '====Start Log====' . "\n" . $message . "\n" . '====End Log====' . "\n\n";

			}

			if ( Wonkasoft_Stripe_Helper::is_wc_lt( '3.0' ) ) {
				self::$logger->add( self::WONKASOFT_LOG_FILENAME, $log_entry );
			} else {
				self::$logger->debug( $log_entry, array( 'source' => self::WONKASOFT_LOG_FILENAME ) );
			}
		}
	}
}
