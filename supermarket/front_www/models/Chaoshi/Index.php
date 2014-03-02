<?php

/**
 * @name Chaoshi_IndexModel
 * @desc 商品分类
 * @author Vic
 */
class Chaoshi_IndexModel extends BasicModel{

    public function __construct() {
        parent::__construct();
        $this->hydb = Factory::getDBO('local_jiada_chaoshi');
    }


}
