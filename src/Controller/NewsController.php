<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Yaml;

class NewsController extends AbstractController
{
    public function __construct(private string $renderedNewsArticlesPath)
    {
    }

    /** @Route("/news", name="news_index") */
    public function indexAction(): Response
    {
        $finder = new Finder();
        $articleFiles = $finder
            ->in($this->renderedNewsArticlesPath)
            ->files()
            ->name('*.html')
            ->sortByName();

        $articles = [];
        foreach ($articleFiles as $htmlFile) {
            $htmlFilePath = $htmlFile->getPathname();
            /** @var string $yamlFilePath */
            $yamlFilePath = \Safe\preg_replace(
                '@\.html$@',
                '_metadata.yaml',
                $htmlFilePath
            );

            $metadata = Yaml::parseFile($yamlFilePath);

            $articles[] = [
                'path' => $htmlFile->getRelativePathname(),
                'metadata' => $metadata,
            ];
        }

        return $this->render(
            'news/index.twig',
            [
                'articles' => $articles
            ]
        );
    }
}