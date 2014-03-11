<?php
ini_set('display_errors', On);
require_once('../server/phprpc_server.php');

function helloWorld(){
	return 'Hello World!';
}

class demo{

	static public function add($number){
		return $number+2;
	}

	
	public function multiply($number){		
		return $number*2;
	}

}
$phprpcServer = new PHPRPC_Server();
$phprpcServer->add('helloWorld');
$phprpcServer->add('multiply',new demo());
$phprpcServer->add('add', demo);
$phprpcServer->start();