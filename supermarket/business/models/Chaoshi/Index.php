<?php

/**
 * @name Chaoshi_IndexModel
 * @desc 商品分类
 * @author root
 */
class Chaoshi_IndexModel extends BasicModel{

    public function __construct() {
        parent::__construct();
        $this->db = Factory::getDBO('local_jiada_chaoshi');
    }

}
