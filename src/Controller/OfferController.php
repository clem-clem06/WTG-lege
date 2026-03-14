<?php

namespace App\Controller;

use App\Entity\Offre;
use App\Repository\OffreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OfferController extends AbstractController
{
    #[Route('/offer/{id}', name: 'app_offer_show')]
    public function show(Offre $offre): Response
    {
        return $this->render('offer/show.html.twig', [
            'offre' => $offre,
        ]);
    }
}
