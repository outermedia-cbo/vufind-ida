<?php
namespace Ida\RecordTab;

use VuFindSearch\Service as SearchService;

/**
 * Media tab
 */
class Media extends \VuFind\RecordTab\AbstractBase
{
	protected $searchService; 
	protected $Media;
	
	
	public function __construct(SearchService $searchService)
	{
		$this->searchService = $searchService;
    }
    
    public function isActive()
    {
    	return $this->hasMedia();
    }
	
	public function getMedia()
	{
		return $this->Media;
	}

	/**
	 * Get the on-screen description for this tab.
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return 'media';
	}

	/**
	 * Checks whether the current record has digitized media.
	 * 
	 * @return boolean
	 */
	protected function hasMedia(){
		$m = $this->driver->getMedia();
		return !empty($m);
	}
}