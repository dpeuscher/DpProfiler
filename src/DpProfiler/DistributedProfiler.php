<?php
/**
 * User: Dominik
 * Date: 02.07.13
 */

namespace DpProfiler;


use Redis;
use DpZFExtensions\ServiceManager\TServiceLocator;
use Exception;

class DistributedProfiler implements IDistributedProfiler {
	use TServiceLocator;
	/**
	 * @var string
	 */
	protected $_id;
	/**
	 * @var int
	 */
	protected $_ownId;
	/**
	 * @var array
	 */
	protected $_tracking;
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
	 * @return int
	 */
	protected function _getOwnId() {
		if (!isset($this->_ownId)) {
			try {
				do { $id = rand(1,999999); } while ($this->_getRedis()->hexists($this->_id.'_jobs',$id));
			} catch (\Predis\ServerException $e) {
				$id = rand(1,999999);
			}
			$this->_ownId = $id;
		}
		return $this->_ownId;
	}
	/**
	 * @param string $id
	 */
	public function setIdentifier($id) {
		if (preg_match('#[^a-f0-9]#',strtolower($id)))
			$id = md5($id);
		$this->_id = strtolower($id);
	}

	/**
	 * @param boolean $start
	 */
	public function trackGlobal($start) {
		if ($start)
			$this->_getRedis()->hset($this->_id.'_jobs_start',$this->_getOwnId(),microtime(true));
		else {
			$this->_getRedis()->incrbyfloat($this->_id.'_jobs_full',
				microtime(true) - $this->_getRedis()->hget($this->_id.'_jobs_start',$this->_getOwnId()));
			$this->_getRedis()->hset($this->_id.'_jobs_start',$this->_getOwnId(),microtime(true));
		}
	}

	/**
	 * @param string      $key
	 * @param boolean     $start
	 * @param string|null $subKey
	 * @throws Exception
	 */
	public function track($key,$start,$subKey = null) {
		$tracking = &$this->_tracking;
		if (!isset($tracking[$key]))
			$tracking[$key] = array();
		if (!is_null($subKey) && !isset($tracking[$key]['children']))
			$tracking[$key]['children'] = array();
		if (!is_null($subKey) && !isset($tracking[$key]['children'][$subKey]))
			$tracking[$key]['children'][$subKey] = array();
		if ($start) {
			if (is_null($subKey))
				$tracking[$key]['start'] = microtime(true);
			else
				$tracking[$key]['children'][$subKey]['start'] = microtime(true);
		}
		else {
			if (preg_match('#[^a-zA-Z0-9]#',$key))
				$key = base64_encode($key);
			if (is_null($subKey)) {
				$this->_getRedis()->incrbyfloat($this->_id.'_'.$key.'_full',
				                                             microtime(true)-$tracking[$key]['start']);
				$this->_getRedis()->incr($this->_id.'_'.$key.'_count');
				unset($tracking[$key]['start']);
			}
			else {
				$this->_getRedis()->incrbyfloat($this->_id.'_'.$key.'_children_'.$subKey.'_full',
				                                  microtime(true)-$tracking[$key]['children'][$subKey]['start']);
				$this->_getRedis()->incr($this->_id.'_'.$key.'_children_'.$subKey.'_count');
				unset($tracking[$key]['children'][$subKey]['start']);
			}
			$this->trackGlobal(false);
		}
	}
}
