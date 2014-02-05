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
            )
        ),
        'Shop' => array(
            '店铺管理' => array(
                '店铺首页' => '/Chaoshi/Shop/index/shopId/'.$shopId,
                '店铺信息' => '/Chaoshi/Shop/edit/shopId/'.$shopId,
                '店铺设置' => '/Chaoshi/Shop/setup/shopId/'.$shopId,
            ),
            '商品管理中心' => array(
                '我的商品' => '/Chaoshi/Shopgoods/index/shopId/'.$shopId,
                '发布商品' => '/Chaoshi/Goods/index/shopId/'.$shopId,
            )
        )
    ),
);
