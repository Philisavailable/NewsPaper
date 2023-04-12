<?php

namespace App\Controller;

use DateTime;
use App\Entity\Article;
use App\Form\ArticleFormType;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/admin')]
class ArticleController extends AbstractController
{
    #[Route('/ajouter-un-article', name: 'create_article', methods: ['GET', 'POST'])]
    public function createArticle(Request $request, ArticleRepository $repo, SluggerInterface $slugger): Response
    {
        $article = new Article();

        $form = $this->createForm(ArticleFormType::class, $article)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $article->setCreatedAt(new DateTime());
            $article->setUpdatedAt(new DateTime());
            $article->setAlias($slugger->slug($article->getTitle()));

            # Set de la relation entre Article et User
            $article->setAuthor($this->getUser());

            /** @var UploadedFile $photo */
            $photo = $form->get('photo')->getData();

            if ($photo) {
                $extension = '.' . $photo->guessExtension();

                $safeFilename = $slugger->slug(pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME));

                $newFilename = $safeFilename . '-' . uniqid("", true) . $extension;
                
                try {
                    $photo->move($this->getParameter('uploads_dir'), $newFilename);
                    $article->setPhoto($newFilename);
                }
                catch(FileException $exception) {
                    $this->addFlash('warning', "Le fichier photo ne s'est pas importé correctement. Veuillez réessayer." . $exception->getMessage());
                    
                }
            }

            $repo->save($article, true);

            return $this->redirectToRoute('show_dashboard');

        }// endif

        return $this->render('admin/article/form.html.twig', [
            'form' => $form->createView()
        ]);
    }// en creatArticle()
}
