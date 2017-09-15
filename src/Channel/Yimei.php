<?php
/**
 *  Yimei.php
 *  亿美软通
 *  
 *  Created by SteveAK on 09/15/17
 *  Copyright (c) 2017 SteveAK. All rights reserved.
 *  Contact email(aer_c@qq.com) or qq(7579476)
 */ 
namespace SteveAK\Sms\Channel;
use SteveAK\Sms\Base;

define('SCRIPT_ROOT',  dirname(__FILE__) . '/Yimei/');
require_once SCRIPT_ROOT.'include/Client.php';

class Yimei extends Base {
	//发送短信
	protected $api = 'http://sdk999in.eucp.b2m.cn:8080/sdk/SDKService';
	
	protected $config = array(
		//用户唯一标识
		'cdkey' => '',
		'password' => '',
		'sessionKey' => '123456'
	);

	protected $client = null;

	public function __construct($config) {
		$this->config = array_merge($this->config, $config);
		$this->client = new \Client($this->api, $this->config['cdkey'], $this->config['password'], $this->config['sessionKey'], false, false, false, false, 2, 10);
		// 设置编码
		$this->client->setOutgoingEncoding('UTF-8');
	}

	/**
	 * 登录获取session key
	 */
	public function login() {
		$statusCode = $this->client->login();
		echo "处理状态码:".$statusCode."<br/>";
		if ($statusCode!=null && $statusCode=="0") {
			//登录成功，并且做保存 $sessionKey 的操作，用于以后相关操作的使用
			echo "登录成功, session key:".$this->client->getSessionKey()."<br/>";
		} else {
			//登录失败处理
			echo "登录失败,返回:".$statusCode;
		}
	}

	/**
	 * 发送短信
	 * @param array $mobile		手机号
	 * @param string $content	短信内容
	 * return array
	*/
	public function send($mobile, $content){
		if(!is_array($mobile)){
			$mobile = array($mobile);
		}

		// 请求短信发送
		$result = $this->client->sendSMS($mobile, $content);
		if($result == 0) {
			return true;
		} else {
			$this->client->getError();
			return false;
		}
	}

	/**
	 * 发送语音验证码
	 * @param mobile $mobile 手机号
	 * @param int $code 验证码
	 */
	public function voice($mobile, $code) {
		if(!is_array($mobile)){
			$mobile = array($mobile);
		}

		// 请求发送语音短信
		$result = $this->client->sendVoice($mobile, $code);
		if($result == 0) {
			return true;
		} else {
			$this->client->getError();
			return false;
		}
	}

	/**
	 * 获取该短信接口的相关数据
	 * @return string nick 昵称
	 * @return string gmt_created 注册时间
	 * @return string mobile 手机号
	 * @return string email 邮箱
	 * @return string ip_whitelist IP白名单
	 * @return string api_version api版本号
	 * @return string alarm_balance 剩余条数低于该值时提醒
	 * @return string emergency_contact 紧急联系人
	 * @return string emergency_mobile 紧急联系人电话
	 * @return string balance 短信剩余条数
	 */
	public function info(){
	}
}