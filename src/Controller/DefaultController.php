<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Repository\ArticleRepository;
use App\Repository\CommentaryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'show_home', methods: ['GET'])]
    public function showHome(ArticleRepository $repo): Response
    {
        $articles = $repo->findBy(['deletedAt' => null]);

        return $this->render('default/show_home.html.twig', [
            'articles' => $articles
        ]);
            
    }// end showHome()

    #[Route('/voir-articles/{alias}', name: 'show_articles_from_cat', methods: ['GET'])]
    public function showArticlesFromCategory(Category $category, ArticleRepository $artRepo): Response
    {
        $articles = $artRepo->findBy([
            'deletedAt' => null,
            'category' => $category->getId()
        ]);

        return $this->render('default/show_articles_from_cat.html.twig', [
            'articles' => $articles,
            'category' => $category
        ]);
    }// end shoFromCat()

    #[Route('/{cat_alias}/{art_alias}/_{id}.html', name: 'show_article', methods: ['GET'])]
    public function showAtricle(Article $article, Category $category, CommentaryRepository $commentRep):Response
    {

        $commentaries = $commentRep->findBy(['deletedAt' => null, 'article' => $article->getId()]);
        
        return $this->render('default/show_article.html.twig', [
            'article' => $article,
            'category' => $category,
            'commentaries' => $commentaries
        ]);

    }
}// end class
