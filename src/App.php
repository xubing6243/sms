<?php
/**
 *  App.php
 *  入口
 *  
 *  Created by SteveAK on 08/29/17
 *  Copyright (c) 2017 SteveAK. All rights reserved.
 *  Contact email(aer_c@qq.com) or qq(7579476)
 */ 
namespace SteveAK\Sms;

class App {
	
    //配置信息
    protected $config;

    //短信引擎实例
    protected $smser;

    //错误信息
    public $error = '';

    public function __construct($config) {
        $this->config = $config;
    }

    // yimei需要登录
    public function login() {
        $this->setClass();
        $this->smser->login();
    }

    /**
     * 发送短信
     * @param  string $mobile  手机号，多个手机号用英文“,”隔开
     * @param  string $content 短信内容，一般一条短信为67个字(包含空格表单符合)，大于67个字将分2条发送
     * @param  string $channel 发送短信的通道。可不传，在配置中设置默认即可。用于营销短信或验证类短信为不同接口而配置
     * @return boolean         发送状态
     */
    public function send($mobile, $content, $channel = '') {
        //设置实例
        $this->setClass($channel);

        //开始发送短信
        $res = $this->smser->send($mobile, $content);

        if(!$res){
            $this->error = $this->smser->error;
            return false;
        }

        return true;
    }

    /**
     * 发送语音短信
     * @param  string $mobile 手机号，多个用英文“,”隔开
     * @param  int $code   验证码
     */
    public function voice($mobile, $code, $channel = '') {
         //设置实例
        $this->setClass($channel);

        //开始发送短信
        $res = $this->smser->voice($mobile, $code);

        if(!$res){
            $this->error = $this->smser->error;
            return false;
        }

        return true;
    }

    /**
     * 获取该短信接口的相关数据
     * @param  string $channel 通道
     */
    public function info($channel = ''){
        //设置实例
        $this->setClass($channel);

        $res = $this->smser->info();
        return $res;
    }

    /**
     * 设置实例
     * @param string $channel 通道
     */
    protected function setClass($channel = ''){
        // 获取通道对应的配置信息
        $channel = $channel ? $channel : $this->config['channel'];
        if(!$channel){
            $this->error = '未设置通道';
            return false;
        }

        // 获取对应的通道
        $class = 'SteveAK\\Sms\\Channel\\' . ucfirst($channel);
        if(!class_exists($class)) {
        	$this->error = '对应的通道不存在';
            return false;
        }

        $config = $this->config[$channel];
        
        // 签名信息
        $config['sign'] = $this->config['sign'];
        $this->smser = new $class($config);
    }
}