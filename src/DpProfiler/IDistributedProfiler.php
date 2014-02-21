<?php
/**
 * User: Dominik
 * Date: 02.07.13
 */

namespace DpProfiler;


use Zend\ServiceManager\ServiceLocatorAwareInterface;

interface IDistributedProfiler extends IProfiler,ServiceLocatorAwareInterface {
	/**
	 * @param string $id
	 */
	public function setIdentifier($id);
}