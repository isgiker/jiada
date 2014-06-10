<?php

/*
 * 左侧导航，不同行业有不同的导航，管理后台根据行业切换
 */
$leftMenu = array(
    'Default'=>array(
        '用户管理中心'=>array(
            '商家管理'=>'/Default/Business/index',
            '新增商家'=>'/Default/Business/add'
            ),
        '区域管理中心'=>array(
            '地区管理'=>'/Default/Area/index',
            '新增地区'=>'/Default/Area/add'
            ),
        
        '权限管理中心'=>array(
            '用户组管理'=>'/Default/Agroup/index',
            '新增用户组'=>'/Default/Agroup/add',
            '1'=>'keep_spacing',
            '管理员管理'=>'/Default/Admin/index',
            '新增管理员'=>'/Default/Admin/add'
            ),
        '广告管理中心'=>array(
            '广告模块'=>'/Default/Admodule/index',
            '添加广告模块'=>'/Default/Admodule/add',
            '1'=>'keep_spacing',
            '广告主'=>'/Default/Advertiser/index',
            '添加广告主'=>'/Default/Advertiser/add',
            '2'=>'keep_spacing',
            '广告列表'=>'/Default/Ad/index'
            ),
    ),
    'Chaoshi'=>array(
        '店铺管理中心'=>array(
            '店铺管理'=>'/Chaoshi/Shop/index'
            ),
        '商品管理中心'=>array(
            '商品分类管理'=>'/Chaoshi/Goodscate/index',
            '添加分类'=>'/Chaoshi/Goodscate/add',
            '2'=>'keep_spacing',
            '商品管理'=>'/Chaoshi/Goods/index',
            '添加商品'=>'/Chaoshi/Goods/add',
            )
    )
    
);
