<?php
ini_set('display_errors', On);
require_once('../client/phprpc_client.php');
$client = new PHPRPC_Client('http://mytest.local/phprpc/demo/s.php'); 

$client->setKeyLength(1000);  
$client->setEncryptMode(3);  
$client->setCharset('UTF-8');  
$client->setTimeout(10);  
echo $client->getKeyLength(), "\r\n";  
echo $client->getEncryptMode(), "\r\n";  
echo $client->getCharset(), "\r\n";
echo $client->getTimeout(), "\r\n";

echo $client->HelloWorld();


echo $client->multiply(2.5);

echo $client->add(66);

