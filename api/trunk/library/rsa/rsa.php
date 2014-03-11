<?php

/*
 * RSA非对称加密；
 */

class Rsa {
    
    public $private_keyFile = null;
    
    public $public_keyFile = null;

    public $privkey = null;
            
    public $pubkey = null;
            

    public function __construct() {
        //读取私钥
        $this->private_keyFile=JPATH_LIBRARIES_SELFLIB.DS.'rsa'.DS.'private_key.pem';
        $prk = file_get_contents($this->private_keyFile);
        $this->privkey = openssl_pkey_get_private($prk);

        //读取公钥
        $this->public_keyFile=JPATH_LIBRARIES_SELFLIB.DS.'rsa'.DS.'public_key.pem';
        $puk = file_get_contents($this->public_keyFile);
        $this->pubkey = openssl_pkey_get_public($puk);
    }

    //私钥解密
    public function priv_decrypt($encrypted) {
        $encrypted = base64_decode($encrypted);
        $r = openssl_private_decrypt($encrypted, $decrypted, $this->privkey);
        if ($r) {
            return $decrypted;
        }
        return null;
    }

    //公钥加密
    public function pub_encrypt($data) {
        if (!is_string($data)) {
            return null;
        }
        $r = openssl_public_encrypt($data, $encrypted, $this->pubkey);
        if ($r) {
            return base64_encode($encrypted);
        }
        return null;
    }

}