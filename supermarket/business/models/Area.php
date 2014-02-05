<?php
/**
 * @name LoginModel
 * @desc 地区
 * @author Vic Shiwei
 */
class AreaModel extends BasicModel{

    public function __construct() {
        parent::__construct();
        $this->db = Factory::getDBO('local_jiada');
    }
    
    /**
     * 获取省份
     * @param int $parentId 分类的父级节点id
     * @return array
     */
    public function getNodeArea($parentId=0){
        $query = "select a.areaId as id, a.areaName as name, a.parentPath from area a where a.parentId=$parentId and a.public=1";
        $query .=" order by a.sort asc, a.areaId desc ";
        $this->db->setQuery($query);
        $rows = $this->db->loadAssocList();        
        return $rows;
    }
}
