<?php
declare(strict_types=1);

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once __DIR__ . '/../vendor/autoload.php';

$loader = new FilesystemLoader(__DIR__ . '/../template/');
$twig = new Environment($loader, [
    // 'cache' => __DIR__ . '/../cache/compilation/',
    'cache' => false,
]);

$template = $twig->load('index.twig');
echo $template->render();