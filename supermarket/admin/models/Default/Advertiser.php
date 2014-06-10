<?php

/**
 * @name Default_AdvertiserModel
 * @desc 广告主模块
 * @author Vic
 */
class Default_AdvertiserModel extends BasicModel{

    public function __construct() {
        parent::__construct();
        $this->db = Factory::getDBO('local_jiada');
    }

    public function getAdvertiserList($search = array()) {
        $query = "select a.* from advertiser a where 1=1 ";
        if (isset($search['keyworld']) && $search['keyworld']) {
            //默认显示已审核通过的
            $query .=" and INSTR(a.advertiserName,'{$search['keyworld']}') ";     
        }
        $query .=" order by a.createTime desc ";
        $query .=' limit '.$this->getLimitStart().', '.$this->getLimit();

        $this->db->setQuery($query);
        $rows = $this->db->loadAssocList();

        return $rows;
    }
    
    public function getAdvertiserTotal($search=array()){
        $query = "select count(a.advertiserId) from advertiser a where 1=1 ";
        if (isset($search['keyworld']) && $search['keyworld']) {
            //默认显示已审核通过的
            $query .=" and INSTR(a.advertiserName,'{$search['keyworld']}') ";     
        }
        $this->db->setQuery($query);
        $num = $this->db->loadResult();
        return $num;
    }

    public function getAdvertiserInfo($advertiserId){
        if(!$advertiserId) return false;
        $query = "select * from advertiser where advertiserId='$advertiserId'";
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
        if(!$data['price']){
            $data['price']=0;
        }
        $time=time();
        $sql = "insert advertiser set 
                    advertiserType='{$data['advertiserType']}',
                    advertiserName='{$data['advertiserName']}',
                    price='{$data['price']}',
                    contact='{$data['contact']}',
                    tel='{$data['tel']}',
                    mobile='{$data['mobile']}',
                    createTime='{$time}'";
        $result = $this->db->query($sql);
        if ($result === false) {
            $error = $this->db->ErrorMsg();
            die("$error");
        }
        return true;
    }

    public function edit($data) {
        if(!$data['price']){
            $data['price']=0;
        }
        $sql = "update advertiser set advertiserType='{$data['advertiserType']}',
                    advertiserName='{$data['advertiserName']}',
                    price='{$data['price']}',
                    contact='{$data['contact']}',
                    tel='{$data['tel']}',
                    mobile='{$data['mobile']}'";
        $sql .=" where advertiserId=$data[advertiserId]";
        
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
			 		"value":"advertiserType",
			  		"label":"客户类型",
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
			 		"value":"advertiserName",
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
	 					}
		  			]	
				}
                                
                                ]}';

        return $rules;
    }

}
