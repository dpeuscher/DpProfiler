<?php
namespace DpProfiler;
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
	'controllers' => array(
		'invokables' => array(
			'DpProfiler\PrintDistributedProfilerController' => 'DpProfiler\PrintDistributedProfilerController'
		),
	),
	'console' => array(
		'router' => array(
			'routes' => array(
				'profiler-show' => array(
					'options' => array(
						'route' => 'profiler show --id=',
						'defaults' => array(
							'controller' => 'DpProfiler\PrintDistributedProfilerController',
							'action'     => 'show',
						),
					),
				),
			),
		),
	)
);