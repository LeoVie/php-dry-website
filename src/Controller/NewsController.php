<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Yaml;
use function Symfony\Component\DependencyInjection\Loader\Configurator\iterator;

class NewsController extends AbstractController
{
    public function __construct(private string $renderedNewsArticlesPath)
    {
    }

    /** @Route("/news", name="news_index") */
    public function indexAction(): Response
    {
        $articles = $this->findArticles();

        return $this->render(
            'news/index.twig',
            [
                'articles' => $articles
            ]
        );
    }

    /** @Route("/news.json", name="news_index_json") */
    public function indexJsonAction(): Response
    {
        $articles = $this->findArticles();

        return new JsonResponse($articles);
    }

    /** @Route("/news/{id}", name="news_show") */
    public function showAction(int $id): Response
    {
        $articles = $this->findArticles();

        if (!array_key_exists($id, $articles)) {
            return new Response('Article not found', 404);
        }

        return $this->render(
            'news/show.twig',
            [
                'article' => $articles[$id]
            ]
        );
    }

    private function findArticles(): array
    {
        $finder = new Finder();
        $articleFiles = $finder
            ->in($this->renderedNewsArticlesPath)
            ->files()
            ->name('*.html')
            ->sortByName();

        $articles = [];
        foreach (array_values(iterator_to_array($articleFiles)) as $i => $htmlFile) {
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
                'url' => $this->generateUrl('news_show', ['id' => $i])
            ];
        }

        return array_reverse($articles, true);
    }
}