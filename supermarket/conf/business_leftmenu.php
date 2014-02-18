<?php

/**
 * 商家管理后台
 * 左侧导航，不同行业有不同的导航，管理后台根据行业切换
 */
$leftMenu = array(
    'Chaoshi' => array(
        'Business' => array(
            '店铺管理' => array(
                '商家首页' => '/Chaoshi/Index/index',
                '新建店铺' => '/Chaoshi/Shop/add'
            ),
            '权限管理中心'=>array(
            '用户组管理'=>'/Chaoshi/Agroup/index',
            '新增用户组'=>'/Chaoshi/Agroup/add',
            '1'=>'keep_spacing',
            '管理员管理'=>'/Chaoshi/Admin/index',
            '新增管理员'=>'/Chaoshi/Admin/add'
            ),
        ),
        'Shop' => array(
            '店铺管理' => array(
                '店铺首页' => '/Chaoshi/Shop/index/shopId/'.$currentShopId,
                '店铺信息' => '/Chaoshi/Shop/edit/shopId/'.$currentShopId,
                '店铺Logo' => '/Chaoshi/Shop/logo/shopId/'.$currentShopId,
                '店铺设置' => '/Chaoshi/Shop/setup/shopId/'.$currentShopId,
            ),
            '商品管理中心' => array(
                '我的商品' => '/Chaoshi/Shopgoods/index/shopId/'.$currentShopId,
                '发布商品' => '/Chaoshi/Goods/index/shopId/'.$currentShopId,
            )
        )
    ),
);
