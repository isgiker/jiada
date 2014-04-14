<?php

/**
 * @name Chaoshi_GoodscateModel
 * @desc 商品分类
 * @author root
 */
class Chaoshi_GoodscateModel extends BasicModel{

    public function __construct() {
        parent::__construct();
        $this->hydb = Factory::getDBO('local_jiada_chaoshi');
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

        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssocList();

        //递归调用;	
        static $newrows = array();
        foreach ($rows as $key => $item) {
            $item['cateName'] = $tag . $item['cateName'];
            $newrows[] = $item;
            $this->getGcateList($search, $item['cateId'], $tag . "......&nbsp;|&nbsp;");
        }
        $data['data']=array_slice($newrows, $this->getLimitStart(), $this->getLimit());
        $data['total']=count($newrows);
        return $data;
    }
    
    public function getGcateTotal($search=array(), $parentId = 0, $tag = '') {
        $query = "select count(cateId) from goods_categary where 1=1";
        $query .=" and parentId=$parentId";
        if (!isset($search['status']) || !$search['status']) {
            //默认显示已审核通过的
            $query .=" and status = 1";            
        }elseif (isset($search['status']) && $search['status'] !='all') {
            $query .=" and status = $search[status]";
        }
        $this->hydb->setQuery($query);
        $num = $this->hydb->loadResult();
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
        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssocList();


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
     * @param int $self true（包含分类本身）false（只返回子类）
     * @return array
     */
    public function getGcateChildren($cateId, $self=false){
        if(!$cateId){
            return false;
        }
        if($self===true){
            $query = "select cateId,cateName,parentId from goods_categary where cateId=$cateId or parentId=$cateId and status = 1";
        }else{
            $query = "select cateId,cateName,parentId from goods_categary where parentId=$cateId and status = 1";
        }
        
        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssocList();
        return $rows;
    }
    
    /**
     *检查该分类下是否有子分类 
     */
    public function checkCateChildren($cateId){
        if(!$cateId){
            die('商品分类Id不能为空！');
        }
        $query = "select count(a.cateId) from goods_categary a where a.parentId='$cateId'";
        $this->hydb->setQuery($query);
        $num = $this->hydb->loadResult();
        return $num;
    }
    
    /**
     *检查该分类下是否有品牌数据 
     */
    public function checkCateBrand($cateId){
        if(!$cateId){
            die('商品分类Id不能为空！');
        }
        $query = "SELECT count(a.brandId) FROM goods_brand a where a.cateId='$cateId' or FIND_IN_SET('$cateId',a.childCateType)";
        $this->hydb->setQuery($query);
        $num = $this->hydb->loadResult();
        return $num;
    }
    
    /**
     *检查该分类下是否有属性数据 
     */
    public function checkCateAttribute($cateId){
        if(!$cateId){
            die('商品分类Id不能为空！');
        }
        $query = "SELECT count(a.attrId) FROM goods_attribute a where a.goodsCateId='$cateId'";
        $this->hydb->setQuery($query);
        $num = $this->hydb->loadResult();
        return $num;
    }
    
    /**
     *检查该分类下是否有商品数据 
     */
    public function checkCateGoods($cateId){
        if(!$cateId){
            die('商品分类Id不能为空！');
        }
        $query = "SELECT count(a.goodsId) FROM goods a where a.cateId in(select cateId from goods_categary where cateId='$cateId' or parentId='$cateId')";
        $this->hydb->setQuery($query);
        $num = $this->hydb->loadResult();
        return $num;
    }
    
    public function getGcateInfo($cateId){
        $query = "select * from goods_categary where cateId=$cateId";
        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssoc();
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
        $result = $this->hydb->query($sql);
        if ($result == false) {
            $error = $db->ErrorMsg();
            die("$error");
        }
        $cateId = $this->hydb->insertid();
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
        $result = $this->hydb->query($sql);
        if ($result === false) {
            $error = $this->hydb->ErrorMsg();
            die("$error");
        }
        //成功后
        //更新子类的节点路径
        $this->upChild($data['cateId'],$parentPath);
        //更新当前分类子元素数量
        $this->upChildNums($data['parentId']);
        return true;
    }
    
    /**
     * 删除分类
     * @param type $cateId
     * @return boolean
     */
    public function del($cateId){
        if(!$cateId) return false;
        //获取该分类信息
        $cateInfo=$this->getGcateInfo($cateId);
        if(!$cateInfo) return false;
        $query = "delete from goods_categary where cateId='$cateId'";
        $result = $this->hydb->query($query);
        $this->upChildNums($cateInfo['parentId']);
        return $result;
    }
    
    /**
     * 更新父类下的所有子类的节点路径parentPath和其子节点的数量
     * @param type $cateId 当前分类id
     * @param type $parentPath 当前父类的节点路径
     * @return boolean
     */
    private function upChild($cateId, $parentPath) {
        if(!$cateId) return false;
        $query="select cateId,parentPath from goods_categary where  FIND_IN_SET($cateId,parentPath)";
        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssocList();
        if ($rows) {
            $childSql='';
            foreach($rows as $k=>$item){
                $id = $cateId;
                $newP = '10024,10026';
                $str = '10025,10026,10000';

                $pattern = '/(.*)' . $cateId . '/i';
                if($parentPath){
                    $newParentPath=$parentPath.','.$cateId;
                }else{
                    $newParentPath=$cateId;
                }
                
                //当前子分类新的节点路径
                $childNewParentPath = preg_replace($pattern, $newParentPath, $item['parentPath']);
                //当前子分类新的子元素数量，子节点数量这个还跟原来的一样无需更新
//                $childNum=$this->getCateChildNums($item['cateId']);
//                $childSql.="update goods_categary set parentPath='$childNewParentPath',childNums='$childNum' where cateId='$item[cateId]';";
                $childSql.="update goods_categary set parentPath='$childNewParentPath' where cateId='$item[cateId]';";
            }
            if($childSql){
                return $this->hydb->query($childSql);
            }
        }
        return false;
    }

    /**
     * 更新当前分类的子元素数量
     * @param type $cateId
     * @return boolean
     */
    private function upChildNums($cateId){
        if(!$cateId) return false;        
        $num = $this->getCateChildNums($cateId);
        $sql = "update goods_categary set childNums='$num' where cateId=$cateId";
        $result = $this->hydb->query($sql);
        if ($result === false) {
            $error = $this->hydb->ErrorMsg();
            die("$error");
        }
        return true;
    }
    /**
     * 获取分类下子节点的数量
     * @param type $cateId
     * @return boolean
     */
    private function getCateChildNums($cateId){
        if(!$cateId) return false;
        $query = "select count(cateId) from goods_categary where parentId=$cateId";
        $this->hydb->setQuery($query);
        $num = $this->hydb->loadResult();
        return $num;
    }
    
    private function getParentPath($parentId){
        $query = "select parentId from goods_categary where cateId = $parentId";
        $this->hydb->setQuery($query);
        $newParentId = $this->hydb->loadResult();
        
        static $parentPath = '';
        if($newParentId!=0){
            $this->getParentPath($newParentId);
            $parentPath = $newParentId;
        }
        
        if(!empty($parentPath)){
            $result = $parentPath.','.$parentId;
        }else{
            $result = $parentId;
        }
        return $result;
    }
    
    /**
     * 获取分类的子分类;如果$shopGcate有值则只显示店铺的分类数据
     * @param int $parentId 分类的父级节点id
     * @param string $shopGcate 店铺的所有分类id，字符串形式逗号分隔;默认为空
     * @return array
     */
    public function getNodeGcate($parentId=0){
        $query = "select a.cateId as id, a.cateName as name, a.parentPath from goods_categary a where a.parentId=$parentId and a.status=1";
        $query .=" order by a.sort asc,a.cateId desc ";
        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssocList();
        return $rows;
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
	 						"value":"/^[a-zA-Z_\\\\s?\\\/?]{2,}$/",
	 						"message":"%s%长度为2位以上的字母、数字、_、空格的组合"
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
        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssocList();
        return $rows;
    }
}
