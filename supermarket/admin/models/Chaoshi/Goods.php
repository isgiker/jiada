<?php

/**
 * @name Chaoshi_GoodsModel
 * @desc 商品管理
 * @author Vic
 */
class Chaoshi_GoodsModel extends BasicModel{

    public function __construct() {
        parent::__construct();
        $this->db = Factory::getDBO('local_jiada_chaoshi');
    }

    public function getGoodsList($search = array()) {
        $query = "select a.*,b.cateName,c.brandName from goods a,goods_categary b,goods_brand c where a.cateId=b.cateId and a.brandId=c.brandId";
        if (isset($search['cateId']) && $search['cateId'] != 'all') {
            $query .=" and a.cateId = $search[cateId]";
        }
        if (isset($search['onLine']) && $search['onLine'] >=0) {
            $query .=" and a.onLine = $search[onLine]";
        }else{
            $query .=" and a.onLine = 1";
        }

        $query .=" order by a.publishTime desc ";
        $query .=' limit '.$this->getLimitStart().', '.$this->getLimit();

        $this->db->setQuery($query);
        $rows = $this->db->loadAssocList();

        return $rows;
    }
    
    public function getGoodsTotal($search = array()){
        $query = "select count(goodsId) from goods where 1=1";
        if (isset($search['onLine']) && $search['onLine'] != 'all' && $search['onLine'] > 0) {
            $query .=" and onLine = $search[onLine]";
        }

        $this->db->setQuery($query);
        $num = $this->db->loadResult();
        return $num;
    }
   
    public function getGoodsInfo($goodsId){
        $query = "select a.* ,b.goodsDesc,b.goodsPics,b.goodsRemark from goods a left join goods_detail b on a.goodsId=b.goodsId where a.goodsId=$goodsId ";
        $this->db->setQuery($query);
        $rows = $this->db->loadAssoc();
        return $rows;
    }
    
    public function getGoodsPriceInfo($goodsId){
        $query = "select a.cateId,a.brandId,a.goodsName,a.recTags,a.onLine,a.goodsId,b.*,(select cateName from goods_categary where cateId=a.cateId)as cateName,(select brandName from goods_brand where brandId=a.brandId)as brandName   from goods a left join goods_price b on a.goodsId=b.goodsId where a.goodsId=$goodsId ";
        $this->db->setQuery($query);
        $rows = $this->db->loadAssoc();
        return $rows;
    }
    
    public function childCateType($cateId){
        $query = "select cateId,cateName,parentPath from goods_categary where parentId=$cateId";
        $this->db->setQuery($query);
        $rows = $this->db->loadAssocList();
        return $rows;
    }

    public function add($data) {
        $time = time();
        $query = "insert goods set cateId='$data[cateId]',brandId='$data[brandId]',goodsName='$data[goodsName]',originalPrice='$data[originalPrice]',discount='$data[discount]',currentPrice='$data[currentPrice]',marketPrice='$data[marketPrice]',activityStartTime='$data[activityStartTime]',activityEndTime='$data[activityEndTime]',onLine='$data[onLine]',recTags='$data[recTags]',publishTime='$time';";

        $result = $this->db->query($query);
        if ($result == false) {
            $error = $this->db->ErrorMsg();
            die("$error");
        }
        $goodsId = $this->db->insertid();
        return $goodsId;
    }

    public function edit($data) {
        $time = time();
        $query = "update goods set cateId='$data[cateId]',brandId='$data[brandId]',goodsName='$data[goodsName]',originalPrice='$data[originalPrice]',discount='$data[discount]',currentPrice='$data[currentPrice]',marketPrice='$data[marketPrice]',activityStartTime='$data[activityStartTime]',activityEndTime='$data[activityEndTime]',onLine='$data[onLine]',recTags='$data[recTags]',modifyTime='$time' where goodsId=$data[goodsId];";

        $result = $this->db->query($query);
        if ($result == false) {
            $error = $this->db->ErrorMsg();
            die("$error");
        }
        return true;
    }
    
    public function price($data) {
        $time = time();
        $query = "replace into goods_price set storehouseId='$data[storehouseId]',goodsId='$data[goodsId]',originalPrice='$data[originalPrice]',discount='$data[discount]',currentPrice='$data[currentPrice]',marketPrice='$data[marketPrice]',activityStartTime='$data[activityStartTime]',activityEndTime='$data[activityEndTime]';";

        $result = $this->db->query($query);
        if ($result == false) {
            $error = $this->db->ErrorMsg();
            die("$error");
        }
        return true;
    }
    
    /**
     * 商品进货记录
     * @param array $data
     * @return int
     */
    public function stock($data) {
        $time = time();
        $query = "insert goods_stock set goodsId='$data[goodsId]',storehouseId='$data[storehouseId]',quantity='$data[quantity]',purchasePrice='$data[purchasePrice]',operator='No User',putinTime='$time';";
        $result = $this->db->query($query);
        if ($result == false) {
            $error = $this->db->ErrorMsg();
            die("$error");
        }
        return true;
    }
    
    public function getStocklist($goodsId, $storehouseId){
        $query = "select a.*,(select goodsName from goods where goodsId=$goodsId)as goodsName  from goods_stock a  where a.goodsId = $goodsId and a.storehouseId=$storehouseId";
        $query .=" order by a.putinTime desc ";
        $query .=' limit '.$this->getLimitStart().', '.$this->getLimit();

        $this->db->setQuery($query);
        return $this->db->loadAssocList();
    }
    
    /**
     * 商品详情
     * @param array $data only goodsId
     * @return boolean
     */
    public function detail($data) {
        $time = time();
        $query = "replace into goods_detail set goodsDesc='$data[goodsDesc]',goodsId=$data[goodsId];";

        $result = $this->db->query($query);
        if ($result == false) {
            $error = $this->db->ErrorMsg();
            die("$error");
        }
        return true;
    }
    
    /**
     * 更新商品包装图片
     * @param string $packPic 包装图片路径
     * @param int $goodsId 商品id
     * @param string $eventType 事件类型del|update
     * @return boolean
     */
    public function upGoodsPackPic($packPic, $goodsId, $eventType='update'){
        if($eventType=='del'){
            $query = "update goods set packPic='$packPic' where goodsId=$goodsId;";
        }else{
            $query = "update goods set packPic=concat_ws(',',packPic,'$packPic') where goodsId=$goodsId;";
        }
        $result = $this->db->query($query);
        if ($result == false) {
            $error = $this->db->ErrorMsg();
            die("$error");
        }
        return true;
    }
    
    /**
     * 保存商品属性
     * @param array $data post过来的数据
     * @return boolean
     */
    public function attr($data){
        $query = '';
        if(isset($data['attr_input'])){
            foreach($data['attr_input'] as $attrId=>$attrValue){
                if(trim($attrValue)){
                    
                    //有的属性值是多选如checkbox选型；转化成字符串
                    if(is_array($attrValue)){
                        $attrValue= implode('、', $attrValue);
                        $attrValue=trim($attrValue);
                    }
                    $query .= "REPLACE INTO goods_attribute_value set goodsId='$data[goodsId]',attrId='$attrId',attrValue='$attrValue';";
                }
            }
        }
        
        $result = $this->db->query($query);
        if ($result == false) {
            $error = $this->db->ErrorMsg();
            die("$error");
        }
        return true;
    }

    /**
     * 根据商品分类获取商品属性
     * @param int $goodsCateId 商品分类id
     * @return array
     */
    public function getGoodsAttr($goodsCateId){
        //商品属性分类
        $query = "select * from goods_attribute_categary where goodsCateId=$goodsCateId";
        $query .=" order by sort asc,attrCateId desc ";
        $this->db->setQuery($query);
        $attrCateRows = $this->db->loadAssocList();
        //根据商品分类和属性分类获取属性项
        foreach ($attrCateRows as $key => $value) {
            $q = "select a.* from goods_attribute a where a.goodsCateId=$goodsCateId and attrCateId=$value[attrCateId]";
            $q .=" order by a.attrCateId,a.sort,a.createTime asc";
            $this->db->setQuery($q);
            $attrRows = $this->db->loadAssocList();
            if(!empty($attrRows)){
                $attrCateRows[$key]['attr']=$attrRows;
            }else{
                unset($attrCateRows[$key]);
            }
        }
        return $attrCateRows;
    }
    
    public function getGoodsAttrVaules($goodsId){
        $query = "select * from goods_attribute_value where goodsId=$goodsId";
        $this->db->setQuery($query);
        $attrValueRows = $this->db->loadAssocList();
        $newArray=array();
        if ($attrValueRows) {
            foreach ($attrValueRows as $key => $value) {
                $newArray[$value['attrId']] = $value['attrValue'];
            }
        }
        return $newArray;
    }

    /**
     * form 验证规则
     */
    public function getRules() {
        $rules = '{"validation":[{
			 		"value":"cateId",
			  		"label":"商品分类",
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
				},
                                {
			 		"value":"brandId",
			  		"label":"商品品牌",
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
				},
                                {
			 		"value":"goodsName",
			  		"label":"商品名称",
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
			 		"value":"originalPrice",
			  		"label":"商品原价",
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
	 						"value":"/^([0-9]+)[.]([0-9]{1,2})$/",
	 						"message":"%s%必须为数字格式为：19.88"
	 					}
		  			]	
				},
                                {
			 		"value":"currentPrice",
			  		"label":"商品现价",
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
	 						"value":"/^([0-9]+)[.]([0-9]{1,2})$/",
	 						"message":"%s%必须为数字格式为：19.88"
	 					}
		  			]	
				},
                                {
			 		"value":"discount",
			  		"label":"折扣比例",
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
	 						"value":"/^([0]?)[.]*([0-9]{0,2})$/",
	 						"message":"%s%必须为数字格式为：0.85"
	 					}
		  			]	
				},
		  		{
			 		"value":"onLine",
			  		"label":"是否上架",
			  		"rules":[
						{
	  						"name":"clearxss"
	  					},
						{
	 						"name":"required",
	 						"message":"%s%为必填项"
	 					}
		  			]	
				}
                                ]}';

        return $rules;
    }
    
    public function getPriceRules() {
        $rules = '{"validation":[{
			 		"value":"storehouseId",
			  		"label":"所属仓库",
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
			 		"value":"originalPrice",
			  		"label":"商品原价",
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
	 						"value":"/^([0-9]+)[.]([0-9]{1,2})$/",
	 						"message":"%s%必须为数字格式为：19.88"
	 					}
		  			]	
				},
                                {
			 		"value":"currentPrice",
			  		"label":"商品现价",
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
	 						"value":"/^([0-9]+)[.]([0-9]{1,2})$/",
	 						"message":"%s%必须为数字格式为：19.88"
	 					}
		  			]	
				},
                                {
			 		"value":"discount",
			  		"label":"折扣比例",
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
	 						"value":"/^([0]?)[.]*([0-9]{0,2})$/",
	 						"message":"%s%必须为数字格式为：0.85"
	 					}
		  			]	
				}
                                ]}';

        return $rules;
    }
    
    public function getStockRules() {
        $rules = '{"validation":[{
			 		"value":"storehouseId",
			  		"label":"所属仓库",
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
			 		"value":"quantity",
			  		"label":"进货数量",
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
	 						"message":"%s%必须为数字"
	 					}
		  			]	
				},
                                {
			 		"value":"purchasePrice",
			  		"label":"进货价格",
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
	 						"value":"/^([0-9]+)[.]([0-9]{1,2})$/",
	 						"message":"%s%必须为数字格式为：19.88"
	 					}
		  			]	
				}
                                ]}';

        return $rules;
    }

}
