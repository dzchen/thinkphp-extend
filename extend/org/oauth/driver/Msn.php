<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi.cn@gmail.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace org\oauth\driver;

use org\oauth\Driver;

class Msn extends Driver
{
    /**
     * 获取requestCode的api接口
     * @var string
     */
    protected $getRequestCodeURL = 'https://login.live.com/oauth20_authorize.srf';

    /**
     * 获取access_token的api接口
     * @var string
     */
    protected $getAccessTokenURL = 'https://login.live.com/oauth20_token.srf';

    /**
     * 获取request_code的额外参数 URL查询字符串格式
     * @var srting
     */
    protected $authorize = 'scope=wl.basic wl.offline_access wl.signin wl.emails wl.photos';

    /**
     * API根路径
     * @var string
     */
    protected $apiBase = 'https://apis.live.net/v5.0/';

    /**
     * 组装接口调用参数 并调用接口
     * @param  string $api    微博API
     * @param  string $param  调用API的额外参数
     * @param  string $method HTTP请求方法 默认为GET
     * @return json
     */
    public function call($api, $param = '', $method = 'GET')
    {
        /*  MSN 调用公共参数 */
        $params = [
            'access_token' => $this->token['access_token'],
        ];

        $data = $this->http($this->url($api), $this->param($params, $param), $method);
        return json_decode($data, true);
    }

    /**
     * 解析access_token方法请求后的返回值
     * @param string $result 获取access_token的方法的返回值
     */
    protected function parseToken($result)
    {
        $data = json_decode($result, true);
        if ($data['access_token'] && $data['token_type'] && $data['expires_in']) {
            $data['openid'] = $this->getOpenId();
            return $data;
        } else {
            throw new \Exception("获取 MSN ACCESS_TOKEN出错：未知错误");
        }

    }

    /**
     * 获取当前授权应用的openid
     * @return string
     */
    public function getOpenId()
    {
        if (!empty($this->token['openid'])) {
            return $this->token['openid'];
        }

        $data = $this->call('me');
        return !empty($data['id']) ? $data['id'] : null;
    }

    /**
     * 获取当前登录的用户信息
     * @return array
     */
    public function getOauthInfo()
    {
        $data = $this->call('me');

        if (!empty($data['id'])) {
            $userInfo['type']   = 'MSN';
            $userInfo['name']   = $data['name'];
            $userInfo['nick']   = $data['name'];
            $userInfo['avatar'] = '微软暂未提供头像URL，请通过 me/picture 接口下载';
            return $userInfo;
        } else {
            E("获取msn用户信息失败：{$data}");
        }
    }

}
