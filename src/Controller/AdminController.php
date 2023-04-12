<?php

namespace App\Controller;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/tableau-de-bord', name: 'show_dashboard', methods: ['GET'])]
    public function sohwDashboard(EntityManagerInterface $entityManager): Response
    {
        #Ce block de code try/catch permet de bloquer l'acces et de rediriger si le rôle n'est pas bon
        #Désactiver access_control dans config/packages/security.yaml ! (sinon cela ne fonctionnera pas)
        try {
            $this->denyAccessUnlessGranted("ROLE_ADMIN");
        } catch(AccessDeniedException $exception){
            $this->addFlash('danger', "cette partie du site est réservée.");
            return $this->redirectToRoute('app_login');
        }

        $categories = $entityManager->getRepository(Category::class)->findBy(['deletedAt' => null]);

        return $this->render('admin/show_dashboard.html.twig', [
            'categories' => $categories
        ]);
    }// end showDashboard()
}// end Class()
