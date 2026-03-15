<?php

namespace App\Controller;

use App\Repository\InterventionRepository;
use App\Repository\UniteRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Intervention;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

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

    // ======================================================
    // 3. API : DÉCLARER UNE INTERVENTION (POST)
    // ======================================================
    /**
     * @throws /JsonException
     */
    #[Route('/interventions', name: 'interventions_create', methods: ['POST'])]
    public function createIntervention(Request $request, EntityManagerInterface $em, UniteRepository $uniteRepository): JsonResponse
    {
        // 1. Lire le JSON envoyé par l'application C#
        $jsonData = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        // 2. Le Vigile : Vérifier que l'application JAVA a bien envoyé toutes les infos
        if (!isset($jsonData['type'], $jsonData['description'], $jsonData['unites_ids']) || !is_array($jsonData['unites_ids'])) {
            return $this->json([
                'erreur' => 'Données invalides. Les champs "type", "description" et "unites_ids" (tableau) sont obligatoires.'
            ], 400); // 400 = Bad Request
        }

        // 3. Création de l'objet Intervention
        $intervention = new Intervention();
        $intervention->setType($jsonData['type']); // ex: "incident" ou "maintenance"
        $intervention->setDescription($jsonData['description']);
        $intervention->setDateDebut(new DateTime()); // L'intervention commence TOUT DE SUITE

        // 4. Lier les unités (Serveurs/Baies) qui sont en panne
        foreach ($jsonData['unites_ids'] as $uniteId) {
            $unite = $uniteRepository->find($uniteId);

            if ($unite) {
                $intervention->addUnite($unite);

                // n change l'état selon le type d'intervention déclaré par le JAVA
                $nouvelEtat = match ($jsonData['type']) {
                    'incident' => 'En panne',
                    'maintenance' => 'En maintenance',
                    default => 'Intervention en cours', // Sécurité au cas où ils envoient autre chose
                };

                $unite->setEtat($nouvelEtat);
                $em->persist($unite);
            }
        }

        // Sécurité : Si l'appli JAVA a envoyé des faux IDs d'unités qui n'existent pas
        if ($intervention->getUnites()->isEmpty()) {
            return $this->json([
                'erreur' => 'Aucune unité correspondante trouvée dans le datacenter.'
            ], 404); // 404 = Not Found
        }

        // 5. Sauvegarder dans la base de données
        $em->persist($intervention);
        $em->flush();

        // 6. Renvoyer la confirmation à l'application JAVA
        return $this->json([
            'message' => 'L\'intervention a été déclarée avec succès !',
            'intervention_id' => $intervention->getId()
        ], 201); // 201 = Created (Code HTTP standard pour une création réussie)
    }
}
