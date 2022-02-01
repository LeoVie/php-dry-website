<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home_index")
     */
    public function indexAction(): Response
    {
        return $this->render('home/index.twig');
        /*$loader = new FilesystemLoader(__DIR__ . '/../template/');
        $twig = new Environment($loader, [
            // 'cache' => __DIR__ . '/../cache/compilation/',
            'cache' => false,
        ]);

        $template = $twig->load('index.twig');
        echo $template->render();*/
    }
}