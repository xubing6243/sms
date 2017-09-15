<?php
/**
 *  Base.php
 *  短信发送接口构造方法
 *  
 *  Created by SteveAK on 11/18/16
 *  Copyright (c) 2016 SteveAK. All rights reserved.
 *  Contact email(aer_c@qq.com) or qq(7579476)
 */ 
namespace SteveAK\Sms;

abstract class Base {

	protected $config;

	public $error = '';

	/**
     * 脚本执行时间。－1表示采用PHP的默认值。
     * @access  private
     * @var     integer     $time_limit
     */
    protected $time_limit = -1;

    /**
     * 在多少秒之内，如果连接不可用，脚本就停止连接。－1表示采用PHP的默认值。
     * @access  private
     * @var     integer     $connect_timeout
     */
    protected $connect_timeout = -1;

    /**
     * 连接后，限定多少秒超时。－1表示采用PHP的默认值。此项仅当采用CURL库时启用。
     * @access  private
     * @var     integer    $stream_timeout
     */
    protected $stream_timeout = -1;
    
    public function __construct($config) {
        $this->config = array_merge($this->config, $config);
    }
	
	/**
	 * 发送短信
	 * @param array $mobile		手机号
	 * @param string $content	短信内容
	 * return array
	*/
	abstract public function send($mobile, $content);
	
    /**
     * 发送语音验证码
     * @param  mobile $mobile 手机号
     * @param  intval $code   验证码
     */
    abstract public function voice($mobile, $code);

	/**
	 * 获取该短信接口的相关数据
	 * @return string
	 */
	abstract public function info();

	/**
     * 使用curl进行连接
     *
     * @access  private
     * @param   string      $url            远程服务器的URL
     * @param   string      $params         查询参数，形如bar=foo&foo=bar
     * @param   string      $method         请求方式，是POST还是GET
     * @param   array       $my_header      用户要发送的头部信息，为一维关联数组，形如array('a'=>'aa',...)
     * @return  array                       成功返回一维关联数组，形如array('header'=>'bar', 'body'=>'foo')，
     *                                      失败返回false。
     */
    public function use_curl($url, $params, $method, $my_header=array()){
        //开始一个新会话
        $ch = curl_init();

        //基本设置
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true); // 处理完后，关闭连接，释放资源
        curl_setopt($ch, CURLOPT_HEADER, true); //结果中包含头部信息
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //把结果返回，而非直接输出
        //curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0); //采用1.0版的HTTP协议

        $url_parts = $this->parse_raw_url($url);

        //设置验证策略
        if (!empty($url_parts['user'])){
            $auth = $url_parts['user'] . ':' . $url_parts['pass'];
            curl_setopt($ch, CURLOPT_USERPWD, $auth);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        }

        $header = array();

        //设置主机
        $header[] = 'Host: ' . $url_parts['host'];

        //格式化自定义头部信息
        if ($my_header && is_array($my_header)){
            foreach ($my_header AS $key => $value){
                $header[] = $key . ': ' . $value;
            }
        }

        if ($method === 'GET'){
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            $url .= $params ? '?' . $params : '';
        }else{
            curl_setopt($ch, CURLOPT_POST, true);
            $header[] = 'Content-Type: application/x-www-form-urlencoded';
            $header[] = 'Content-Length: ' . strlen($params);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //跳过SSL证书检查
        }

        //设置请求地址
        curl_setopt($ch, CURLOPT_URL, $url);

        //设置头部信息
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        if ($this->connect_timeout > -1){
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connect_timeout);
        }

        if ($this->stream_timeout > -1){
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->stream_timeout);
        }

        //发送请求
        $http_response = curl_exec($ch);

        if (curl_errno($ch) != 0){
            return false;
        }

        $separator = '/\r\n\r\n|\n\n|\r\r/';
        list($http_header, $http_body) = preg_split($separator, $http_response, 2);

        $http_response = array('header' => $http_header,//肯定有值
                               'body'   => $http_body); //可能为空

        curl_close($ch);

        return $http_response;
    }

    /**
     * 将xml转为array
     * @param string $xml
     * @throws WxPayException
     */
    public function xmlToArray($xml){   
        //将XML转为array
        $array = json_decode(json_encode(simplexml_load_string($xml)), true);     
        return $array;
    }

    /**
     * Similar to PHP's builtin parse_url() function, but makes sure what the schema,
     * path and port keys are set to http, /, 80 respectively if they're missing
     *
     * @access     private
     * @param      string    $raw_url    Raw URL to be split into an array
     * @author     http://www.cpaint.net/
     * @return     array
     */
    private function parse_raw_url($raw_url){
        $retval   = array();
        $raw_url  = (string) $raw_url;

        // make sure parse_url() recognizes the URL correctly.
        if (strpos($raw_url, '://') === false){
          	$raw_url = 'http://' . $raw_url;
        }

        // split request into array
        $retval = parse_url($raw_url);

        // make sure a path key exists
        if (!isset($retval['path'])){
          	$retval['path'] = '/';
        }

        // set port to 80 if none exists
        if (!isset($retval['port'])){
          	$retval['port'] = '80';
        }

        return $retval;
    }
}