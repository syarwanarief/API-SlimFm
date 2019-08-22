<?php

//Create = post
//READ = get
//UPDATE = put
//DELETE = delete

use Slim\Http\Request;
use Slim\Http\Response;

if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
$dependencies = require __DIR__ . '/../src/dependencies.php';
$dependencies($app);

// Register middleware
$middleware = require __DIR__ . '/../src/middleware.php';
$middleware($app);

// Register routes
$routes = require __DIR__ . '/../src/routes.php';
//$routes($app);

$app->get('/datakaryawan', function (Request $request, Response $response) {
    $query = $this->db->prepare('SELECT * FROM master_data_karyawan');
    $result = $query->execute();
    if ($result) {
        if ($query->rowCount()) {
            $data = array(
                'kode' => 1,
                'keterangan' => 'Sukses',
                'data' => $query->fetchAll());
        }else{
            $data = array(
                'kode' => 2,
                'keterangan' => 'Tidak ada data',
                'data' => null);
        }
    }else{
        $data = array(
            'kode' => 100,
            'keterangan' => 'Terdapat error',
            'data' => null);
    }
    return $response->withJson($data);
});

$app->get('/datakaryawan/{nopek}',
    function (\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, array $args) {
        $query = $this->db->prepare('SELECT nopek, nama, jabatan FROM master_data_karyawan WHERE nopek = :nopek');
        $query->bindParam(':nopek', $args['nopek']);
        $query->execute();

        $jabatan = "";
        $nama ="";
        $nopek ="";
        $query->bind_result($nopek, $nama, $jabatan);

        $data = array();

        while ($query->fetch()) {
            $temp = array();
            $temp['nopek'] = $nopek;
            $temp['nama'] = $nama;
            $temp['jabatan'] = $nama;
            array_push($data, $temp);
        }
        return $response->withJson($data);
    });

// Run app
$app->run();
