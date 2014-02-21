<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace DpProfiler;

use Zend\Console\Adapter\AbstractAdapter;

/**
 * Class Module
 *
 * @package DbProfiler
 */
class Module
{
	/**
	 * @return array
	 */
	public function getConfig()
    {
        return include(__DIR__.'/config/module.config.php');
    }

	/**
	 * @return array
	 */
	public function getServiceConfig()
    {
    	return array(
		    'aliases' => array(
			    'Profiler' => 'DpProfiler\IDistributedProfiler'
		    ),
			'invokables' => array(
				'DpProfiler\IDistributedProfiler' => 'DpProfiler\DistributedProfiler',
				'DpProfiler\PrintDistributedProfiler' => 'DpProfiler\PrintDistributedProfiler'
			),
            'factories' => array(
			    'DpProfiler\Profiler' =>  function($sm) {
				    $profiler = Profiler::getInstance();
				    $profiler->trackGlobal(true);
				    return $profiler;
			    },
            ),
        );
    }

	/**
	 * @return array
	 */
	public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
	public function getConsoleUsage(AbstractAdapter $console)
	{
		return array(
			// Describe available commands
			'profiler show --id=',
			// Describe expected parameters
			array('--id=','(optional) prefix of the redis-keys for profiling'),
		);
	}

}
