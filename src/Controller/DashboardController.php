<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'modules' => [
                ['name' => 'Tiers & CRM', 'description' => 'Clients, prospects et fournisseurs', 'status' => 'À construire'],
                ['name' => 'Ventes', 'description' => 'Devis, commandes et factures', 'status' => 'À construire'],
                ['name' => 'Achats', 'description' => 'Demandes, commandes et fournisseurs', 'status' => 'À construire'],
                ['name' => 'Stocks', 'description' => 'Articles, dépôts et mouvements', 'status' => 'À construire'],
            ],
        ]);
    }
}
