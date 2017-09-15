<?php
/**
 *  Luosimao.php
 *  螺丝帽
 *  
 *  Created by SteveAK on 09/14/17
 *  Copyright (c) 2017 SteveAK. All rights reserved.
 *  Contact email(aer_c@qq.com) or qq(7579476)
 */ 
namespace SteveAK\Sms\Channel;
use SteveAK\Sms\Base;

class Luosimao extends Base {
	//查询余额
	protected $user_api = '';
	
	protected $config = array(
		//用户唯一标识
		'key' => '',
	);

	/**
	 * 发送短信
	 * @param array $mobile		手机号
	 * @param string $content	短信内容
	 * return array
	*/
	public function send($mobile, $content) {
		if(is_array($mobile)){
			$mobile = implode(",", $mobile);
		}

		$data = array(
			'mobile' => $mobile,
			'message' => $content . '【' . $this->config['sign'] . '】',
		);

		$api = 'https://api:key-' . $this->config['key'] . '@sms-api.luosimao.com/v1/send.json';

		$dataStr = http_build_query($data);

		//请求短信发送
		$res = $this->use_curl($api, $dataStr, 'POST');
		if(empty($res['body'])) {
			return false;
		}

		$body = json_decode($res['body'], true);

		if(intval($body['error']) == 0) {
			return true;
		} else {
			//返回错误信息
			$this->error = $body['msg'];
			return false;
		}
	}

	/**
	 * 发送语音验证码短信
	 * @param  string $mobile 手机号
	 * @param  int    $code   验证码
	 */
	public function voice($mobile, $code) {
		if(intval($code) <= 0) {
			//返回错误信息
			$this->error = '语音必须为4-6位的数字验证码';
			return false;
		}

		$data = array(
			'mobile' => $mobile,
			'code' => $code,
		);

		$api = 'https://api:key-' . $this->config['voicekey'] . '@voice-api.luosimao.com/v1/verify.json';

		$dataStr = http_build_query($data);

		//请求短信发送
		$res = $this->use_curl($api, $dataStr, 'POST');
		if(empty($res['body'])) {
			return false;
		}

		$body = json_decode($res['body'], true);

		if(intval($body['error']) == 0) {
			return true;
		} else {
			//返回错误信息
			$this->error = $body['msg'];
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
		$api = 'https://api:key-' + $this->config['key'] + '@sms-api.luosimao.com/v1/status.json';

		//请求查询账户
		$res = $this->use_curl($api, '', 'POST');
		return $res['body'];
	}
}