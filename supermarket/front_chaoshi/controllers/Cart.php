<?php

/**
 * @name CartController
 * @desc 超市购物车
 */
class CartController extends Core_Controller_Www {
    private $imagesConfig;
    
    private $fileImg;
    
    private $phprpcClient;
    
    private $uid;
    
    private $userKey;


    public function init() {
        parent::init();
        Yaf_Loader::import('phprpc/client/phprpc_client.php');
        Yaf_Loader::import('phprpc/common/bigint.php');
        Yaf_Loader::import('phprpc/common/compat.php');
        Yaf_Loader::import('phprpc/common/phprpc_date.php');
        Yaf_Loader::import('phprpc/common/xxtea.php');
        
        //加载配置文件
        $this->imagesConfig = Yaf_Registry::get("_ImagesConfig");
        
        $this->fileImg = new File_Image();
        //接口地址
        $this->phprpcClient = new PHPRPC_Client('http://api.jiada.local/Chaoshi/Cart/index');
        
        //用户信息
        $this->uid=$_COOKIE['uid'];
        $this->userKey=$_COOKIE['user-key'];
        
    }
    
    /**
     * 加入购物车（只执行加入或累计加入的操作）
     * @param string $newCartItem 格式：店铺id-商品价格id-数量
     * @example /cart/add/cartItem/95396877549174785-95402721590378504-1
     * @return array|json
     */
    public function addAction() {
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        
        //获取要加入购物车内的商品参数,这里不验证参数是否正确，交给接口统一验证。
        $cartItem = $this->getParam('cartItem');
        //重组数据
        $parameter=array(
            'userId'=>  $this->uid,
            'userKey'=>  $this->userKey,
            'cartItem'=>  $cartItem
        );
        
        //请求接口,返回json数据
        $addCartResult = $this->phprpcClient->add($parameter);
        exit($addCartResult);
    }
    
    /**
     * 加入更新购物车内商品，如果商品数量为0则删除该商品
     * @param string $newCartItem 格式：店铺id-商品价格id-数量
     * @example /cart/edit/cartItem/95396877549174785-95402721590378504-0
     * @return array|json
     */
    public function editAction() {
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        
        //获取要加入购物车内的商品参数,这里不验证参数是否正确，交给接口统一验证。
        $cartItem = $this->getParam('cartItem');                
        //重组数据
        $parameter=array(
            'userId'=>  $this->uid,
            'userKey'=>  $this->userKey,
            'cartItem'=>  $cartItem
        );
        
        //请求接口,返回json数据
        $editCartResult = $this->phprpcClient->edit($parameter);
        exit($editCartResult);
    }
    
    
    /**
     * 重新计算购物车（购物车里的添加、修改、活动等事件都需要重新计算价格）
     * @param type $userId 登录情况：根据用户id获取用户购物车内商品数据
     * @param type $userKey 未登录情况：根据用户key获取用户购物车内商品数据
     * @example /cart/recount
     * @return array|json
     */
    public function recountAction() {
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        $parameter=array(
            'userId'=>  $this->uid,
            'userKey'=>  $this->userKey
        );
        
        //请求接口,返回json数据
        $recountResult = $this->phprpcClient->recount($parameter);
//        $recountResult = json_decode($recountResult,true);
//        print_r($recountResult);
        exit($recountResult);
    }
    
    public function indexAction() {
        $parameter=array(
            'userId'=>  $this->uid,
            'userKey'=>  $this->userKey
        );
        
        //请求接口,返回json数据
        $recountResult = $this->phprpcClient->recount($parameter);
        $recountResult = json_decode($recountResult,true);
        $this->getView()->assign('data', $recountResult['data']);
    }

}
