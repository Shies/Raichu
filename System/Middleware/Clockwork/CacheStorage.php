<?php

namespace Raichu\Middleware\Clockwork;
use Clockwork\Storage\Storage as Storage;
use Clockwork\Request\Request;

class CacheStorage extends Storage
{
    protected static $_memcached;

    protected $host;
    protected $port;

    public function __construct($host, $port = 11211)
    {
        $this->host = $host;
        $this->port = $port;
    }

    protected static function getMemcached($path, $port)
    {
        if (static::$_memcached == null) {
            static::$_memcached = new \Memcached();
            static::$_memcached->addServer($path, $port);
        }

        return static::$_memcached;
    }

    public function retrieve($id = null, $last = null)
    {
        if (!$id) {
            return;
        }

        if ($data = static::getMemcached($this->host, $this->port)->get('clockwork_'.$id)) {
            return new Request(json_decode($data, true));
        }

        return false;
    }

    public function store(Request $request)
    {
        static::getMemcached($this->host, $this->port)->set('clockwork_'.$request->id, @json_encode($this->applyFilter($request->toArray())), 30);
    }
}
