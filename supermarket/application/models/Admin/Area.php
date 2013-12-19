<?php

/**
 * @name SampleModel
 * @desc sample数据获取类, 可以访问数据库，文件，其它系统等
 * @author root
 */
class Admin_AreaModel {

    public function __construct() {
        
    }

    public function add($data) {
        print_r($data);exit;
        $sql ="";
        return true;
    }

    public function edit($data) {
        return true;
    }

    /**
     * form 验证规则
     */
    public function getRules() {
        $rules = '{"validation":[{
			 		"value":"username",
			  		"label":"用户名",
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
		  					"value":"/^[A-Za-z0-9_\\\\-]{6,20}$/",
			  				"message":"%s%应为6到20位的数字、字符和下划线"
	  					}
		  			]	
		  		},
		  		{
			 		"value":"password",
			  		"label":"密码",
			  		"rules":[
						{
	  						"name":"clearxss"
	  					},
						{
	 						"name":"required",
	 						"message":"%s%为必填项"
	 					},
	 					{
	 						"name":"rangelength",
	 						"value":"[6,20]",
	 						"message":"%s%长度为6到20位"
	 					}
		  			]	
				}]}';
        
        return $rules;
    }

}
