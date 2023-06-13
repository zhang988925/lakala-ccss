<?php

namespace My\LakalaCcss\Model;

class OrderCreateReq
{
    public $out_order_no;
    public $merchant_no;
    public $total_amount;
    public $order_efficient_time;
    public $notify_url;
    public $callback_url;
    public $order_info;
}