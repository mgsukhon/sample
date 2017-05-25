<?php

/**
 * Zend Framework (http://framework.zend.com/)
 * @author Abdul Wadud
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Application\Entity\Classes;
use Application\Form\ClassForm;
use Application\Form\addClassFilter;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Stdlib\Hydrator;
use Application\Options\ModuleOptions;

class ClassController extends AbstractActionController {
    
    
    /**
     * Reason ServiceLocatorAwareInterface is deprecated and no longer valid in zf3
     * 
     * @var Zend\ServiceManager\ServiceManager
     * @author ABC
     * @versio 2017-04-03 
     */
    public $sl;
    
    /**
     * Reason ServiceLocatorAwareInterface is deprecated and no longer valid in zf3
     * 
     * @return Zend\ServiceManager\ServiceManager
     * @author ABC 
     * @date 2017-04-04 
     */
    public function getServiceLocator(){
        return $this->sl;
    }

    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;

    public function getEntityManager() {
        if (null === $this->em) {
            $this->em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        }

        return $this->em;
    }

	/*
	 * Landing page
	 * listing user classes
	 */
    public function indexAction() {
          $request = $this->getRequest();
        if ($request->isPost()) {
            $this->_classlistajax();
        }
        return new ViewModel();
    }

    // generate ajax data table for user list show
    private function _classlistajax() {
        $this->layout()->app_user_classlist = 1; // to set navigation class active
        //$userlist = $this->getEntityManager()->getRepository('\Application\Entity\Users')->findAll();



        $targetPage = $this->params()->fromPost('targetPage');
        $params = array(
            'search' => $this->params()->fromPost('sSearch'),
            'page_start' => $this->params()->fromPost('iDisplayStart'),
            'draw_count' => $this->params()->fromPost('sEcho'),
            'per_page' => $this->params()->fromPost('iDisplayLength'),
        );
        /**
         * Let's take care of the sorting column to be passed to doctrine.
         * DataTable sends params like iSortCol_0.
         */
        $sorting_cols = array('1' => 'c.name');
        $params['sort_key'] = $sorting_cols[$this->params()->fromPost('iSortCol_0')];
        $params['sort_dir'] = $this->params()->fromPost('sSortDir_0');
        $params['targetPage'] = $targetPage;

        $records = $this->getEntityManager()->getRepository("\Application\Entity\Classes")->getClasses($params);

        $params['show_total'] = true;
        $records_total = $this->getEntityManager()->getRepository("\Application\Entity\Classes")->getClasses($params);

        /**
         * Datatable json format
         */
        $jsonArray = array();
        if ($records_total > 0) {
            foreach ($records as $key => $e) {

                $jsonArray[$key][] = '<input type="checkbox" class="checkbox" name="delete_id" >'; //$e['priority']
                $jsonArray[$key][] = $e['name'];
                $jsonArray[$key][] = '<div class="visible-md visible-lg hidden-sm hidden-xs action-buttons">'
                        . '<a class="green editClass" href="application/class/edit-class/' . $e['id'] . '"><i class="icon-edit bigger-130"></i></a>'
                        . '<a class="red deleteBtn" href="application/class/delete-class/' . $e['id'] . '"><i class="icon-trash bigger-130"></i></a>'
                        . '
                    </div>
                    <div class="visible-xs visible-sm hidden-md hidden-lg action-buttons">
                        <div class="inline position-relative">
                            <button class="btn btn-minier btn-yellow dropdown-toggle" data-toggle="dropdown">
                                <i class="icon-caret-down icon-only bigger-120"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-only-icon dropdown-yellow pull-right dropdown-caret dropdown-close">
                                <li>'
                        . '<a class="green editClass" href="application/class/edit-class/' . $e['id'] . '"><i class="icon-edit bigger-130"></i></a>'
                        . '<a class="red deleteBtn" href="application/class/delete-class/' . $e['id'] . '"><i class="icon-trash bigger-130"></i></a>'
                        . '</li>
                            </ul>
                        </div>
                    </div>';
            }
        }

        $jsonData['iTotalRecords'] = $records_total;
        $jsonData['iTotalDisplayRecords'] = $records_total;
        $jsonData['aaData'] = $jsonArray;
        echo json_encode($jsonData);
        die();
    }}
