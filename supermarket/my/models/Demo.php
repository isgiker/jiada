<?php

/**
 * @name DemoModel
 * @desc 商品分类
 * @author Vic
 */
class DemoModel extends BasicModel{

    public function __construct() {
        parent::__construct();
        $this->hydb = Factory::getDBO('local_jiada_chaoshi');
    }
    
    public function aaa(){
        $sql="insert `order_delivery` set orderNo='95510817528610837',userId='95433943100162057',dmId='2',deliveryMode='早上',deliveryTime='1401638400',timeHourStart='5',timeMinuteStart='30',timeHourEnd='7',timeMinuteEnd='30',deliveryFee='0.00',fullMoneyDelivery='0.00',fullMoneyFree='0.00',deliveryDistance='0',isNow='',callToConfirm='-1',payway=''";
        $this->hydb->query($sql);
        echo $this->hydb->getErrorMsg();
    }


}
