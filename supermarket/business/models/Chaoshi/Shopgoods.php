<?php

/**
 * @name Chaoshi_GoodsModel
 * @desc 商品管理
 * @author Vic
 */
class Chaoshi_ShopgoodsModel extends BasicModel{

    public function __construct() {
        parent::__construct();
        $this->hydb = Factory::getDBO('local_jiada_chaoshi');
    }
    
    /**
     * 条件：只显示本店铺的数据
     * @param type $search
     * @return type
     */
    public function getGoodsList($shopId, $search = array()) {
        if(!$shopId) return FALSE;
        $query = "select a.*,b.goodsName,b.recTags,c.cateName,d.brandName from goods_price a,goods b,goods_categary c,goods_brand d where a.shopId='$shopId' and a.goodsId=b.goodsId and b.cateId=c.cateId and b.brandId=d.brandId";
        if (isset($search['cateId']) && $search['cateId']) {
            $query .=" and b.cateId = $search[cateId]";
        }
        if (isset($search['brandId']) && $search['brandId']) {
            $query .=" and b.brandId = $search[brandId]";
        }
        if (isset($search['goodsName']) && $search['goodsName']) {
            $query .=" and instr(b.goodsName, '$search[goodsName]')";
        }

        $query .=" order by a.createTime desc ";
        $query .=' limit '.$this->getLimitStart().', '.$this->getLimit();

        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssocList();

        return $rows;
    }
    
    public function getGoodsTotal($shopId, $search = array()){
        if(!$shopId) return FALSE;
        $query = "select count(a.priceId) from goods_price a,goods b,goods_categary c,goods_brand d where a.shopId='$shopId' and a.goodsId=b.goodsId and b.cateId=c.cateId and b.brandId=d.brandId";
        if (isset($search['cateId']) && $search['cateId']) {
            $query .=" and b.cateId = $search[cateId]";
        }
        if (isset($search['brandId']) && $search['brandId']) {
            $query .=" and b.brandId = $search[brandId]";
        }
        if (isset($search['goodsName']) && $search['goodsName']) {
            $query .=" and instr(b.goodsName, '$search[goodsName]')";
        }

        $this->hydb->setQuery($query);
        $num = $this->hydb->loadResult();
        return $num;
    }
   
    public function getGoodsInfo($goodsId){
        $query = "select a.* ,b.goodsDesc,b.goodsPics,b.goodsRemark from goods a left join goods_detail b on a.goodsId=b.goodsId where a.goodsId=$goodsId ";
        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssoc();
        return $rows;
    }
    
    /**
     * 获取商品信息
     * 这里关联shopId，是防止有人篡改goodsId；任何人只能修改自己店铺的商品；
     * @param type $goodsId
     * @param type $shopId
     * @return boolean
     */
    public function getGoodsPriceInfo($goodsId, $shopId){
        if(!$goodsId || !$shopId) return FALSE;
        $query = "select a.*,b.goodsName,b.recTags,b.packPic,b.cateId,b.brandId,c.cateName,d.brandName from goods_price a,goods b,goods_categary c,goods_brand d  where a.goodsId=$goodsId and a.shopId=$shopId and a.goodsId=b.goodsId and b.cateId=c.cateId and b.brandId=d.brandId";
        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssoc();
        return $rows;
    }
    
    /**
     * 获取该店铺的所有商品分类
     * 店铺本身是没有自己的分类数据，只能通过发布的所有商品id算出所有的分类id，然后通过排除法最终能展示出店铺的分类
     * @param int $shopId
     * @return string 返回店铺分类id的字符串，逗号分隔
     */
    public function getShopGcates($shopId) {
        //得到店铺的分类id
        $query = "select cateId,parentPath from goods_categary where cateId in(select cateId from goods where goodsId in(select a.goodsId from goods_price a  where a.shopId = '$shopId') group by cateId)";
        $this->hydb->setQuery($query);
        $gcates = $this->hydb->loadAssocList();
        $data=array();
        $cateidStr='';
        if($gcates && is_array($gcates)){
            foreach($gcates as $key => $item){
                if($item['parentPath']){
                    $cateParentPath=$item['parentPath'].','.$item['cateId'];
                }else{
                    $cateParentPath=$item['cateId'];
                }
                if($cateidStr){
                    $cateidStr.=','.$cateParentPath;
                }else{
                    $cateidStr=$cateParentPath;
                }
                
                
            }
        }
        
        if($cateidStr){
            $data=explode(',', $cateidStr);
            //去重
            $data = array_unique($data);
            $cateidStr = implode(',', $data);
        }
        return $cateidStr;
    }
    
    /**
     * 获取该店铺的所有商品品牌
     * 店铺本身是没有自己的品牌数据，只能通过发布的所有商品id算出所有的品牌id，然后通过排除法最终能展示出店铺的品牌数据
     * @param int $shopId
     * @return string 返回店铺品牌id的字符串，逗号分隔
     */
    public function getShopGbrands($shopId) {
        //得到店铺的分类id
        $query = "select b.brandId from goods_price a,goods b where a.shopId='$shopId' and a.goodsId=b.goodsId ";
        $this->hydb->setQuery($query);
        $shopBrands = $this->hydb->loadAssocList();
        $data=array();
        $brandIdStr='';
        if($shopBrands && is_array($shopBrands)){
            foreach($shopBrands as $key => $item){
                if($brandIdStr){
                    $brandIdStr.=','.$item['brandId'];
                }else{
                    $brandIdStr=$item['brandId'];
                }
                
            }
        }
        
        if($brandIdStr){
            $data=explode(',', $brandIdStr);
            //去重
            $data = array_unique($data);
            $brandIdStr = implode(',', $data);
        }
        return $brandIdStr;
    }



    public function edit($data) {
        if(!$data['goodsId'] || !$data['shopId']) return FALSE;
        $activityStartTime = strtotime($data['activityStartTime']);
        $activityEndTime = strtotime($data['activityEndTime']);
        $query = "update goods_price set originalPrice='$data[originalPrice]',discount='$data[discount]',currentPrice='$data[currentPrice]',marketPrice='$data[marketPrice]',activityStartTime='$activityStartTime',activityEndTime='$activityEndTime',status='$data[status]' where goodsId='$data[goodsId]' and shopId='$data[shopId]';";
        $result = $this->hydb->query($query);
        if ($result == false) {
            $error = $this->hydb->ErrorMsg();
            die("$error");
        }
        return true;
    }
    
    public function uuid_short(){
       $sql = "select uuid_short();";
       $this->hydb->setQuery($sql);
       $uuid_short = $this->hydb->loadResult();
       return $uuid_short;
    }
    
    
    /**
     * 商品进货记录
     * @param array $data
     * @return int
     */
    public function stock($data) {        
        if(isset($_COOKIE['uname']) && $_COOKIE['uname']){
            $operator = @$_COOKIE['uname'];
        }else{
            return false;
        }
        $putinTime = strtotime($data['putinTime']);
        $stockId=$this->uuid_short();
        $time=time();
        $query = "insert goods_stock set stockId='$stockId',goodsId='$data[goodsId]',shopId='$data[shopId]',quantity='$data[quantity]',purchasePrice='$data[purchasePrice]',operator='$operator',putinTime='$putinTime',createTime='$time';";
        $result = $this->hydb->query($query);
        if ($result == false) {
            $error = $this->hydb->ErrorMsg();
            die("$error");
        }
        return true;
    }
    
    public function getStocklist($goodsId, $shopId){
        if(!$goodsId || !$shopId) return FALSE;
        $query = "select a.*,b.goodsName from goods_stock a,goods b where a.goodsId = $goodsId and a.shopId=$shopId and a.goodsId=b.goodsId";
        $query .=" order by a.createTime desc ";
        $query .=' limit '.$this->getLimitStart().', '.$this->getLimit();

        $this->hydb->setQuery($query);
        return $this->hydb->loadAssocList();
    }
    
    public function getStockTotal($goodsId, $shopId){
        if(!$goodsId || !$shopId) return 0;
        $query = "select count(a.stockId) from goods_stock a,goods b where a.goodsId = $goodsId and a.shopId=$shopId and a.goodsId=b.goodsId";

        $this->hydb->setQuery($query);
        $num = $this->hydb->loadResult();
        return $num;
    }
    
    public function getRules() {
        $rules = '{"validation":[
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
	 						"value":"/^\\\d+(\\\.\\\d{1,2})?$/",
	 						"message":"%s%必须是整数或小数"
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
	 						"value":"/^\\\d+(\\\.\\\d{1,2})?$/",
	 						"message":"%s%必须是整数或小数"
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
	 						"value":"/^\\\d+(\\\.\\\d{1,2})?$/",
	 						"message":"%s%必须是整数或小数"
	 					}
		  			]	
				}
                                ]}';

        return $rules;
    }

    public function getStockRules() {
        $rules = '{"validation":[
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
