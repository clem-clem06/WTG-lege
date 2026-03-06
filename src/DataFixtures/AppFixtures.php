<?php

namespace App\DataFixtures;

use App\Entity\Baie;
use App\Entity\Unite;
use App\Entity\Offre;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // 1. CRÉATION DES OFFRES COMMERCIALES (Prix en centimes)
        $offresData = [
            // Base: 100€/mois | Annuel: (100 * 12) - 10% = 1080€
            ['nom' => 'Base', 'unites' => 1, 'prixMensuel' => 10000, 'prixAnnuel' => 108000],

            // Start-up: 900€/mois | Annuel: (900 * 12) - 10% = 9720€
            ['nom' => 'Start-up', 'unites' => 10, 'prixMensuel' => 90000, 'prixAnnuel' => 972000],

            // PME: 1680€/mois | Annuel: (1680 * 12) - 10% = 18144€
            ['nom' => 'PME', 'unites' => 21, 'prixMensuel' => 168000, 'prixAnnuel' => 1814400],

            // Entreprise: 2940€/mois | Annuel: (2940 * 12) - 10% = 31752€
            ['nom' => 'Entreprise', 'unites' => 42, 'prixMensuel' => 294000, 'prixAnnuel' => 3175200],
        ];

        foreach ($offresData as $data) {
            $offre = new Offre();
            $offre->setNom($data['nom']);
            $offre->setNombreUnites($data['unites']);
            $offre->setPrixMensuel($data['prixMensuel']);
            $offre->setPrixAnnuel($data['prixAnnuel']);
            $manager->persist($offre);
        }

        // 2. CRÉATION DES BAIES ET DES UNITÉS
        for ($b = 1; $b <= 30; $b++) {
            $baie = new Baie();
            $referenceBaie = 'B' . str_pad((string)$b, 3, '0', STR_PAD_LEFT);
            $baie->setReference($referenceBaie);
            $manager->persist($baie);

            for ($u = 1; $u <= 42; $u++) {
                $unite = new Unite();
                $numeroUnite = 'U' . str_pad((string)$u, 2, '0', STR_PAD_LEFT);
                $unite->setNumero($numeroUnite);
                $unite->setEtat('OK');
                $unite->setBaie($baie);
                $manager->persist($unite);
            }
        }

        // On envoie tout dans la base de données
        $manager->flush();
    }
}
