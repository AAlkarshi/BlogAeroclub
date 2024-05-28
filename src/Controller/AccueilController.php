<?php

namespace App\Controller;

use App\Form\FormInscriptionUtilisateurType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#Nom du Controller
class AccueilController extends AbstractController
{
     
#[Route('/accueil', name: 'app_accueil')]
    public function index(): Response
    {
        #render PERMET D'AFFICHER LA PAGE 
        return $this->render('accueil/index.html.twig', [
            'controller_name' => 'AccueilController',
        ]);
    }



}
