<?php

declare(strict_types=1);

namespace App\Controller;

use Safe\Exceptions\PcreException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Yaml;

class BlogController extends AbstractController
{
    public function __construct(private string $renderedNewsArticlesPath)
    {
    }

    /** @Route("/blog", name="blog_index") */
    public function indexAction(): Response
    {
        $articles = $this->findArticles();

        return $this->render(
            'blog/index.twig',
            [
                'articles' => $articles
            ]
        );
    }

    /** @Route("/blog.json", name="blog_index_json") */
    public function indexJsonAction(): Response
    {
        $articles = $this->findArticles();

        return new JsonResponse($articles);
    }

    /** @Route("/blog/{id}", name="blog_show") */
    public function showAction(int $id): Response
    {
        $articles = $this->findArticles();

        if (!array_key_exists($id, $articles)) {
            return new Response('Article not found', 404);
        }

        return $this->render(
            'blog/show.twig',
            [
                'article' => $articles[$id]
            ]
        );
    }

    /**
     * @return array<int, array{path: string, metadata: array{date: string, title: string}, url: string}>
     * @throws PcreException
     */
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

            /** @var array{date: string, title: string} $metadata */
            $metadata = Yaml::parseFile($yamlFilePath);

            $articles[] = [
                'path' => $htmlFile->getRelativePathname(),
                'metadata' => $metadata,
                'url' => $this->generateUrl('blog_show', ['id' => $i])
            ];
        }

        return array_reverse($articles, true);
    }
}