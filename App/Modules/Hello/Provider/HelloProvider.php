<?php
namespace Hello\Provider;
/**
 * 逻辑Service测试库.
 * User: gukai@bilibili.com
 * Date: 17/2/13
 * Time: 下午7:27
 */
class HelloProvider
{

    function music()
    {
        return 'sound of my dreams';
    }


    // @override
    public function middleware($middleware = null)
    {
        return "Hello ";
    }

}