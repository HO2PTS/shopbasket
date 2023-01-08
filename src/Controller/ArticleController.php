<?php

namespace App\Controller;

use DateTime;
use App\Entity\Article;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ArticleController extends AbstractController
{
    #[Route('/article', name: 'app_article')]
    public function index(ArticleRepository $repo): Response
    {
        // Nous récupérons tous les Articles 
        $articles = $repo->findAll();
        
        return $this->render('article/index.html.twig', [
            'articles' => $articles,
        ]);
    }
    #[Route('/article/show/{id}', name: 'show_article')]
    public function show(EntityManagerInterface $manager, $id)
    {
        $article = $manager->getRepository(Article::class)->findById($id);

        //Nous donnons les informations des variables à Twig
        return $this->render('article/show_article.html.twig', [
            'article' => $article
        ]);
    }
}
