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

    public function store(Request $request)
    {
        $json = @json_encode($this->applyFilter($request->toArray()));
        static::getMemcached($this->host, $this->port)->set('clockwork_'.$request->id, $json, 30);
    }


    public function retrieveAsJson($id = null)
    {
        $requests = $this->find($id);

        if (!$requests)
            return null;

        return $requests->toJson();
    }


    public function find($id)
    {
        if ($data = static::getMemcached($this->host, $this->port)->get('clockwork_'.$id)) {
            return new Request(json_decode($data, true));
        }

        return false;
    }

    public function all()
    {
        return true;
    }

    public function latest()
    {
        return true;
    }

    public function previous($id, $count = null)
    {
        return true;
    }

    public function next($id, $count = null)
    {
        return true;
    }

    public function cleanup()
    {
        return true;
    }

}
