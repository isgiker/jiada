<?php

/**
 * @name Chaoshi_GoodsbrandModel
 * @desc 店铺（仓库）
 * @author Vic Shiwei
 */
class Chaoshi_ShopModel extends BasicModel{

    public function __construct() {
        parent::__construct();
        $this->db = Factory::getDBO('local_jiada');
        $this->hydb = Factory::getDBO('local_jiada_chaoshi');
    }
    
    public function getShopInfo($shopId){
        $query = "select * from shop_basic where shopId='$shopId'";
        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssoc();
        return $rows;
    }
    
    /**
     * 获取商家所有店铺
     * @param int $cateId 分类id 必须
     * @param int $currentShopId 当前店铺id 可选
     * @return array
     */
    public function getShops($businessId, $currentShopId=0){
        if(!$businessId) return false;
        $query = "SELECT a.shopId, a.shopName, a.shopLogo, a.provinceId, a.cityId, a.districtId, a.address, a.status FROM shop_basic a where a.businessId=$businessId";
        if($currentShopId){
            $query .=" and shopId != '$currentShopId' ";
        }
        $query .=" order by a.createTime desc ";

        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssocList();
        return $rows;
    }
    
    public function uuid_short(){
       $sql = "select uuid_short();";
       $this->hydb->setQuery($sql);
       $uuid_short = $this->hydb->loadResult();
       return $uuid_short;
    }

    /**
     * 新建店铺/仓库
     * @param array $data
     * @return boolean
     */
    public function add($data) {
        $time=time();
        $shopId = $this->uuid_short();
        $businessId = @$_COOKIE['businessId'];
        if(!$shopId || !$businessId){
            return false;
        }
        $mobile = $data['countryCode'].'-'.$data['mobile'];
        $sql = "insert shop_basic set 
                                    shopId='$shopId',
                                    businessId='$businessId',
                                    shopName='$data[shopName]',
                                    provinceId='$data[provinceId]',
                                    cityId='$data[cityId]',
                                    districtId='$data[districtId]',
                                    address='$data[address]',
                                    lng='$data[lng]',
                                    lat='$data[lat]',
                                    contact='$data[contact]',
                                    mobile='$mobile',
                                    tel='$data[tel]',                                    
                                    workingTime='$data[workingTime]',
                                    status='-1',
                                    createTime='$time'";
        $result = $this->hydb->query($sql);
        if ($result == false) {
            $error = $this->hydb->ErrorMsg();
            die("$error");
        }
        return $shopId;
    }
    
    /**
     * 编辑店铺/仓库
     * @param array $data
     * @return boolean
     */
    public function edit($data) {
        if(!trim($data['shopId'])){
            return false;
        }
        $mobile = $data['countryCode'].'-'.$data['mobile'];
        $sql = "update shop_basic set 
                                    shopName='$data[shopName]',
                                    provinceId='$data[provinceId]',
                                    cityId='$data[cityId]',
                                    districtId='$data[districtId]',
                                    address='$data[address]',
                                    lng='$data[lng]',
                                    lat='$data[lat]',
                                    contact='$data[contact]',
                                    mobile='$mobile',
                                    tel='$data[tel]',                                    
                                    workingTime='$data[workingTime]',
                                    status='-1'";
        $sql .=" where shopId='$data[shopId]'";
        $result = $this->hydb->query($sql);
        if ($result == false) {
            $error = $this->hydb->ErrorMsg();
            die("$error");
        }
        return $data['shopId'];
    }
    
    /**
     * form 验证规则
     */
    public function getRules() {
        $rules = '{"validation":[{
			 		"value":"shopName",
			  		"label":"店铺名称",
			  		"rules":[
                                                {
	  						"name":"trim"
	  					},
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
                                        "value":"provinceId",
                                                "label":"省市",
                                                "rules":[
                                                        {
                                                                "name":"trim"
                                                        },
                                                        {
                                                                "name":"required",
                                                                "message":"%s%为必填项"
                                                        }
                                                ]
                                },
                                {
                                        "value":"cityId",
                                                "label":"城市",
                                                "rules":[
                                                        {
                                                                "name":"trim"
                                                        },
                                                        {
                                                                "name":"required",
                                                                "message":"%s%为必填项"
                                                        },
                                                        {
                                                                "name":"clearxss"
                                                        }
                                                ]
                                },
                                {
                                        "value":"districtId",
                                                "label":"区县",
                                                "rules":[
                                                        {
                                                                "name":"trim"
                                                        },
                                                        {
                                                                "name":"required",
                                                                "message":"%s%为必填项"
                                                        },
                                                        {
                                                                "name":"clearxss"
                                                        }
                                                ]
                                },
		  		{
			 		"value":"address",
			  		"label":"详细地址",
			  		"rules":[
                                                {
	  						"name":"trim"
	  					},
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
                                        "value":"lng",
                                                "label":"经度",
                                                "rules":[
                                                        {
                                                                "name":"trim"
                                                        },
                                                        {
                                                                "name":"required",
                                                                "message":"%s%为必填项"
                                                        },
                                                        {
                                                                "name":"clearxss"
                                                        }
                                                ]
                                },
                                {
                                        "value":"lat",
                                                "label":"纬度",
                                                "rules":[
                                                        {
                                                                "name":"trim"
                                                        },
                                                        {
                                                                "name":"required",
                                                                "message":"%s%为必填项"
                                                        },
                                                        {
                                                                "name":"clearxss"
                                                        }
                                                ]
                                },
		  		{
			 		"value":"contact",
			  		"label":"联系人",
			  		"rules":[
                                                {
	  						"name":"trim"
	  					},
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
			 		"value":"mobile",
			  		"label":"联系人手机",
			  		"rules":[
                                                {
	  						"name":"trim"
	  					},
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
				}
                                
                                ]}';

        return $rules;
    }
}
