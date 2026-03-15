<?php

namespace App\Controller;

use App\Repository\InterventionRepository;
use App\Repository\UniteRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_')]
final class ApiController extends AbstractController
{
    // ======================================================
    // 1. API : LISTER LES UNITÉS ET LEUR ÉTAT
    // ======================================================
    #[Route('/unites', name: 'unites_list', methods: ['GET'])]
    public function getUnites(UniteRepository $uniteRepository): JsonResponse
    {
        $unites = $uniteRepository->findAllWithBaieAndLocataire();

        $data = [];
        foreach ($unites as $unite) {

            // On vérifie si l'unité a un locataire ET si la date de fin n'est pas dépassée
            $maintenant = new DateTime();
            $estLouee = false;

            if ($unite->getLocataire() !== null && $unite->getDateFinLocation() > $maintenant) {
                $estLouee = true;
            }

            $data[] = [
                'id' => $unite->getId(),
                'numero' => $unite->getNumero(),
                'etat' => $unite->getEtat(),
                'disponible' => !$estLouee,
                'locataire_id' => $estLouee ? $unite->getLocataire()->getId() : null,
                'date_fin_location' => $unite->getDateFinLocation()?->format('Y-m-d H:i:s'),
                'baie_id' => $unite->getBaie()?->getId(),
            ];
        }

        return $this->json($data);
    }

    // ======================================================
    // 2. API : LISTER LES INTERVENTIONS
    // ======================================================
    #[Route('/interventions', name: 'interventions_list', methods: ['GET'])]
    public function getInterventions(InterventionRepository $interventionRepository): JsonResponse
    {
        $interventions = $interventionRepository->findAllWithUnites();

        $data = [];
        foreach ($interventions as $intervention) {

            $unitesAffectees = [];
            foreach ($intervention->getUnites() as $unite) {
                $unitesAffectees[] = $unite->getId();
            }

            $data[] = [
                'id' => $intervention->getId(),
                'type' => $intervention->getType(),
                'description' => $intervention->getDescription(),
                'dateDebut' => $intervention->getDateDebut()?->format('Y-m-d H:i:s'),
                'dateFin' => $intervention->getDateFin()?->format('Y-m-d H:i:s'),
                'unites_affectees' => $unitesAffectees
            ];
        }

        return $this->json($data);
    }
}
