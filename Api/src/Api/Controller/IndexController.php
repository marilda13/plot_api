<?php
namespace Api\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Api\Model\NewsCategories;
use Api\Model\News;

use Zend\Json\Json;
use Zend\View\Model\JsonModel;
use \Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;

class IndexController extends AbstractActionController
{

    public function init()
    {
        // do some stuff!
    }
   
   
    public function indexAction()
    {
        // do some stuff!
    }
   
   
    /**
     * Get list of news categories
     * @return \Zend\View\Model\ViewModel
     */
    public function getnewscategoriesAction()
    { 
    	//get params
    	$cat_id = (int) $this->params()->fromRoute('id', null);
    	$params["catid"]= $cat_id;
    	
    	//fetch data
    	$sm = $this->getServiceLocator();
       	$newscategories = $sm->get('Api\Model\NewsCategoriesTable');
       	$resultSet = $newscategories->selectData($params);
       	$resultSetArr = $resultSet->toArray();

        $result = array();
        foreach ($resultSetArr as $key=>$value){
             $result[$key]["categorie"] = $value;
             $result[$key]["metadata"]= array();
        }

        //convert to json
        $jsonObj = $this->arrayToJson($result);
        echo $jsonObj;
       
        $view = new ViewModel();
        //disable layout
        $view->setTerminal(true);
   
        return $view;
    }
   
   
    /**
     * Get all the news of a given category 
     * or all news if no category is selected
     * @return \Zend\View\Model\ViewModel
     */
    public function getnewsbycategoryAction()
    {
        //get params
    	$cat_id = (int) $this->params()->fromRoute('id', null);
    	$params["catid"]= $cat_id;
       
    	//fetch data
    	$sm = $this->getServiceLocator();
    	$newsTbl = $sm->get('Api\Model\NewsTable');
    	$resultSet = $newsTbl->selectData($params);
    	$resultSetArr = $resultSet->toArray();
   	
        $result = array();
        foreach ($resultSetArr as $key=>$value){
            $result[$key]["article"] = $value;
            $result[$key]["article"]["article_body"] = htmlentities($value["article_body"]);
            $result[$key]["article"]["article_description"] = htmlentities($value["article_description"]);
            //not defined yet, plugin not installed
            $result[$key]["gallery"]["images"]= array();
            $result[$key]["gallery"]["descriptions"]= array();
           
            $art_meta = $value["metadata"];
            //$art_author = substr($value["metadata"], strpos($art_meta,'author')+7);
            $result[$key]["metadata"]["keywords"]= $value["metakey"];
            $result[$key]["metadata"]["desc"]= $value["metadesc"];
            $result[$key]["metadata"]["author"]= "Test";
           
            //get article tags/keywords
            $result[$key]["article"]["article_keyword"] = array();
            $news_tags = $newsTbl->getNewsTags($value["article_id"]);
            foreach ($news_tags as $tag){
                array_push($result[$key]["article"]["article_keyword"], $tag->artTag);
            }
        }

        //convert to json
        $jsonObj = $this->arrayToJson($result);
        echo $jsonObj;
       
        $view = new ViewModel();
        $view->setTerminal(true);
       
        return $view;

    }
   
   
   /**
    * Get news content (test)
    * shfaq permbajtjen e nje lajmi
    */
    public function newsAction()
    {
        //get params
        $id = (int) $this->params()->fromRoute('id', 0);
       
        $db = $this->getServiceLocator()->get('db');
       
         $sql = "SELECT i.id as article_id
                , i.title as article_title
                , i.introtext as article_description
                , i.fulltext as article_body
                , i.created as article_date
                , c.id as category_id
                , c.name as category_name
                FROM hibc3_k2_items as i
                INNER JOIN hibc3_k2_categories as c on c.id = i.catid";
        
        if ($id) {
           //get news with the given id
            $sql .= " WHERE i.id = $id" ;
        }
       
        $stmt = $db->query($sql);
        $resultSet = new ResultSet();
        $resultSet->initialize($stmt->execute());
        $art_data = $resultSet->toArray();
       
         //convert to json
        $jsonObj = $this->toJson($resultSet);
        echo $jsonObj;
                 
         //return new ViewModel(array(
        //        'rows' => $stmt->execute(),
        //));
    }
    
   
    /**
     * Convert Resultset to Json object
     * @param ResultSet $resultSet
     */
    private function toJson($resultSet){
       
        $jsonClass = new Json();
        $jsonObject = $jsonClass::encode($resultSet->toArray());
         
        
        return html_entity_decode(
                preg_replace('/\\\\u([0-9a-f]{4})/i', '&#x\1;', $jsonObject),
                ENT_QUOTES, 'UTF-8'
        );
    }
    
   
    /**
     * Convert array Resultset to Json object
     * @param Array $resultSet
     */
    private function arrayToJson($resultSet){
         
        $jsonClass = new Json();
        $jsonObject = $jsonClass::encode($resultSet);
   
        return html_entity_decode(
                preg_replace('/\\\\u([0-9a-f]{4})/i', '&#x\1;', $jsonObject),
                ENT_QUOTES, 'UTF-8'
        );
    }
    
    private function test (){
    	//get params
    	$cat_id = (int) $this->params()->fromRoute('id', 0);
    	 
    	$sql_query = "
                 SELECT id AS categorie_id
                 , name AS categorie_text
                 , alias AS categorie_alias
                 , description AS categorie_description
                 , image AS categorie_image
                 , parent as categories_parent
                 , CONCAT('http://www.plot.al?cid=', id) as hardlink
                 , published
                 , access
                 FROM hibc3_k2_categories
                WHERE published=1";
    	if ($cat_id) {
    		//get data for the given category
    		$sql_query .= " AND id = $cat_id";
    	}
    	 
    	$sql_query.= " ORDER BY ordering";
    	 
    	//fetch data
    	$db = $this->getServiceLocator()->get('db');
    	$stmt = $db->query($sql_query);
    	$resultSet = new ResultSet();
    	$resultSet->initialize($stmt->execute());
    	$resultSetArr = $resultSet->toArray();
    	 
    	$result = array();
    	foreach ($resultSetArr as $key=>$value){
    		$result[$key]["categorie"] = $value;
    		$result[$key]["metadata"]= array();
    	}
    	
    	//convert to json
    	$jsonObj = $this->arrayToJson($result);
    	echo $jsonObj;
    	 
    	$view = new ViewModel();
    	$view->setTerminal(true);
    	 
    	return $view;
    	 
    }
}
