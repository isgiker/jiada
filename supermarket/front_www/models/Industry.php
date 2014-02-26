<?php
/**
 * @name LoginModel
 * @desc 行业
 * @author Vic Shiwei
 */
class IndustryModel extends BasicModel{

    public function __construct() {
        parent::__construct();
        $this->db = Factory::getDBO('local_jiada');
    }
    
    /**
     * 获取行业信息
     * @param int $parentId 分类的父级节点id
     * @return array
     */
    public function getIndustryInfo($industryId){
        if(!$industryId){
            return false;
        }
        $query = "select a.* from industry a where a.industryId=$industryId";
        $this->db->setQuery($query);
        $rows = $this->db->loadAssoc();
        return $rows;
    }
}
