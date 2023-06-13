<?php
namespace My\LakalaCcss;

use My\LakalaCcss\Model\BaseRequest;
use My\LakalaCcss\Model\OrderCreateReq;
use GuzzleHttp\Client;

/**
 * 仅用于拉卡拉收银台收款服务
 */
class Lakala {
    private $apiUrl = 'https://s2.lakala.com';
    private $appid;
    private $merchantNo;
    private $mchSerialNo;
    private $merchantPrivateKeyPath;
    private $lklCertificatePath;
    private $notifyUrl;
    private $callbackUrl;
    private $schema = 'LKLAPI-SHA256withRSA';
    private $version;

    public function __construct($params) {
        $this->appid                    = $params['appid'];
        $this->merchantNo               = $params['merchantNo'];
        $this->mchSerialNo              = $params['mchSerialNo'];
        $this->merchantPrivateKeyPath   = $params['merchantPrivateKeyPath'];
        $this->lklCertificatePath       = $params['lklCertificatePath'];
        $this->notifyUrl                = $params['notifyUrl'];
        $this->callbackUrl              = $params['callbackUrl'];
        $this->version                  = $params['version'] ?? '3.0';
    }

    /**
     * 收银台订单创建
     * @param string $orderInfo 订单标题
     * @param string $outOrderNo 订单号
     * @param string $totalAmount 总金额
     * @return mixed
     * @throws \Exception
     */
	public function orderCreate($orderInfo, $outOrderNo, $totalAmount) {
        $reqData = new OrderCreateReq();
        $reqData->order_info = $orderInfo;
        $reqData->out_order_no = $outOrderNo;
        $reqData->total_amount = $totalAmount;
        $reqData->merchant_no = $this->merchantNo;
        $reqData->order_efficient_time = date('YmdHis', time() + 600);
        $reqData->notify_url = $this->notifyUrl;
        $reqData->callback_url = $this->callbackUrl;

        $baseRequestVO = new BaseRequest();
        $baseRequestVO->req_time = date('YmdHis');
        $baseRequestVO->version = $this->version;
        $baseRequestVO->req_data = $reqData;

        $body = json_encode($baseRequestVO, JSON_UNESCAPED_UNICODE);
        $authorization = $this->getAuthorization($body);

        return $this->post($this->apiUrl . '/api/v3/ccss/counter/order/create', $body, $authorization);
    }

    /**
     * 签名
     */
	public function getAuthorization($body) {
		$nonceStr = $this->random(12);
     	$timestamp = time();

      	$message = $this->appid . "\n" . $this->mchSerialNo . "\n" . $timestamp . "\n" . $nonceStr . "\n" . $body . "\n";

		$key = openssl_get_privatekey(file_get_contents($this->merchantPrivateKeyPath));

        openssl_sign($message, $signature, $key, OPENSSL_ALGO_SHA256);
        openssl_free_key($key);

        return $this->schema . " appid=\"" . $this->appid . "\"," . "serial_no=\"" . $this->mchSerialNo . "\"," . "timestamp=\"" . $timestamp . "\"," . "nonce_str=\"" . $nonceStr . "\"," . "signature=\"" . base64_encode($signature) . "\"";
	}

    /**
     * 验签
     */
    public function signatureVerification($authorization, $body) {
        $authorization = str_replace($this->schema . " ", "", $authorization);
        $authorization = str_replace(",","&", $authorization);
        $authorization = str_replace("\"","", $authorization);
        $authorization = $this->convertUrlQuery($authorization);

        $authorization['signature'] = base64_decode($authorization['signature']);

        $message = $authorization['timestamp'] . "\n" . $authorization['nonce_str'] . "\n" . $body . "\n";

        $key = openssl_get_publickey(file_get_contents($this->lklCertificatePath));
        $flag = openssl_verify($message, $authorization['signature'], $key, OPENSSL_ALGO_SHA256);
        openssl_free_key($key);
        if($flag) {
            return true;
        }
        return false;
    }

    /**
     * 请求
     * @throws \Exception
     */
    public function post($url, $data, $authorization) {
        $client = new Client();
        $response = $client->post($url, [
            'headers' => [
                "Accept" => 'application/json',
                "Content-Type" => 'application/json',
                "Authorization" => $authorization,
            ],
            'body' => $data,
        ]);

        if (!$response) {
            throw new \Exception('请求异常');
        }

        $result = json_decode($response->getBody()->getContents(), true);
        if (!isset($result['code']) || $result['code'] != '000000') {
            throw new \Exception('请求异常: ' . $result['msg']);
        }
        return $result['resp_data'];
    }

    //签名参数转数组
    private function convertUrlQuery($query) { 
        $queryParts = explode('&', $query); 
         
        $params = array(); 
        foreach ($queryParts as $param) { 
            $item = explode('=', $param); 
            $params[$item[0]] = $item[1]; 
        }
        if($params['signature']) {
            $params['signature'] = substr($query, strrpos($query, 'signature=') + 10);
        }
         
        return $params; 
    }

    /**
     * 生成随机数
     */
    private function random($len = 12) {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle(str_repeat($pool, ceil($len / strlen($pool)))), 0, $len);
    }
}







