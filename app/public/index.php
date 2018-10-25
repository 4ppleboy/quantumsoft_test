<?php

require __DIR__ . '/../vendor/autoload.php';

const GET = 'GET';
const POST = 'POST';
const PUT = 'PUT';
const DELETE = 'DELETE';

$uri = urldecode($_SERVER['REQUEST_URI']);
$method = $_SERVER['REQUEST_METHOD'];
$db = new \App\DB();

if ($method === GET && '/' === $uri) {
    (new \App\MainPage($db))->build();
} elseif ($method === GET && (false !== $params = strpos($uri, $route = 'nodes'))) {
    // [GET] /nodes/{path}

    (new \App\NodesRequest($db, substr($uri, $params + strlen($route))))->render();
} elseif ($method === PUT && (false !== $nodes = strpos($uri, $route = 'cache'))) {
    // [PUT] /cache/merge  For merge cache with node on backend(reduce time implementation of solution)

    (new \App\NodesRequest($db))->merge();
} elseif ($method === PUT && (false !== $params = strpos($uri, $route = 'nodes'))) {
    // [PUT] /nodes/{path}

    (new \App\NodesRequest($db, substr($uri, $params + strlen($route))))->rename();
} elseif ($method === DELETE && (false !== $params = strpos($uri, $route = 'nodes'))) {
    // [DELETE] /nodes/{path}

    (new \App\NodesRequest($db, substr($uri, $params + strlen($route))))->delete();
} elseif ($method === POST && (false !== $params = strpos($uri, $route = 'nodes'))) {
    // [POST] /nodes/{path}

    (new \App\NodesRequest($db, substr($uri, $params + strlen($route))))->create();
} elseif ($method === POST && (false !== $params = strpos($uri, $route = 'cache'))) {
    // [POST] /cache

    (new \App\NodesRequest($db, substr($uri, $params + strlen($route))))->saveTree();
}elseif ($method === PUT && (false !== $params = strpos($uri, $route = 'db/reset'))) {
    // [PUT] /db/reset

    (new \App\NodesRequest($db, substr($uri, $params + strlen($route))))->resetTree();
} else {
    http_response_code(403);
}
