# 短信通道聚合
顾名思义，可聚合多个短信通道，支持短信验证码的语音发送

## 使用composer安装
```
"steveak/sms": "1.*"
```

## 使用方式

### 在PhalApi中使用，支持2.x
```
// 在di.php中注册
$di->sms = new \SteveAK\Sms\App($di->config->get('app.sms'));

// 在app.php中配置
'sms' => array(
    'channel' => 'yimei',
    'sign' => '短信签名',
    'yimei' => array(
        'cdkey' => 'xxx-xxx-xxx-xxx',
        'password' => '123456',
        'sessionKey' => '123456'
    ),
    'luosimao' => array(
        'key' => 'xxxx',
        'voicekey' => 'xxxx'
    ),
),

// 以上2步完成以后即可发送短信操作
// 发送短信
$result = $di->sms->send('138xxxxxxxx', '短信内容');
var_dump($result);

// 发送语音验证码
$result = $di->sms->voice('138xxxxxxxx', 123456);
var_dump($result);

// 根据业务需要可以自由切换通道
$result = $di->sms->send('138xxxxxxxx', '短信内容', 'luosimao');

```

## 整合通道
已整合的通道，没有打广告的意思，以下的通道都是几年来用的比较好的
```
螺丝帽：https://luosimao.com/ 上海的一家短信公司，可群发
亿美软通：业务QQ（2460818140），量大可申请独立通道，我们自己公司也用此通道，现在共享出来

```
可根据自己的需求来整合需要的通道，也可以联系steveak(7579476)定制需要的通道