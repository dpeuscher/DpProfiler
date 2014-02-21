<?php
/**
 * User: dpeuscher
 * Date: 03.04.13
 */

namespace DpProfiler;

use Exception;

/**
 * Class Profiler
 *
 * @package DpProfiler
 */
class Profiler implements IPrintableProfiler{
	/**
	 * @var Profiler
	 */
	protected static $instance;
	/**
	 * @return Profiler
	 */
	public static function getInstance() {
		if (!isset(self::$instance))
			self::$instance = new self();
		return self::$instance;
	}

	/**
	 * Singleton-Pattern
	 */
	protected function __construct() {}

	/**
	 * @var array
	 */
	protected $_tracking = array();
	/**
	 * @var int
	 */
	protected $_notPrintedCounter = 0;
	/**
	 * @var int
	 */
	protected $_printInterval = 0;

	/**
	 * @param boolean $start
	 */
	public function trackGlobal($start) {
		$tracking = &$this->_tracking;
		if (!isset($tracking['full']))
			$tracking['full'] = 0;
		if ($start)
			$tracking['start'] = microtime(true);
		else {
			$tracking['full'] += microtime(true)-$tracking['start'];
			$this->trackGlobal(true);
		}
	}

	/**
	 * @param int $interval
	 */
	public function setPrintInterval($interval) {
		$this->_printInterval = $interval;
	}
	/**
	 * @param string      $key
	 * @param boolean     $start
	 * @param string|null $subKey
	 * @throws \Exception
	 */
	public function track($key,$start,$subKey = null) {
		if (in_array($key,array('full','start')))
			throw new Exception("Key not allowed (".$key.")");

		$tracking = &$this->_tracking;
		if (!isset($tracking[$key])) {
			$tracking[$key] = array();
			$tracking[$key]['count'] = 0;
		}
		if (!is_null($subKey) && !isset($tracking[$key]['children']))
			$tracking[$key]['children'] = array();
		if (!is_null($subKey) && !isset($tracking[$key]['children'][$subKey])) {
			$tracking[$key]['children'][$subKey] = array();
			$tracking[$key]['children'][$subKey]['count'] = 0;
		}
		if ($start) {
			if (is_null($subKey))
				$tracking[$key]['start'] = microtime(true);
			else
				$tracking[$key]['children'][$subKey]['start'] = microtime(true);
		}
		else {
			if (is_null($subKey)) {
				if (!isset($tracking[$key]['full']))
					$tracking[$key]['full'] = 0;
				$tracking[$key]['full'] += microtime(true)-$tracking[$key]['start'];
				$tracking[$key]['count']++;
				unset($tracking[$key]['start']);
			}
			else {
				if (!isset($tracking[$key]['children'][$subKey]['full']))
					$tracking[$key]['children'][$subKey]['full'] = 0;
				$tracking[$key]['children'][$subKey]['full'] +=
					microtime(true)-$tracking[$key]['children'][$subKey]['start'];
				$tracking[$key]['children'][$subKey]['count']++;
				unset($tracking[$key]['children'][$subKey]['start']);
			}
			if ($this->_printInterval && $this->_notPrintedCounter++ >= $this->_printInterval) {
				echo $this->trackPrintTime();
				$this->_notPrintedCounter = 0;
			}
		}
	}

	/**
	 * @param int $time
	 * @return string
	 */
	public function humanReadableTime ($time) {
		$timeString = '';
		if ($time > 60*60) {
			$timeString .= floor($time/60/60)." hours, ";
			$time -= floor($time/60/60)*60*60;
		}
		if ($time > 60) {
			$timeString .= floor($time/60)." minutes, ";
			$time -= floor($time/60)*60;
		}
		$timeString .= floor($time)." seconds";
		return $timeString;
	}

	/**
	 * @return string
	 */
	public function trackPrintTime () {
		$this->trackGlobal(false);
		$tracking = &$this->_tracking;
		$full = $tracking['full'];
		$return = '';
		foreach ($tracking as $key => $data) {
			if ($key == 'full' || $key == 'start') continue;
			$return .= $key." ".number_format($data['full']/$full*100,2)."% (".$this->humanReadableTime($data['full']).
				") [Count: ".$data['count']."]\n";
			if (isset($data['children']))
				foreach ($data['children'] as $subKey => $subData) {
					$return .= $key." ".$subKey." ".number_format($subData['full']/$data['full']*100,2)."% (".
						$this->humanReadableTime($subData['full']).") (".number_format($subData['full']/$full*100,2).
						"% of full) [Count: ".$subData['count']."]\n";
				}
		}
		$return .= "\nfull 100% (".$this->humanReadableTime($full).")\n\n\n";
		return $return;
	}
}