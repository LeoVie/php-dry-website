<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CurrentVersionNumberService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private CurrentVersionNumberService $currentVersionNumberService
    )
    {
    }

    /** @Route("/", name="home_index") */
    public function indexAction(): Response
    {
        return $this->render(
            'home/index.twig',
            [
                'current_version_number' => $this->currentVersionNumberService->getFromPackagist(),
            ]
        );
    }
}