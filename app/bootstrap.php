<?php

require __DIR__ . '/autoload.php';

class BusinessException extends Exception
{

    protected $code;
    protected $data;

    public function __construct($code, $message = 'Unknown business error', $data = [])
    {   
        // if (preg_match("/[\x{4e00}-\x{9fa5}]+/u", $message)) {
        //     $message = json_encode($message);
        // }

        parent::__construct($message);

        $this->code = $code;
        $this->data = $data;
    }

    public function toJson()
    {
        // return @json_encode([
        //     'code' => $this->code,
        //     'message' => $this->getMessage(),
        //     'data' => $this->data,
        // ], JSON_UNESCAPED_UNICODE);
        
        return @json_encode([
            'code' => $this->code,
            'message' => $this->getMessage(),
            // 'data' => $this->data,
        ]);
    }
}

function confirm_jsonp()
{
    if (isset($_GET['cb']) && $_GET['cb']) {
        return $_GET['cb'];
    }

    if (isset($_GET['callback']) && $_GET['callback']) {
        return $_GET['callback'];
    }

    return false;
}

function load_modules($app)
{
    $found = false;
    if (isset($_GET['_url']) && $_GET['_url']) {
        $guess = explode('/', $_GET['_url']);
        while (!empty($guess)) {
            $filepath = sprintf('%s/controllers%s.php', __DIR__, implode('/', $guess));
            if (file_exists($filepath)) {
                include $filepath;
                $found = true;
                break;
            }
            array_pop($guess);
        }
    }

    if (!$found) {
        include __DIR__ . '/controllers/index.php';
    }
}

function init_app($di)
{
    $app = new Phalcon\Mvc\Micro($di);

    $app->notFound(function () use ($app) {
        // deal ajax CORS
        if ($app->request->isOptions()) {
            $app->response->setStatusCode(200);
            $app->response->setHeader('Access-Control-Allow-Origin', '*');
            $app->response->setHeader('Access-Control-Allow-Headers', 'X-TOKEN');
            $app->response->setHeader('Access-Control-Allow-Methods', 'GET,POST,PUT,DELETE,OPTIONS,HEAD');
            $app->response->sendHeaders();
            return;
        }
        raise_not_found($app);
    });

    $app->before(function () use ($app) {
        ob_start();

        $requestUrl = $_SERVER['REQUEST_URI'];
        if (preg_match('/^\/open\/.*/', $requestUrl) || $requestUrl == "/" || preg_match('/^\/test\/.*/', $requestUrl)) {
            return true;
        }

        is_valid_access($app);
    });

    $app->after(function () use ($app) {
        if ($ctx = ob_get_clean()) {
            send_response($app, $ctx);
        } else {
            // $ctx = @json_encode([
            //     'code' => 1,
            //     'message' => 'success',
            //     'data' => $app->getReturnedValue(),
            // ], JSON_UNESCAPED_UNICODE);

            $ctx = @json_encode([
                'code' => 1,
                'message' => 'success',
                'data' => $app->getReturnedValue(),
            ]);
    
            send_response($app, $ctx);
        }
    });

    $app->error(function ($exception) use ($app) {
        $app->logger->error($exception->getMessage());
        if ($exception instanceof BusinessException) {
            $ctx = $exception->toJson();
            send_response($app, $ctx);
        }
        
        $app->response->setStatusCode(500);
        if (is_debugging($app)) {
            send_response($app, json_encode(['code' => 500, 'message' => $exception->getMessage()]));
        } else {
            send_response($app, json_encode(['code' => 500, 'message' => 'service error']));
        }
    });

    $app->get('/version', function () {
        return '1.0.1';
    });

    load_modules($app);

    $app->handle();
}

function send_response($app, $ctx)
{
    if ($method = confirm_jsonp()) {
        $type = 'text/javascript; charset=utf8';
        $ctx = sprintf('%s(%s);', $method, $ctx);
    } else {
        $type = 'application/json; charset=utf8';
    }
    
    $app->response->setHeader('Content-Type', $type);
    $app->response->setHeader('Content-Length', strlen($ctx));
    $app->response->setHeader('Access-Control-Allow-Origin', '*');
    $app->response->sendHeaders();
    echo $ctx;
    exit;
}

function is_debugging($app)
{
    return $app->config->params->debug;
}

function is_valid_access($app)
{
    $method = $app->request->getMethod();
    if ($method != 'OPTIONS' && $method != 'HEAD') {
        if (is_debugging($app)) {
            return true;
        }

        if (isset($_REQUEST['token'])) {
            $access_token = $_REQUEST['token'];
        } else {
            $access_token = $app->request->getHeader('token');
        }

        if (empty($access_token)) {
            raise_bad_request($app);
        } 

        if (!$app->redis->exists($access_token)) {
            raise_unauthorized($app);
        }
    }
}


function raise_errors($app, $code, $text = '')
{
    static $HTTP_STATUS = [
        404 => 'Not Found',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
    ];

    $status = isset($HTTP_STATUS[$code]) ? $HTTP_STATUS[$code] : '';
    if (!$text) {
        $text = $status;
    }

    $app->response->setStatusCode($code);

    // $ctx = @json_encode([
    //     'code' => $code,
    //     'message' => $text,
    // ], JSON_UNESCAPED_UNICODE);

    $ctx = @json_encode([
        'code' => $code,
        'message' => $text,
    ]);

    send_response($app, $ctx);
}

function raise_not_found($app)
{
    raise_errors($app, 404);
}

function raise_bad_request($app)
{
    raise_errors($app, 400);
}

function raise_unauthorized($app)
{
    raise_errors($app, 401);
}

function raise_forbidden($app)
{
    raise_errors($app, 403);
}

set_error_handler(function ($err_no, $err_str, $err_file, $err_line) {
    $message = sprintf('%s in %s on line %d', $err_str, $err_file, $err_line);
    throw new \Exception($message, $err_no);
}, E_ALL);

$config = load_config();
init_loader();
$di = init_dependency_injection($config);
init_app($di);
