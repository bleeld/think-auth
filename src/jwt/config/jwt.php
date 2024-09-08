<?php

return [
    'key'   =>  '#sdfkw%$',                                     //  定义key值
    'expire_time'   =>  config('cookie.expire') ?: 7200,        //  过期时间：自动获取cookie过期时间/定义为7200
    'method'    =>  config('cookie.method') ?: 'HS256',         //  定义加密方法：自动获取cookie加密方法/定义HS256
];