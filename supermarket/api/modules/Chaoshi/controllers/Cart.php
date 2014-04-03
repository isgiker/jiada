<?php

/**
 * @name CartController
 * @author Vic Shiwei
 * @desc 购物车API
 */
class CartController extends Core_Controller_Api{
    public $_config;
    protected $model;

    public function init() {
        parent::init();
        $this->_config = Yaf_Registry::get('_CONFIG');
        Yaf_Loader::import('phprpc/server/phprpc_server.php');
        Yaf_Loader::import('phprpc/server/dhparams.php');
        Yaf_Loader::import('phprpc/common/bigint.php');
        Yaf_Loader::import('phprpc/common/compat.php');
        Yaf_Loader::import('phprpc/common/phprpc_date.php');
        Yaf_Loader::import('phprpc/common/xxtea.php');
        $this->model = new Chaoshi_CartModel();
    }
    
    public function indexAction() {
        //禁止缓存
//        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        $this->_layout = false;        
        $phprpcServer = new PHPRPC_Server();
        $phprpcServer->add(array('add','edit','getCartItems','recount'),  $this);
        
        $phprpcServer->start();
    }
    
    /**
     * 加入购物车（只执行加入或累计加入的操作）
     * @param string $newCartItem 格式：店铺id-商品价格id-数量
     * @return array|json
     */
    public function add($parameter) {
        //验证参数,未登录情况下用户id是空的；
        if (!isset($parameter['cartItem']) || !$parameter['cartItem'] || !isset($parameter['userId']) || !isset($parameter['userKey']) || !$parameter['userKey']) {
            return $this->errorMessage('请求参数错误！');
        }
        
        //验证数据格式：店铺id-商品id-数量
        $inputPattern = '/([0-9a-zA-Z]+)-([0-9a-zA-Z]+)-(\d+)$/i';
        $isRight = preg_match($inputPattern, $parameter['cartItem']);
        if (!$isRight) {
            return $this->errorMessage('请求参数格式不正确！');
        }
        
        //重组数据
        $cartItemArr = explode('-', $parameter['cartItem']);
        $parameter['shopId'] = $cartItemArr[0];
        $parameter['goodsPriceId'] = $cartItemArr[1];
        if(!$cartItemArr[2]){
            $cartItemArr[2]=1;
        }
        $parameter['buyNum'] = $cartItemArr[2];

        //将该商品放进购物车表
        $r=$this->model->putinCart($parameter);
        if(!$r){
            return $this->errorMessage('写入数据库失败！');
        }
        
        //统计购物车内所有商品总数量
        $cn=$this->model->countCart($parameter);
        $data=array('cn'=>$cn);
        return $this->returnData($data);
    }
    
    
    /**
     * 修改购物车,如果数量等于0删除购物车内数据
     * @param string $newCartItem 格式：店铺id-商品价格id-数量
     * @return array|json
     */
    public function edit($parameter) {
        //验证参数,未登录情况下用户id是空的；
        if (!isset($parameter['cartItem']) || !$parameter['cartItem'] || !isset($parameter['userId']) || !isset($parameter['userKey']) || !$parameter['userKey']) {
            return $this->errorMessage('请求参数错误！');
        }
        
        //验证数据格式：店铺id-商品id-数量
        $inputPattern = '/([0-9a-zA-Z]+)-([0-9a-zA-Z]+)-(\d+)$/i';
        $isRight = preg_match($inputPattern, $parameter['cartItem']);
        if (!$isRight) {
            return $this->errorMessage('请求参数格式不正确！');
        }
        
        //重组数据
        $cartItemArr = explode('-', $parameter['cartItem']);
        $parameter['shopId'] = $cartItemArr[0];
        $parameter['goodsPriceId'] = $cartItemArr[1];        
        $parameter['buyNum'] = $cartItemArr[2];

        //将该商品放进购物车表
        $r=$this->model->updateCart($parameter);
        if(!$r){
            return $this->errorMessage('写入数据库失败！');
        }
        
        //统计购物车内所有商品总数量
        $goodsCn=$this->model->countCart($parameter);
        if (!$goodsCn)
            $goodsCn = 0;
        $data=array('goodsCn'=>$goodsCn);
        return $this->returnData($data);
    }
    
    /**
     * 获取用户购物车内所有商品
     * @param string $userId 登录情况：根据用户id获取用户购物车内商品数据
     * @param string $userKey 未登录情况：根据用户key获取用户购物车内商品数据
     * @return array|json
     */
    public function getCartData($parameter){
        //验证参数,未登录情况下用户id是空的；
//        if (!isset($parameter['userId']) || !isset($parameter['userKey']) || !$parameter['userKey']) {
//            return $this->errorMessage('请求参数错误！');
//        }
        
        //根据参数获取数据
        $cartItems=$this->model->getCartItems($parameter);
        
        //根据商品价格id关联商品数据
        $goodsPriceId_arr=array();
        if ($cartItems) {
            $new_cartItems=array();
            foreach($cartItems as $item) {
                $goodsPriceId_arr[]=$item['goodsPriceId'];
                $new_cartItems[$item['goodsPriceId']]=$item;
            }
            if($new_cartItems){
                $cartItems=$new_cartItems;
            }
        }
        
        //根据商品价格id获取商品数据
        $goodsItems=$this->model->getGoodsItems($goodsPriceId_arr);
        
        //按照店铺id分组重新排列数据
        $shopGoods=array();
        $shopId_str='';
        if($goodsItems && isset($goodsItems[0]) && $goodsItems[0]){
            foreach ($goodsItems as $k => $item) {
                if($item['shopId']){
                    $shopGoods[$item['shopId']][]=$item;
                    if(!$shopId_str){
                        $shopId_str=$item['shopId'];
                    }else{
                        $shopId_str.=','.$item['shopId'];
                    }
                }
            }
            
        }
        
        //关联店铺信息、店铺活动等
        $shops=$this->model->getShops($shopId_str);
        if ($shops) {
            $new_shops=array();
            foreach($shops as $item) {
                $new_shops[$item['shopId']]=$item;
            }
            if($new_shops){
                $shops=$new_shops;
            }
        }
        
        $data=array('cartItems'=>$cartItems,'goodsItems'=>$shopGoods,'shops'=>$shops);
        
        return $data;
    }
    
    /**
     * 重新计算购物车价格：活动、配送费、原价、总价、应付等..这些价格随着数量、金额、活动等因素发生变化。
     * （支持两种模式调用同步和异步）
     * @param type $parameter userId|userKey
     */
    public function recount($parameter) {
        //验证参数,未登录情况下用户id是空的；
        if (!isset($parameter['userId']) || !isset($parameter['userKey']) || !$parameter['userKey']) {
            return $this->errorMessage('请求参数错误！');
        }
        //获取购物车数据
        $cartData = $this->getCartData($parameter);
        
        //开始计算购物车内商品价格...................................begin;
        //商品总数量统计
        $goodsCn=0;
        //商品原总价
        $originalPriceTotal = 0;
        //商品现总价
        $currentPriceTotal = 0;
        if(isset($cartData['goodsItems']) && $cartData['goodsItems']){
            foreach($cartData['goodsItems'] as $shopId => $shopGoods){
                //店铺循环
                if(isset($shopGoods[0]) && $shopGoods[0]){
                    //店铺内商品循环
                    foreach($shopGoods as $k => $p){
                        if (isset($cartData['cartItems'][$p['priceId']]['buyNum']) && $cartData['cartItems'][$p['priceId']]['buyNum']) {
                            $buyNum = $cartData['cartItems'][$p['priceId']]['buyNum'];
                        } else {
                            $buyNum = 0;
                        }
                        $goodsCn+=$buyNum;
                        $originalPriceTotal += $p['originalPrice'] * $buyNum;
                        $currentPriceTotal += $p['currentPrice'] * $buyNum;
                    }
                }
            }
        }
        
        //开始计算配送费.............................................begin
        //暂时没有配送费以后追加计算
        $deliveryFee = 0;
        
        //开始计算活动.............................................begin
        //活动应减金额（暂时没有活动以后追加计算）
        $lower = 0;
        //如果活动是价格则应付金额-活动优惠价，否则该值为0；活动类型不同该值类型也不同，有可能是字符串。
        $activity = $lower;
        if (!is_numeric($activity)) {
            $activity = 0;
        }

        //应付金额           
        $payPriceTotal = ($currentPriceTotal - $activity) + $deliveryFee;
        
        //计算节省价格;节省=商品原价-商品现价+活动价格
        $jieSheng = $originalPriceTotal - $currentPriceTotal + $activity;
        
        //最终统计结果
        $statistics = array(
                'goodsCn'=>$goodsCn,
                'originalPriceTotal' => Util::formatNum($originalPriceTotal),
                'currentPriceTotal' => Util::formatNum($currentPriceTotal),
                'payPriceTotal' => Util::formatNum($payPriceTotal),
                'deliveryFee' => Util::formatNum($deliveryFee),
                'jieSheng' => Util::formatNum($jieSheng),
                'lower' => Util::formatNum($lower)
        );
        $cartData['statistics']=$statistics;
        $data=$cartData;
        return $this->returnData($data);
    }
    
    /**
     * 重新计算购物车价格：活动、配送费、原价、总价、应付等..这些价格随着数量、金额、活动等因素发生变化。
     * （支持两种模式调用同步和异步）
     * @param type $parameter userId|userKey
     */
    public function recount2($parameter) {
        //验证参数,未登录情况下用户id是空的；
        if (!isset($parameter['userId']) || !isset($parameter['userKey']) || !$parameter['userKey']) {
            return $this->errorMessage('请求参数错误！');
        }
        //获取购物车数据
        $cartData = $this->getCartData($parameter);
        
        //开始计算购物车内商品价格...................................begin;
        //商品原总价
        $originalPriceTotal = 0;
        //商品现总价
        $currentPriceTotal = 0;
        if(isset($cartData['goodsItems']) && $cartData['goodsItems']){
            foreach($cartData['goodsItems'] as $k => $p){
                if(isset($cartData['cartItems'][$p['priceId']]['buyNum']) && $cartData['cartItems'][$p['priceId']]['buyNum']){
                    $buyNum=$cartData['cartItems'][$p['priceId']]['buyNum'];
                }else{
                    $buyNum=0;
                }
                
                $originalPriceTotal += $p['originalPrice'] * $buyNum;
                $currentPriceTotal += $p['currentPrice'] * $buyNum;
            }
        }
        
        //开始计算配送费.............................................begin
        //暂时没有配送费以后追加计算
        $deliveryFee = 0;
        
        //开始计算活动.............................................begin
        //活动应减金额（暂时没有活动以后追加计算）
        $lower = 0;
        //如果活动是价格则应付金额-活动优惠价，否则该值为0；活动类型不同该值类型也不同，有可能是字符串。
        $activity = $lower;
        if (!is_numeric($activity)) {
            $activity = 0;
        }

        //应付金额           
        $payPriceTotal = ($currentPriceTotal - $activity) + $deliveryFee;
        
        //计算节省价格;节省=商品原价-商品现价+活动价格
        $jieSheng = $originalPriceTotal - $currentPriceTotal + $activity;
        
        $statistics = array(
                'originalPriceTotal' => Util::formatNum($originalPriceTotal),
                'currentPriceTotal' => Util::formatNum($currentPriceTotal),
                'payPriceTotal' => Util::formatNum($payPriceTotal),
                'deliveryFee' => Util::formatNum($deliveryFee),
                'jieSheng' => Util::formatNum($jieSheng),
                'lower' => Util::formatNum($lower)
        );
        $cartData['statistics']=$statistics;
        $data=$cartData;
        return $this->returnData($data);
    }
    
    
}
