<?php
/**
 * User: Dominik
 * Date: 02.07.13
 */

namespace DpProfiler;


interface IPrintableProfiler extends IProfiler {
	/**
	 * @param int $interval
	 */
	public function setPrintInterval($interval);
	/**
	 * @param int $time
	 * @return string
	 */
	public function humanReadableTime ($time);

	/**
	 * @return string
	 */
	public function trackPrintTime ();
}