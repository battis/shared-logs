<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Battis\SharedLogs\Database\Bindings\DevicesBinding;
use Battis\SharedLogs\Database\Bindings\EntriesBinding;
use Battis\SharedLogs\Database\Bindings\LogsBinding;
use Battis\SharedLogs\Database\Bindings\UsersBinding;
use Battis\SharedLogs\Objects\User;
use Slim\App;
use Slim\Handlers\Strategies\RequestResponseArgs;
use Slim\Http\Request;
use Slim\Http\Response;

define('id_PATTERN', '/{id:[0-9]+}');

$config = json_decode(file_get_contents('config.json'), true);
$app = new App(['settings' => $config]);

/* register dependencies */
$container = $app->getContainer();

/*
 * show errors
 * TODO Handle database errors more transparently
 * FIXME disable in production!
 */
$container['settings']['displayErrorDetails'] = true;

/* database with PDO */
$container['pdo'] = function ($c) {
    $settings = $c['settings']['database'];
    $pdo = new PDO($settings['dsn'], $settings['user'], $settings['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

/* placeholders as separate arguments */
$container['foundHandler'] = function() {
    return new RequestResponseArgs();
};

/* prepare bindings */
$container['devices'] = function ($c) {
    return new DevicesBinding($c->pdo);
};
$container['logs'] = function ($c) {
    return new LogsBinding($c->pdo);
};
$container['entries'] = function ($c) {
    return new EntriesBinding($c->pdo);
};
$container['users'] = function ($c) {
    return new UsersBinding($c->pdo);
};

/* "lazy CORS" */
$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function (Request $req, Response $res, callable $next) {
    $response = $next($req, $res);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*') /* FIXME OMG! */
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

function callWithNonEmptyParams(callable $method, ...$params) {
    return $method(...array_filter($params, function ($param) {
        return !empty($param);
    }));
}

/*
 * define routes
 */
$app->group('/devices', function () {
    $this->post('', function (Request $request, Response $response) {
        return $response->withJson(callWithNonEmptyParams([$this->devices, 'create'], $request->getParsedBody(), $request->getParams()));
    });
    $this->get('', function (Request $request, Response $response) {
        return $response->withJson(callWithNonEmptyParams([$this->devices, 'all'], $request->getParams()));
    });
    $this->get(id_PATTERN, function (Request $request, Response $response, $id) {
        return $response->withJson(callWithNonEmptyParams([$this->devices, 'get'], $id, $request->getParams()));
    });
    $this->put(id_PATTERN, function (Request $request, Response $response, $id) {
        return $response->withJson(callWithNonEmptyParams([$this->devices, 'update'], $id, $request->getParams()));
    });
    $this->delete(id_PATTERN, function (Request $request, Response $response, $id) {
        return $response->withJson(callWithNonEmptyParams([$this->devices, 'delete'], $id, $request->getParams()));
    });
    $this->get(id_PATTERN . '/logs', function (Request $request, Response $response, $id) {
        return $response->withJson(callWithNonEmptyParams([$this->logs, 'listByDevice'], $id, $request->getParams()));
    });
});
$app->group('/logs', function () {
    $this->post('', function (Request $request, Response $response) {
        return $response->withJson(callWithNonEmptyParams([$this->logs, 'create'], $request->getParsedBody(), $request->getParams()));
    });
    $this->get('', function (Request $request, Response $response) {
        return $response->withJson(callWithNonEmptyParams([$this->logs, 'all'], $request->getParams()));
    });
    $this->get(id_PATTERN, function (Request $request, Response $response, $id) {
        return $response->withJson(callWithNonEmptyParams([$this->logs, 'get'], $id, $request->getParams()));
    });
    $this->put(id_PATTERN, function (Request $request, Response $response, $id) {
        return $response->withJson(callWithNonEmptyParams([$this->logs, 'update'], $id, $request->getParams()));
    });
    $this->delete(id_PATTERN, function (Request $request, Response $response, $id) {
        return $response->withJson(callWithNonEmptyParams([$this->logs, 'delete'], $id, $request->getParams()));
    });
    $this->get(id_PATTERN . '/entries', function (Request $request, Response $response, $id) {
        return $response->withJson(callWithNonEmptyParams([$this->entries, 'listByLog'], $id, $request->getParams()));
    });
});
$app->group('/entries', function () {
    $this->post('', function (Request $request, Response $response){
        return $response->withJson(callWithNonEmptyParams([$this->entries, 'create'], $request->getParsedBody(), $request->getParams()));
    });
    $this->get(id_PATTERN, function (Request $request, Response $response, $id) {
        return $response->withJson(callWithNonEmptyParams([$this->entries, 'get'], $id, $request->getParams()));
    });
    $this->put(id_PATTERN, function (Request $request, Response $response, $id) {
        return $response->withJson(callWithNonEmptyParams([$this->entries, 'update'], $id, $request->getParams()));
    });
    $this->delete(id_PATTERN, function (Request $request, Response $response, $id) {
        return $response->withJson(callWithNonEmptyParams([$this->entries, 'delete'], $id, $request->getParams()));
    });
});
$app->group('/users', function () {
    $this->post('', function (Request $request, Response $response) {
        return $response->withJson(callWithNonEmptyParams([$this->users, 'create'], $request->getParsedBody(), $request->getParams()));
    });
    $this->get('', function (Request $request, Response $response) {
        return $response->withJson(callWithNonEmptyParams([$this->users, 'all'], $request->getParams()));
    });
    $this->get(id_PATTERN, function (Request $request, Response $response, $id) {
        return $response->withJson(callWithNonEmptyParams([$this->users, 'get'], $id, $request->getParams()));
    });
    $this->get('/{screen_name:\w{' . User::SCREEN_NAME_MINIMUM_LENGTH . ',}}', function (Request $request, Response $response, $screen_name) {
       return $response->withJson(callWithNonEmptyParams([$this->users, 'lookupByScreenName'], $screen_name, $request->getParams()));
    });
    $this->put(id_PATTERN, function (Request $request, Response $response, $id) {
        return $response->withJson(callWithNonEmptyParams([$this->users, 'update'], $id, $request->getParams()));
    });
    $this->delete(id_PATTERN, function (Request $request, Response $response, $id) {
        return $response->withJson(callWithNonEmptyParams([$this->users, 'delete'], $id, $request->getParams()));
    });
});

$app->run();