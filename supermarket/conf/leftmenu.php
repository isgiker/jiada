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
        '仓库管理中心'=>array(
            '仓库管理'=>'/Default/Storehouse/index',
            '新增仓库'=>'/Default/Storehouse/add'
            ),
        '权限管理中心'=>array(
            '用户组管理'=>'/Default/Agroup/index',
            '新增用户组'=>'/Default/Agroup/add',
            '1'=>'keep_spacing',
            '管理员管理'=>'/Default/Admin/index',
            '新增管理员'=>'/Default/Admin/add'
            ),
    ),
    'Chaoshi'=>array(
        '商品管理中心'=>array(
            '商品分类管理'=>'/Chaoshi/Goodscate/index',
            '添加分类'=>'/Chaoshi/Goodscate/add',
            '1'=>'keep_spacing',
            '商品品牌管理'=>'/Chaoshi/Goodsbrand/index',
            '添加品牌'=>'#',
            '2'=>'keep_spacing',
            '商品管理'=>'/Chaoshi/Goods/index',
            '添加商品'=>'/Chaoshi/Goods/add',
            )
    )
    
);
