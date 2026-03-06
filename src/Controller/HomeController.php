<?php

namespace App\Controller;

use App\Repository\OffreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(OffreRepository $offreRepository): Response
    {
        // On récupère toutes les offres depuis la base de données
        $offres = $offreRepository->findAll();

        return $this->render('home/index.html.twig', [
            'offres' => $offres,
        ]);
    }
}
