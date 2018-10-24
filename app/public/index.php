<?php

require __DIR__ . '/../vendor/autoload.php';

const GET = 'GET';
const POST = 'POST';
const PUT = 'PUT';

$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];
$db = new \App\DB();

if ($method === GET && '/' === $uri) {
    (new \App\MainPage($db))->build();
} elseif ($method === GET && (false !== $params = strpos($uri, $route = 'nodes'))) {
    // [GET] /nodes/{path}

    (new \App\NodesRequest($db, substr($uri, $params + strlen($route))))->render();
} elseif ($method === PUT && (false !== $nodes = strpos($uri, $route = 'cache'))) {
    // [PUT] /cache/merge

    (new \App\NodesRequest($db))->merge();
} elseif ($method === PUT && (false !== $params = strpos($uri, $route = 'nodes'))) {
    // [PUT] /nodes/{path}

    (new \App\NodesRequest($db, substr($uri, $params + strlen($route))))->rename();
} else {
    http_response_code(403);
}
