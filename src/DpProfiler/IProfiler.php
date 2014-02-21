<?php
/**
 * User: Dominik
 * Date: 02.07.13
 */

namespace DpProfiler;


interface IProfiler {
	/**
	 * @param boolean $start
	 */
	public function trackGlobal($start);
	/**
	 * @param string      $key
	 * @param boolean     $start
	 * @param string|null $subKey
	 * @throws \Exception
	 */
	public function track($key,$start,$subKey = null);
}