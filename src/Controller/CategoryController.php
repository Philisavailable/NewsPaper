<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryFormType;
use App\Repository\CategoryRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin')]
class CategoryController extends AbstractController
{
    #[Route('/ajouter-une-categorie', name: 'create_category', methods: ['GET', 'POST'])]
    public function createCategory(Request $request, CategoryRepository $repo, SluggerInterface $slugger): Response
    {

        $category = new Category();

        $form = $this->createForm(CategoryFormType::class, $category)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            $category->setCreatedAt(new DateTime());
            $category->setUpdatedAt(new DateTime());

            # L'alias nous servira pour construire l'url d'un article
            $category->setAlias($slugger->slug($category->getName()));

            $repo->save($category, true);

            $this->addFlash('success', "La catégorie est ajoutée avec succès !");
            return $this->redirectToRoute('show_dashboard');
        }// endif

        
        return $this->render('admin/category/form.html.twig', [
            'form' => $form->createView()
        ]);
    }// end createCategory()

    #[Route('/modifier-une-categorie/{id}', name: 'update_category', methods: ['GET', 'POST'])]
    public function updateCategory(Category $category, Request $request, CategoryRepository $repo): Response
    {

        $form =$this->createForm(CategoryFormType::class, $category)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            $category->setUpdatedAt(new DateTime());

            $repo->save($category, true);


            $this->addFlash('success', "La catégorie est modifiée avec succès !");
            return $this->redirectToRoute('show_dashboard');
        }// endif

        
        return $this->render('admin/category/form.html.twig', [
            'form' => $form->createView(),
            'category' => $category
        ]);
    }// end updateCategory()

    #[Route('/archiver-une-categorie/{id}', name: 'soft_delete_category', methods: ['GET'])]
    public function softDeleteCategory(Category $category, CategoryRepository $repo): Response
    {

        $category->setDeletedAt(new DateTime());
        $repo->save($category, true);

        $this->addFlash('success', "La catégorie " . $category->getName() . " a bien été archivée !");
        return $this->redirectToRoute('show_dashboard');
    }//end deletedCategory()

    #[Route('/restaurer-une-catégorie/{id}', name: 'restore_category', methods: ['GET'])]
    public function restoreCategory(Category $category, CategoryRepository $repo):Response 
    {

        $category->setDeletedAt(null);
        $repo->save($category, true);

        $this->addFlash('success', "La catégorie " . $category->getName() . " a bien été restaurée !");
        return $this->redirectToRoute('show_dashboard');
    }// restore()

    #[Route('/supprimer-une-catégorie/{id}', name: 'hard_delete_category', methods: ['GET', 'POST'])]
    public function hardDeleteCategory(Category $category, CategoryRepository $repo):Response 
    {

        $repo->remove($category, true);

        $this->addFlash('success', "La catégorie a bien été supprimée definitivement !");
        return $this->redirectToRoute('show_dashboard');
    }// restore()


}
