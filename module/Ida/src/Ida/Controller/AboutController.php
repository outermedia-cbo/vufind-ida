<?php
/**
 * User: stefaniep
 * Date: 10/18/16
 * Time: 01:34 PM
 */

namespace Ida\Controller;

use VuFind\Controller\AbstractBase;use VuFind\RecordDriver\SolrDefault;use Zend\Config\Config;use Zend\View\Model\ViewModel;

class AboutController extends AbstractBase
{

    public function homeAction()
    {
        $view = $this->createViewModel('about/about');
        return $view;
    }

    protected function createViewModel($template = null, $params = null)
    {
        $view = new ViewModel();
        $view->setTemplate($template);

        return $view;
    }

}