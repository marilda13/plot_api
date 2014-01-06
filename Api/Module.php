<?php
namespace Api;
use Api\Model\NewsCategoriesTable;
use Api\Model\NewsTable;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class Module
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Api\Model\NewsCategoriesTable' =>  function($sm) {
                	$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                	$resultSetPrototype = new ResultSet();
                	$tableGateway = new TableGateway('hibc3_k2_categories', $dbAdapter, null, $resultSetPrototype);
                	$table = new NewsCategoriesTable($tableGateway);
                	return $table;
                },
                'Api\Model\NewsTable' =>  function($sm) {
                	$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                	$resultSetPrototype = new ResultSet();
                	$tableGateway = new TableGateway('hibc3_k2_items', $dbAdapter, null, $resultSetPrototype);
                	$table = new NewsTable($tableGateway);
                	return $table;
                },
                
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}