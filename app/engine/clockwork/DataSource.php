<?php

namespace bilibili\raichu\middleware\clockwork;

use bilibili\raichu\engine\App;
use Clockwork\DataSource\DataSource as DS;
use Clockwork\Request\Request;
use Clockwork\Request\Timeline;
use bilibili\raichu\engine\Router;
use bilibili\raichu\engine\Request as pRequest;

/**
 * Data source for Pikachu, provides database queries and routes.
 */
class DataSource extends DS
{
    /**
     * Clockwork Timeline to allow hooks to start/stop events.
     */
    protected $timeline;
    protected $controller;
    protected $method;
    protected $databaseQueries = [];

    /**
     * Construct the Timeline. The HookClockwork class will add events.
     */
    public function __construct()
    {
        $this->timeline = new Timeline();
        $this->app = App::getInstance();
    }

    /**
     * Adds Database queries, URI, Method and Controller. Also finalizes
     * the Timeline.
     */
    public function resolve(Request $request)
    {
        $request->timelineData = $this->timeline->finalize($request->time);
        $request->databaseQueries = $this->databaseQueries;
        $routeInfo = $this->app->getRouter()->getInfo();
        $request->controller = isset($routeInfo['controller']) ? $routeInfo['controller'] : null;
        $request->method = isset($routeInfo['method']) ? $routeInfo['method'] : null;
        $request->getData = $this->app->getRequest()->get();
        $request->postData = $this->app->getRequest()->getPost();

        return $request;
    }

    public function addDatabaseQuery($query, $time)
    {
        $this->databaseQueries[] = ['query' => $query, 'duration' => $time];
    }

    /**
     * Start an Event in the Timeline.
     */
    public function startEvent($event, $description)
    {
        $this->timeline->startEvent($event, $description);
    }

    /**
     * End an Event in the Timeline.
     */
    public function endEvent($event)
    {
        $this->timeline->endEvent($event);
    }
}
