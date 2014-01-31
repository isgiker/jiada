<?php

/**
 * @name Chaoshi_GoodscateModel
 * @desc 商品分类
 * @author root
 */
class Chaoshi_GoodscateModel extends BasicModel{

    public function __construct() {
        parent::__construct();
        $this->db = Factory::getDBO('local_jiada_chaoshi');
    }

    public function getGcateList($search=array(), $parentId = 0, $tag = '') {
        $query = "select * from goods_categary where 1=1";
        $query .=" and parentId=$parentId";
        if (!isset($search['status']) || !$search['status']) {
            //默认显示已审核通过的
            $query .=" and status = 1";            
        }elseif (isset($search['status']) && $search['status'] !='all') {
            $query .=" and status = $search[status]";
        }      
        $query .=" order by sort asc,cateId desc ";

        $this->db->setQuery($query);
        $rows = $this->db->loadAssocList();

        //递归调用;	
        static $newrows = array();
        foreach ($rows as $key => $item) {
            $item['cateName'] = $tag . $item['cateName'];
            $newrows[] = $item;
            $this->getGcateList($search, $item['cateId'], $tag . "......&nbsp;|&nbsp;");
        }
        return array_slice($newrows, $this->getLimitStart(), $this->getLimit());
    }
    
    public function getGcateTotal($search=array()){
        $query = "select count(cateId) from goods_categary where 1=1";
        if (!isset($search['status']) || !$search['status']) {
            //默认显示已审核通过的
            $query .=" and status = 1";            
        }elseif (isset($search['status']) && $search['status'] !='all') {
            $query .=" and status = $search[status]";
        }
        $this->db->setQuery($query);
        $num = $this->db->loadResult();
        return $num;
    }

    /**
     * 行业树结构显示 parent category
     */
    function getTreeGcate($cid=0, $parentId=0, $tag='&nbsp;&nbsp;') {
        $query = "select * from goods_categary where 1=1";
        $query .=" and parentId=$parentId";
        $query .=" and status = 1";
        $query .=" order by sort asc,pinyin asc,cateId desc ";
        $this->db->setQuery($query);
        $rows = $this->db->loadAssocList();


        $strstr = '';
        foreach ($rows as $key => $item) {
            if ($cid == $item['cateId']) {
                $optionselect = ' selected="selected"';
            } else {
                $optionselect = '';
            }

            $strstr.="<option value='$item[cateId]' $optionselect>" . $tag . $item['cateName'] . "</option>";

            $strstr.=$this->getTreeGcate($cid, $item['cateId'], $tag . "......&nbsp;|&nbsp;");
        }
        return $strstr;
    }
    
    /**
     * 根据分类id获取当前分类及子类
     * @param int $cateId
     * @return array
     */
    public function getGcateChildren($cateId){
        if(!$cateId){
            return false;
        }
        $query = "select cateId,cateName,parentId from goods_categary where cateId=$cateId or parentId=$cateId and status = 1";
        $this->db->setQuery($query);
        $rows = $this->db->loadAssocList();
        return $rows;
    }
    
    public function getGcateInfo($cateId){
        $query = "select * from goods_categary where cateId=$cateId";
        $this->db->setQuery($query);
        $rows = $this->db->loadAssoc();
        return $rows;
    }

    public function add($data) {
        if($data['parentId']==0){
            $parentPath = 0;
        }else{
            $parentPath = $this->getParentPath($data['parentId']);
        }
        $time=time();
        $sql = "insert goods_categary set cateName='$data[cateName]',pinyin='$data[pinyin]',parentId='$data[parentId]',parentPath='$parentPath',createTime='$time',sort='0',status='$data[status]'";
        $result = $this->db->query($sql);
        if ($result == false) {
            $error = $db->ErrorMsg();
            die("$error");
        }
        $cateId = $this->db->insertid();
        $this->upChildNums($data['parentId']);
        return true;
    }

    public function edit($data) {
        if($data['parentId']==0){
            $parentPath = 0;
        }else{
            $parentPath = $this->getParentPath($data['parentId']);
        }
        //分类和父分类不能相同
        if($data['cateId'] == $data['parentId']){
            return false;
        }
        $sql = "update goods_categary set cateName='$data[cateName]',pinyin='$data[pinyin]',parentId='$data[parentId]',parentPath='$parentPath',sort='0',status='$data[status]' where cateId=$data[cateId]";
        $result = $this->db->query($sql);
        if ($result == false) {
            $error = $db->ErrorMsg();
            die("$error");
        }
        $this->upChildNums($data['parentId']);
        return true;
    }
    
    private function upChildNums($cateId){
        if(!$cateId) return false;
        $query = "select count(cateId) from goods_categary where parentId=$cateId";
        $this->db->setQuery($query);
        $num = $this->db->loadResult();
        $sql = "update goods_categary set childNums='$num' where cateId=$cateId";
        $result = $this->db->query($sql);
        if ($result == false) {
            $error = $db->ErrorMsg();
            die("$error");
        }
        return true;
    }
    
    private function getParentPath($parentId){
        $query = "select parentId from goods_categary where cateId = $parentId";
        $this->db->setQuery($query);
        $newParentId = $this->db->loadResult();
        
        static $parentPath = array();
        if($newParentId!=0){
            $this->getParentPath($newParentId);
            $parentPath[] = $newParentId;
        }
        
        if(!empty($parentPath)){
            $parentPathStr = implode(',', $parentPath);
            $result = $parentPathStr.','.$parentId;
        }else{
            $result = $parentId;
        }
        return $result;
    }

    /**
     * form 验证规则
     */
    public function getRules() {
        $rules = '{"validation":[{
			 		"value":"cateName",
			  		"label":"商品分类名称",
			  		"rules":[	  					
						{
		  					"name":"clearxss"				
	 					},						
	 					{
	 						"name":"required",
	 						"message":"%s%为必填项"
	 					}
		  			]	
		  		},
		  		{
			 		"value":"pinyin",
			  		"label":"拼音",
			  		"rules":[
						{
	  						"name":"clearxss"
	  					},
						{
	 						"name":"required",
	 						"message":"%s%为必填项"
	 					},
	 					{
	 						"name":"regex",
	 						"value":"/^[A-Za-z0-9]{2,}$/",
	 						"message":"%s%长度为2位以上的字母、数字"
	 					}
		  			]	
				},
		  		{
			 		"value":"sort",
			  		"label":"排序",
			  		"rules":[
						{
	  						"name":"clearxss"
	  					},
						{
	 						"name":"required",
	 						"message":"%s%为必填项"
	 					},
	 					{
	 						"name":"number",
	 						"value":"/^[0-9]{1,}$/",
	 						"message":"%s%长度为1位或以上的数字"
	 					}
		  			]	
				}
                                ]}';

        return $rules;
    }
    
    //以下供外部控制器调用
    /*================================================================================================*/
    /**
     * 根据分类id获取分类名称
     * @param string $cateIds 多个id：1,2,3
     * @return array
     */
    public function getCatePathName($cateIds){
        $query = "select cateName from goods_categary where cateId in ($cateIds)";
        $this->db->setQuery($query);
        $rows = $this->db->loadAssocList();
        return $rows;
    }
}
