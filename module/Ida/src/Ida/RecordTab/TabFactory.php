<?php
namespace Ida\RecordTab;
use Zend\ServiceManager\ServiceManager;

class TabFactory
{
	/**
	 * Factory for OtherInstitutions tab plugin.
	 *
	 * @param ServiceManager $sm Service manager.
	 *
	 * @return OtherInstitutions
	 */
	public static function getOtherInstitutions(ServiceManager $sm)
	{
		return new OtherInstitutions(
				$sm->getServiceLocator()->get('VuFind\Search')
		);
	}
	
	/**
	 * Factory for Media tab plugin.
	 *
	 * @param ServiceManager $sm Service manager.
	 *
	 * @return Media
	 */
	public static function getMedia(ServiceManager $sm)
	{
		return new Media(
				$sm->getServiceLocator()->get('VuFind\Search')
				);
	}
}