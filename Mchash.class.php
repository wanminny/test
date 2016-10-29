<?php
/**
 * Created by PhpStorm.
 * User: wanmin
 * Date: 16/8/3
 * Time: 下午8:38
 */

class FiexiHash {
    private $serverList = array();
    private $isSorted = FALSE;
    /***
    处理key值为一个整数，然后映射到期中一台 memcached服务器
    return int
     */
    public function mHash($key){
        $md5 = substr(md5($key), 0,8);
        $seed = 31;
        $hash = 0;
        for ($i=0; $i < 8; $i++) {
            $hash = $hash * $seed + ord($md5{$i});
            $i++;
        }
        return $hash & 0x7FFFFFFF;
    }
    public function addServer($server){
        $hash = $this->mHash($server);
        if(!isset($this->serverList[$hash])){
            $this->serverList[$hash] = $server;
        }
        $this->isSorted = FALSE;
        return TRUE;
    }
    public function removeServer($server){
        $hash = $this->mHash($server);
        if(isset($this->serverList[$hash])){
            unset($this->serverList[$hash]);
        }
        $this->isSorted = FALSE;
        return TRUE;
    }
    public function lookUp($key){
        $hash = $this->mHash($key);
        if(!$this->isSorted){
            krsort($this->serverList,SORT_NUMERIC);
            $this->isSorted = TRUE;
        }
        var_dump($this->serverList);
        foreach ($this->serverList as $pos => $server) {
            if($hash >= $pos) return $server;
        }
        return $this->serverList[count($this->serverList)-1];
    }
}

$hash = new FiexiHash();
//$port = 11211;
//普通的hash 分布
$servers = array(
    array('host'=>'127.0.0.1','port'=>"11211"),
    array('host'=>'127.0.0.2','port'=>"11212")
);
$key = 'TheKey2222wwr';
$value = 'TheValue';
//通过KEY-》HASH函数-》确定是那个服务器；
$sc = $servers[$hash->mHash($key) %2];
//var_dump(json_encode($sc));
$sc = (json_encode($sc));
//var_dump($sc);die;
$memcached = new memcached($sc);
var_dump($memcached);

$status = $memcached->set($key,$value);
var_dump($status);

//一致性的hash分布
$hash->addServer('127.0.0.1');
$hash->addServer('127.0.0.2');
var_dump($hash->lookUp($key));
//var_dump($hash->lookUp('TheKey'));
