# 拉卡拉收银台SDK

## 文档地址

- <http://open.lakala.com/#/home/document/detail?title=%E5%85%AC%E5%85%B1%E5%8F%82%E6%95%B0&id=282> 文档地址

## 使用说明

### 收银台订单创建

```php
$lakala = new \My\LakalaCcss\Lakala([
    'appid' => 'OP00000xxx',
    'merchantNo' => 'xxx',
    'mchSerialNo' => 'xxx',
    'merchantPrivateKeyPath' => '/lakala/key/xxx.pem',
    'lklCertificatePath' => '/lakala/key/xxx.cer',
    'notifyUrl' => 'http://127.0.0.1/notify',
    'callbackUrl' => 'http://127.0.0.1/callback',
]);

// 金额 分
$result = $lakala->orderCreate('订单标题', '订单号', '200');
```

### 收银台支付验签

```php
$returnData = [
    "code" => "SUCCESS",
    "message" => "执行成功"
];

$authorization = $_SERVER['HTTP_AUTHORIZATION'];
$response = file_get_contents("php://input");

$lakala = new \My\LakalaCcss\Lakala([
    'appid' => 'OP00000xxx',
    'merchantNo' => 'xxx',
    'mchSerialNo' => 'xxx',
    'merchantPrivateKeyPath' => '/lakala/key/xxx.pem',
    'lklCertificatePath' => '/lakala/key/xxx.cer',
]);

if(!$lakala->signatureVerification($authorization, $response)) {
    //签名不通过
    $returnData = [
        "code" => "ERROR",
        "message" => "签名不通过"
    ];
}

return $returnData;
```
