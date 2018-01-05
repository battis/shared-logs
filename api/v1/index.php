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
        ->withHeader('Access-Control-Allow-Origin', '*') // FIXME OMG!
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});


/*
 * define routes
 */
$app->group('/devices', function () {
    $this->post('', function (Request $request, Response $response) {
        return $response->withJson($this->devices->create($request->getParsedBody()));
    });
    $this->get('', function (Request $request, Response $response) {
        return $response->withJson($this->devices->all());
    });
    $this->get(id_PATTERN, function (Request $request, Response $response, $id) {
        return $response->withJson($this->devices->get($id));
    });
    $this->put(id_PATTERN, function (Request $request, Response $response, $id) {
        return $response->withJson($this->devices->update($id));
    });
    $this->delete(id_PATTERN, function (Request $request, Response $response, $id) {
        return $response->withJson($this->devices->delete($id));
    });
    $this->get(id_PATTERN . '/logs', function (Request $request, Response $response, $id) {
        return $response->withJson($this->logs->listByDevice($id));
    });
});
$app->group('/logs', function () {
    $this->post('', function (Request $request, Response $response) {
        return $response->withJson($this->logs->create($request->getParsedBody()));
    });
    $this->get('', function (Request $request, Response $response) {
        return $response->withJson($this->logs->all());
    });
    $this->get(id_PATTERN, function (Request $request, Response $response, $id) {
        return $response->withJson($this->logs->get($id));
    });
    $this->put(id_PATTERN, function (Request $request, Response $response, $id) {
        return $response->withJson($this->logs->update($id));
    });
    $this->delete(id_PATTERN, function (Request $request, Response $response, $id) {
        return $response->withJson($this->logs->delete($id));
    });
    $this->get(id_PATTERN . '/entries', function (Request $request, Response $response, $id) {
        return $response->withJson($this->entries->listByLog($id));
    });
});
$app->group('/entries', function () {
    $this->post('', function (Request $request, Response $response){
        return $response->withJson($this->entries->create($request->getParsedBody()));
    });
    $this->get(id_PATTERN, function (Request $request, Response $response, $id) {
        return $response->withJson($this->entries->get($id));
    });
    $this->put(id_PATTERN, function (Request $request, Response $response, $id) {
        return $response->withJson($this->entries->update($id));
    });
    $this->delete(id_PATTERN, function (Request $request, Response $response, $id) {
        return $response->withJson($this->entries->delete($id));
    });
});
$app->group('/users', function () {
    $this->post('', function (Request $request, Response $response) {
        return $response->withJson($this->users->create($request->getParsedBody()));
    });
    $this->get('', function (Request $request, Response $response) {
        return $response->withJson($this->users->all());
    });
    $this->get(id_PATTERN, function (Request $request, Response $response, $id) {
        return $response->withJson($this->users->get($id));
    });
    $this->get('/{screen_name:\w{' . User::SCREEN_NAME_MINIMUM_LENGTH . ',}}', function (Request $request, Response $response, $screen_name) {
       return $response->withJson($this->users->lookupByScreenName($screen_name));
    });
    $this->put(id_PATTERN, function (Request $request, Response $response, $id) {
        return $response->withJson($this->users->update($id));
    });
    $this->delete(id_PATTERN, function (Request $request, Response $response, $id) {
        return $response->withJson($this->users->delete($id));
    });
});

$app->run();