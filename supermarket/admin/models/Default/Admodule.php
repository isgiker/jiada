<?php

/**
 * @name Default_AdmoduleModel
 * @desc 广告模块
 * @author Vic
 */
class Default_AdmoduleModel extends BasicModel{

    public function __construct() {
        parent::__construct();
        $this->db = Factory::getDBO('local_jiada');
    }

    public function getAdmoduleList($search = array()) {
        $query = "select a.* from ad_module a where 1=1 ";
        if (isset($search['keyworld']) && $search['keyworld']) {
            //默认显示已审核通过的
            $query .=" and INSTR(a.moduleName,'{$search['keyworld']}') ";     
        }
        $query .=" order by a.admId desc ";
        $query .=' limit '.$this->getLimitStart().', '.$this->getLimit();

        $this->db->setQuery($query);
        $rows = $this->db->loadAssocList();

        return $rows;
    }
    
    public function getAdmoduleTotal($search=array()){
        $query = "select count(a.admId) from ad_module a where 1=1 ";
        if (isset($search['keyworld']) && $search['keyworld']) {
            //默认显示已审核通过的
            $query .=" and INSTR(a.moduleName,'{$search['keyworld']}') ";     
        }
        $this->db->setQuery($query);
        $num = $this->db->loadResult();
        return $num;
    }

    public function getAdmoduleInfo($admId){
        if(!$admId){
            return false;
        }
        $query = "select * from ad_module where admId='$admId'";
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
        $sql = "insert ad_module set 
                    pageName='{$data['pageName']}',
                    pageLink='{$data['pageLink']}',
                    moduleName='{$data['moduleName']}',
                    moduleType='{$data['moduleType']}',
                    sizeLong='{$data['sizeLong']}',
                    sizeWidth='{$data['sizeWidth']}',
                    maxList='{$data['maxList']}'";
        $result = $this->db->query($sql);
        if ($result === false) {
            $error = $this->db->ErrorMsg();
            die("$error");
        }
        return true;
    }

    public function edit($data) {
        $sql = "update ad_module set pageName='{$data['pageName']}',
                    pageLink='{$data['pageLink']}',
                    moduleName='{$data['moduleName']}',
                    moduleType='{$data['moduleType']}',
                    sizeLong='{$data['sizeLong']}',
                    sizeWidth='{$data['sizeWidth']}',
                    maxList='{$data['maxList']}'";
        $sql .=" where admId=$data[admId]";
        
        $result = $this->db->query($sql);
        if ($result === false) {
            $error = $this->db->ErrorMsg();
            die("$error");
        }
        return true;
    }
    
    //获取所有广告位置
    public function getAdmodules() {
        $query = "select a.admId,a.moduleName from ad_module a ";
        $query .=" order by a.admId desc ";

        $this->db->setQuery($query);
        $rows = $this->db->loadAssocList();

        return $rows;
    }
    

    /**
     * form 验证规则
     */
    public function getRules() {
        $rules = '{"validation":[{
			 		"value":"pageName",
			  		"label":"页面名称",
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
			 		"value":"moduleName",
			  		"label":"广告模块名称",
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
			 		"value":"moduleType",
			  		"label":"模块类型",
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
                                        "value":"sizeLong",
                                                "label":"尺寸（长）",
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
                                },
                                {
                                        "value":"sizeWidth",
                                                "label":"尺寸（宽）",
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
                                },
                                {
                                        "value":"maxList",
                                                "label":"最大数量",
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
