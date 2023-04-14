<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Article;
use App\Entity\Commentary;
use App\Form\ChangePasswordFormType;
use App\Form\RegisterFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    #[Route('/inscription', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(Request $request, UserRepository $repo, UserPasswordHasherInterface $hasher): Response
    {
        $user = new User();

        $form = $this->createForm(RegisterFormType::class, $user)
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $user->setCreatedAt(new DateTime());
            $user->setUpdatedAt(new DateTime());
            $user->setRoles(['ROLE_USER']);

            $user->setPassword(
                $hasher->hashPassword($user, $user->getPassword())
            );

            $repo->save($user, true);

            $this->addFlash('success', "Votre inscription a été effectuée avec succès !");
            return $this->redirectToRoute('show_home');
        }

        return $this->render('user/register_form.html.twig', [
            'form' => $form->createView()
        ]);

    } //end register()

    #EXERCICE : Faire le compte d'un utilisateur dans le UserController

    #[Route('/profile/voir-mon-compte', name: 'show_profil', methods: ['GET'])]
    public function showProfil(EntityManagerInterface $entity): Response
    {
        $articles = $entity->getRepository(Article::class)->findBy(['author' => $this->getUser()]);
        $commentaries = $entity->getRepository(Commentary::class)->findBy(['author' => $this->getUser()]);
        // $user = $this->getUser();

        // // dd($user);

        return $this->render('user/show_profil.html.twig', [
            'articles' => $articles,
            'commentaries' => $commentaries
        ]);
    }// end showProfil()

    #[Route('/changer-mon-mot-de-passe', name:'change_password', methods: ['GET', 'POST'])]
    public function changePassword(Request $request, UserRepository $repo, UserPasswordHasherInterface $hasher): Response
    {
        $form = $this->createForm(ChangePasswordFormType::class)
            ->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                $user = $repo->find($this->getUser());

                //listing étapes
                /*
                1- 
                    Un nouvel input => ChangePasswordFormType
                2-
                    Récupérer la valeur de cet input
                3-
                    hacher le $currentPassord pour comparaison en BDD
                4-
                    Condition de vérification
                5-
                    Si elle est vérifiée alors on éxecute le code
                */

                //-----------VERIFICATION MDP-----------\\
                $currentPassord = $form->get('currentPassword')->getData();

                if(! $hasher->isPasswordValid($user, $currentPassord)){
                    $this->addFlash('warning', "Le mot de passe actuel n'est pas valide");
                    return $this->redirectToRoute('show_profil');
                }

                $user->setUpdatedAt(new DateTime());

                $plainPassword = $form->get('plainPassword')->getData();

                $user->setPassword($hasher->hashPassword($user, $plainPassword));

                $repo->save($user, true);

                $this->addFlash('warning', "Votre mot de passe a bien été modifié !");
                return $this->redirectToRoute('show_profil');
            }

            return $this->render('user/change_password_form.html.twig', [
                'form' => $form->createView()
            ]);
    }// end changePassword()

}// end Class
