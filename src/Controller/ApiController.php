<?php

namespace App\Controller;

use App\Repository\InterventionRepository;
use App\Repository\UniteRepository;
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
        $unites = $uniteRepository->findAll();

        $data = [];
        foreach ($unites as $unite) {
            $data[] = [
                'id' => $unite->getId(),
                'nom' => $unite->getNom(),
                // On vérifie si l'unité est louée
                'disponible' => $unite->getNom() === null,
                // On récupère l'ID de la baie à laquelle elle appartient
                'baie_id' => $unite->getBaie()?->getId(),
            ];
        }

        // $this->json() transforme automatiquement le tableau en vrai JSON !
        return $this->json($data, 200, [
            'Access-Control-Allow-Origin' => '*'
        ]);
    }

    // ======================================================
    // 2. API : LISTER LES INTERVENTIONS
    // ======================================================
    #[Route('/interventions', name: 'interventions_list', methods: ['GET'])]
    public function getInterventions(InterventionRepository $interventionRepository): JsonResponse
    {
        $interventions = $interventionRepository->findAll();

        $data = [];
        foreach ($interventions as $intervention) {
            // On récupère les IDs des unités touchées par cette intervention
            $unitesAffectees = [];
            foreach ($intervention->getUnites() as $unite) {
                $unitesAffectees[] = $unite->getId();
            }

            $data[] = [
                'id' => $intervention->getId(),
                'type' => $intervention->getType(), // "incident" ou "maintenance"
                'description' => $intervention->getDescription(),
                'dateDebut' => $intervention->getDateDebut()?->format('Y-m-d H:i:s'),
                'dateFin' => $intervention->getDateFin()?->format('Y-m-d H:i:s'),
                'unites_affectees' => $unitesAffectees
            ];
        }

        return $this->json($data, 200, [
            'Access-Control-Allow-Origin' => '*'
        ]);
    }
}
