<?php

require __DIR__ . '/../vendor/autoload.php';

/*
 * Init application
 */
$db = new \App\Database();
$db_state = new \App\DatabaseState($db->getData());

/**
 * Routes
 */
if (is_method('GET')) {
    if (is_route('/', true)) {
        /*
         * Main page
         */
        (new \App\Page($db_state->get()))->build();
    } elseif (is_route('db/node')) {
        /*
         * Get node by id
         */
        (new \App\DatabaseRequest($db_state))->getNode(find_param('id'));
    } else {
        http_response_code(403);
    }
} elseif (is_method('PUT')) {
    if (is_route('db/save')) {
        /*
         * Apply changes in cache
         */
        (new \App\DatabaseRequest($db_state))->setDatabase($db)->save();
    }
    if (is_route('db/reset')) {
        /*
         * Reset database to fallback state
         */
        (new \App\DatabaseRequest($db_state))->setDatabase($db)->fallback();
    }
} else {
    http_response_code(403);
}

function is_method(string $type): bool
{
    return $type === $_SERVER['REQUEST_METHOD'];
}

function is_route(string $name, bool $strict = false): bool
{
    if ($strict) {
        return urldecode($_SERVER['REQUEST_URI']) === $name;
    }

    return is_int(strpos(urldecode($_SERVER['REQUEST_URI']), $name));
}

function find_param(string $name): ?string
{
    return explode('=',
        current((array)preg_grep("#{$name}=#", (array)explode('&',
            parse_url(urldecode($_SERVER['REQUEST_URI']), PHP_URL_QUERY)))
        )
    )[1];
}