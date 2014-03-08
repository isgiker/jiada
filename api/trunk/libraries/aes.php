<?php
/** *****************************************************
 *
 * 功能:php Aes 加解密
 * ****************************************************** */
class Aes {

// $key must be 32 bytes
    var $key = "a1b2c3d4e5f6g7h8i9j0!@#$%^&*()_+";
    
    public function __construct($key=null){
        if(!$key) $key = $this->key;
    }

    public function cypherAES128($plaintext) {
         $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);


        $ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->key, $plaintext, MCRYPT_MODE_ECB, $iv);
        $ciphertext = base64_encode($ciphertext);

        return $ciphertext;
    }

    public function uncypherAES128($ciphertext) {
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

        $ciphertext = base64_decode($ciphertext);
        $plaintext = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->key, $ciphertext, MCRYPT_MODE_ECB, $iv);
        return $plaintext;
    }

}