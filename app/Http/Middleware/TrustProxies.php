<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Middleware\TrustProxies as Middleware;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array|string
     */
    protected $proxies = '*';

    public function __construct()
    {
        // On some local environments, REMOTE_ADDR might be null which causes IpUtils::checkIp4 to crash if proxies is '*'
        if (!isset($_SERVER['REMOTE_ADDR']) || $_SERVER['REMOTE_ADDR'] === null || $_SERVER['REMOTE_ADDR'] === '') {
            $this->proxies = null;
        }
    }


    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
    Request::HEADER_X_FORWARDED_FOR |
    Request::HEADER_X_FORWARDED_HOST |
    Request::HEADER_X_FORWARDED_PORT |
    Request::HEADER_X_FORWARDED_PROTO |
    Request::HEADER_X_FORWARDED_AWS_ELB;
}
