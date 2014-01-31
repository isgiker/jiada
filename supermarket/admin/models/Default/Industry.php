<?php

/**
 * @name Default_IndustryModel
 * @desc 行业管理
 * @author Vic
 */
class Default_IndustryModel extends BasicModel {

    public function __construct() {
        parent::__construct();
        $this->db = Factory::getDBO('local_jiada');
    }

    /**
     * 获取所有行业
     */
    public function getIndustrys() {
        $query = "select a.* from industry a where a.public = 1";
        $query .=" order by a.pinyin asc, a.industryId desc ";

        $this->db->setQuery($query);
        $rows = $this->db->loadAssocList();
        $data = array();
        if ($rows) {
            foreach ($rows as $key => $item) {
                if ($item['parentId'] > 0) {
                    $data[$item['parentId']]['items'][] = $item;
                } elseif ($item['parentId'] == 0) {
                    $data[$item['industryId']]['name'] = $item['industryName'];
                }
            }
        }
        return $data;
    }

}
