<?php
namespace Api\Model;
use Zend\Db\TableGateway\TableGateway;

class NewsCategoriesTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }
    
    public function selectData(array $params)
    {
    	$select = $this->tableGateway->getSql()->select();
    	$select->columns(array('categorie_id' => 'id'
    			, 'categorie_text' => 'name'
    			, 'categorie_alias' => 'alias'
    			, 'categorie_description' => 'description'
    			, 'categorie_image' => 'image'
    			, 'hardlink' => new \Zend\Db\Sql\Expression("CONCAT('http://www.plot.al?cid=', id)")
    			, 'categorie_parent' => 'parent'
    			, 'published', 'access'));

    	$select->where(array('published' => 1));

    	if (isset($params["catid"]) && $params["catid"]!=null) {
    		$catid  = (int) $params["catid"];
    		$select->where(array('id' => $catid));
    	}
    	
    	$select->order(array('ordering asc'));
    	
    	$rows = $this->tableGateway->selectWith($select);
    	return $rows;

    }

    public function getCategory($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

}