<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\FormInscriptionUtilisateurType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UtilisateurController extends AbstractController
{
    #[Route('/utilisateur', name: 'app_utilisateur')]
    public function index(): Response
    {
        return $this->render('utilisateur/index.html.twig', [
            'controller_name' => 'UtilisateurController',
        ]);
    }

    #[Route('/utilisateurFormInscription', name:'FormInscriptionUser')]
    public function new(Request $request): Response
    {
        $formInscription = new Utilisateur();
        $form = $this->createForm(FormInscriptionUtilisateurType::class, $formInscription);
         // Convertir le formulaire en vue de formulaire
         $formView = $form->createView();
        return $this->render('utilisateur/new.html.twig', [
            'formAddUtilisateur' => $formView,
        ]);
    }



}
