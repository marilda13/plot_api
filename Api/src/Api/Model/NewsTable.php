<?php
namespace Api\Model;
use Zend\Db\TableGateway\TableGateway;

class NewsTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }
    
    public function selectData(array $params)
    {
    	$select = $this->tableGateway->getSql()->select();
    	//get table name
    	$tbl = $this->tableGateway->getTable();
    	
    	$select->columns(array('article_id' => "id"
    			, 'article_title' => 'title'
    			, 'article_alias' => 'alias'
    			, 'article_description' => 'introtext'
    			, 'article_body' => 'fulltext'
    			, 'article_date' => 'created'
    			, 'hardlink' => new \Zend\Db\Sql\Expression("CONCAT('http://www.plot.al?aid=', $tbl.id)")
    			, 'metakey', 'metadesc', 'metadata'));
    	 
    	// join table with alias
    	$select->join(array('c' => 'hibc3_k2_categories'), 'catid = c.id'
    					, array('category_id' => "id"
    					, 'category_name' => "name"))
    		->join(array('u' => 'hibc3_users'), 'created_by = u.id', array('article_author' => "name"))
   			->where(array("$tbl.published" => 1));

    	if (isset($params["catid"]) && $params["catid"]!=null) {
    		$catid  = (int) $params["catid"];
    		$select->where(array('catid' => $catid));
    	}
    	
    	$select->order(array("$tbl.catid asc", "$tbl.ordering asc"));
    	
    	$rows = $this->tableGateway->selectWith($select);
    	return $rows;

    }
    
    public function getNewsTags($newsId)
    {
    	$newsId  = (int) $newsId;
    	
    	$select = $this->tableGateway->getSql()->select();
    	//get table name
    	$tbl = $this->tableGateway->getTable();
    	$select->columns(array("id"));
    	// join table with alias
    	$select->join(array('tref' => 'hibc3_k2_tags_xref'), "$tbl.id = tref.itemID")
    		->join(array('t' => 'hibc3_k2_tags'), 't.id = tref.tagID', array('artTag' => "name"))
    		->where(array("$tbl.id" => $newsId))
     		->order(array("artTag"));
    	 
    	$rows = $this->tableGateway->selectWith($select);

    	return $rows;
    
    }
    

    public function getNewsById($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }
    
    public function fetchAll()
    {
    	$resultSet = $this->tableGateway->select();
    	return $resultSet;
    }

}