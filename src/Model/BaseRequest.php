<?php

namespace My\LakalaCcss\Model;

class BaseRequest
{
    /**
     * 请求时间，格式yyyyMMddHHmmss
     * @var string
     */
    public $req_time;

    /**
     * 版本号1.0
     * @var string
     */
    public $version;

    /**
     * 请求参数
     * @var object
     */
    public $req_data;
}