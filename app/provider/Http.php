<?php
namespace Raichu\Provider;
/**
 * Http请求类.
 * 封装了常用的CURL操作
 *
 * User: gukai@bilibili.com
 * Date: 16/11/23
 * Time: 下午5:16.
 */
class Http
{
    /**
     * @var string $api_path 请求路径
     */
    protected $api_path = '';

    /**
     * @var string $url 请求地址
     */
    protected $url = '';

    /**
     * @var array $headers 请求头部
     */
    protected $headers = [];

    /**
     * @var bool $withHeader 设置是否将返回头包含在输出中
     */
    protected $withHeader = false;

    /**
     * @var array $query 请求参数
     */
    protected $query = [];

    /**
     * @var array $info curl_getinfo
     */
    protected $info = null;

    /**
     * @var array $info curl error
     */
    protected $error = null;

    /**
     * @var array $opts curl设置参数
     */
    protected $opts = [
        'dns_use_global_cache' => true,
        'dns_cache_timeout' => 300,
        'returntransfer' => true,
        'failonerror' => true,
        'maxredirs' => 5,
        'connecttimeout' => 4,
        'timeout' => 8,
    ];

    /**
     * 获取curl句柄，可设置自定义参数
     *
     * @param  array $opts curl参数
     * @return resource
     */
    protected function getHandler($opts = [])
    {
        $ch = curl_init();

        $opts = array_merge($this->opts, $opts);
        curl_setopt_array($ch, [
            CURLOPT_DNS_USE_GLOBAL_CACHE => $opts['dns_use_global_cache'],
            CURLOPT_DNS_CACHE_TIMEOUT => $opts['dns_cache_timeout'],
            CURLOPT_RETURNTRANSFER => $opts['returntransfer'],
            CURLOPT_FAILONERROR => $opts['failonerror'],
            CURLOPT_MAXREDIRS => $opts['maxredirs'],
            CURLOPT_CONNECTTIMEOUT => $opts['connecttimeout'],
            CURLOPT_TIMEOUT => $opts['timeout']]);

        return ($ch ?: false);
    }

    /**
     * 设置请求路径
     *
     * @param  string $api_path 请求路径
     * @return Http
     */
    public function setPath($api_path)
    {
        $this->api_path = $api_path;

        return $this;
    }

    /**
     * 设置请求Host
     *
     * @param  string $host 请求Host
     * @return Http
     */
    public function setHost($host)
    {
        $this->headers[] = "Host: {$host}";

        return $this;
    }

    /**
     * 设置请求头
     *
     * @param  string $header 设置请求头
     * @return Http
     */
    public function setHeader($header)
    {
        $this->headers[] = $header;

        return $this;
    }

    /**
     * 获取请求头
     *
     * @return array
     */
    public function getHeader()
    {
        return $this->headers;
    }

    /**
     * 设置将响应头包含到输出中
     *
     * @return Http
     */
    public function withHeader()
    {
        $this->withHeader = true;

        return $this;
    }

    /**
     * 发出GET请求
     *
     * @param  string       $api   API地址
     * @param  string|array $query 请求参数
     * @param  array        $opts  curl自定义参数
     * @return mixed
     */
    public function Get($api, $query = '', $opts = [])
    {
        $ch = $this->getHandler($opts);
        $query = is_array($query) ? http_build_query($query, '', '&', PHP_QUERY_RFC3986) : $query; // PHP_QUERY_RFC3986 : Space will be turn to %20
        $this->query = $query;
        $this->url = $this->api_path.$api.($query ? "?{$query}" : '');
        if ($this->headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        }

        return $this->run($ch);
    }

    /**
     * 发出POST请求
     *
     * @param  string       $api         API地址
     * @param  string|array $query       请求参数
     * @param  bool         $field_isarr 是否数组参数
     * @param  array        $opts        curl自定义参数
     * @return mixed
     */
    public function Post($api, $query = '', $field_isarr = false, $opts = [])
    {
        $ch = $this->getHandler($opts);
        $query = is_array($query) && !$field_isarr ? http_build_query($query) : $query;
        $this->query = $query;
        $this->url = $this->api_path.$api;
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        if ($this->headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        }

        return $this->run($ch);
    }

    /**
     * 发出PUT请求
     *
     * @param  string       $api   API地址
     * @param  string|array $file  上传文件信息
     * @param  string|array $query 请求参数
     * @param  array        $opts  curl自定义参数
     * @return mixed
     */
    public function Put($api, $file = '', $query = [], $opts = [])
    {
        $ch = $this->getHandler($opts);
        $query = is_array($query) ? http_build_query($query) : $query;
        $this->url = $this->api_path.$api.($query ? "?{$query}" : '');

        if (is_array($file)) {
            if (isset($file['filepath'])) {
                $fp = fopen($file['filepath'], 'r');
            } elseif (isset($file['fp'])) {
                $fp = $file['fp'];
            }
            $this->headers[] = "Content-Type: {$file['filetype']}";
        } else {
            $fp = fopen($file, 'r');
            if ($img_info = getimagesize($file)) { // get mime type wher file is a image
                $this->headers[] = "Content-Type: {$img_info['mime']}";
            }
        }
        curl_setopt($ch, CURLOPT_PUT, 1);
        curl_setopt($ch, CURLOPT_INFILE, $fp);
        if ($this->headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        }

        return $this->run($ch);
    }

    /**
     * 执行CURL请求，并设置CURL请求及错误信息
     *
     * @param  resource $ch CURL句柄
     * @return mixed
     */
    protected function run($ch)
    {
        curl_setopt($ch, CURLOPT_URL, $this->url);
        if ($this->withHeader) {
            curl_setopt($ch, CURLOPT_HEADER, 1);
        }
        $response = curl_exec($ch);
        if ($response === false) {
            $this->error = ['errno' => curl_errno($ch), 'error' => curl_error($ch)];
        } else {
            $this->info = curl_getinfo($ch);
        }
        curl_close($ch);

        return $response;
    }

    /**
     * 获取CURL请求信息
     *
     * @return array
     */
    public function getCurlInfo()
    {
        return $this->info;
    }

    /**
     * 获取CURL错误信息
     *
     * @return array
     */
    public function getError()
    {
        return $this->error;
    }
}
