<?php

namespace App\Controller;

use App\Repository\ThirdPartyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ThirdPartyController extends AbstractController
{
    #[Route('/third-parties', name: 'app_third_parties', methods: ['GET'])]
    public function index(ThirdPartyRepository $thirdParties): Response
    {
        return $this->render('third_party/index.html.twig', [
            'third_parties' => $thirdParties->findRecent(),
        ]);
    }
}
