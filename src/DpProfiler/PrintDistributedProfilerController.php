<?php
/**
 * User: Dominik
 * Date: 02.07.13
 */

namespace DpProfiler;


use Redis;
use DpZFExtensions\ServiceManager\TServiceLocator;
use HttpRequest;
use Zend\Console\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

class PrintDistributedProfilerController extends AbstractActionController implements ServiceLocatorAwareInterface {
	use TServiceLocator;
	/**
	 * @var Redis
	 */
	protected $_redis;

	/**
	 * @return Redis
	 */
	protected function _getRedis() {
		if (!isset($this->_redis))
			$this->_redis = $this->getServiceLocator()->get('PlainRedisNoSerializer');
		return $this->_redis;
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
	 * @param string $id
	 * @return string
	 */
	public function trackPrintTime ($id) {
		$return = '';
		#$full = 0;
		$full = $this->_getRedis()->get($id.'_jobs_full');
		#foreach ($this->_getRedis()->hkeys($id.'_jobs_start') as $key)
		#	$full += (microtime(true)-$this->_getRedis()->hget($id.'_jobs_start',$key));

		$return .= "full ".number_format($full/$full*100,2)."% (".$this->humanReadableTime($full).
			") [Count: ".$this->_getRedis()->hlen($id.'_jobs_start')."]\n";
		foreach ($this->_getRedis()->keys($id.'_*_full') as $key) {
			if (preg_match('#_jobs_full$#',$key))
				continue;
			$delta = $this->_getRedis()->get($key);
			$count = $this->_getRedis()->get(str_replace('_full','_count',$key));
			if (preg_match('#'.$id.'_([^_]+)_full#',$key,$matches)) {
				$return .= $matches[1]." ".number_format($delta/$full*100,2)."% (".$this->humanReadableTime($delta).
					") [Count: ".$count."]\n";
			}
			elseif (preg_match('#'.$id.'_([^_]+)_children_([^_]+)_full#',$key,$matches)) {
				$return .= $matches[1]." > ".$matches[2]." ".number_format($delta/$full*100,2)."% (".
					$this->humanReadableTime($delta).") [Count: ".$count."]\n";
			}
		}
		$return .= "\nfull 100% (".$this->humanReadableTime($full).")\n\n\n";
		return $return;
	}
	public function showAction() {
		/** @var HttpRequest|Request $request */
		$request = $this->getRequest();
		$id = $request->getParam('id');
		echo $this->trackPrintTime($id);
		exit;
	}
}
