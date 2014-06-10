<?php

/**
 * @name Default_AdModel
 * @desc 广告模块
 * @author Vic
 */
class Default_AdModel extends BasicModel{

    public function __construct() {
        parent::__construct();
        $this->db = Factory::getDBO('local_jiada');
    }

    public function getAdList($search = array()) {
        $query = "select a.*,b.advertiserName,c.moduleName,c.sizeLong,c.sizeWidth from ad a,advertiser b,ad_module c where a.advertiserId=b.advertiserId and a.admId=c.admId ";
        if (isset($search['keyworld']) && $search['keyworld']) {
            //默认显示已审核通过的
            $query .=" and INSTR(a.adTitle,'{$search['keyworld']}') ";     
        }
        $query .=" order by a.sort desc,a.adId desc ";
        $query .=' limit '.$this->getLimitStart().', '.$this->getLimit();

        $this->db->setQuery($query);
        $rows = $this->db->loadAssocList();

        return $rows;
    }
    
    public function getAdTotal($search=array()){
        $query = "select count(a.adId) from ad a where 1=1 ";
        if (isset($search['keyworld']) && $search['keyworld']) {
            //默认显示已审核通过的
            $query .=" and INSTR(a.adTitle,'{$search['keyworld']}') ";     
        }
        $this->db->setQuery($query);
        $num = $this->db->loadResult();
        return $num;
    }

    public function getAdInfo($adId){
        $query = "select a.*,b.advertiserName from ad a, advertiser b where a.adId='$adId' and a.advertiserId=b.advertiserId ";
        $this->db->setQuery($query);
        $rows = $this->db->loadAssoc();
        return $rows;
    }
       
    public function uuid_short(){
       $sql = "select uuid_short();";
       $this->db->setQuery($sql);
       $uuid_short = $this->db->loadResult();
       return $uuid_short;
    }

    public function add($data) {
        //地区字段暂时没有用，以后业务扩展需要
        $provinceId=0;
        $cityId=0;
        $sql = "insert ad set 
                    advertiserId='{$data['advertiserId']}',
                    admId='{$data['admId']}',
                    adTitle='{$data['adTitle']}',
                    adContent='{$data['adContent']}',
                    adLink='{$data['adLink']}',
                    startTime='{$data['startTime']}',
                    endTime='{$data['endTime']}',
                    provinceId='{$provinceId}',
                    cityId='{$cityId}',
                    sort='{$data['sort']}',
                    status='1'";
        $result = $this->db->query($sql);
        if ($result === false) {
            $error = $this->db->ErrorMsg();
            die("$error");
        }
        return true;
    }

    public function edit($data) {
        $provinceId=0;
        $cityId=0;
        $sql = "update ad set 
                    admId='{$data['admId']}',
                    adTitle='{$data['adTitle']}',
                    adContent='{$data['adContent']}',
                    adLink='{$data['adLink']}',
                    startTime='{$data['startTime']}',
                    endTime='{$data['endTime']}',
                    provinceId='{$provinceId}',
                    cityId='{$cityId}',
                    sort='{$data['sort']}',
                    status='1'";
        $sql .=" where adId=$data[adId]";
        
        $result = $this->db->query($sql);
        if ($result === false) {
            $error = $this->db->ErrorMsg();
            die("$error");
        }
        return true;
    }

    /**
     * form 验证规则
     */
    public function getRules() {
        $rules = '{"validation":[{
			 		"value":"advertiserId",
			  		"label":"广告主",
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
		  		},{
			 		"value":"adId",
			  		"label":"广告位置",
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
			 		"value":"adTitle",
			  		"label":"广告标题",
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
			 		"value":"adContent",
			  		"label":"广告内容",
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
			 		"value":"adLink",
			  		"label":"广告链接",
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
                                        "value":"startTime",
                                                "label":"开始投放时间",
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
                                        "value":"endTime",
                                                "label":"结束投放",
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
                                        "value":"sort",
                                                "label":"广告排序",
                                                "rules":[
                                                        {
                                                                "name":"trim"
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

}
