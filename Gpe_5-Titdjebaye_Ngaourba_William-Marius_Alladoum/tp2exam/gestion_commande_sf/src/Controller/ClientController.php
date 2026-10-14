<?php

namespace App\Controller;

use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ClientController extends AbstractController
{
    #[Route('/clients', name: 'app_client_index', methods: ['GET'])]
    public function index(ClientRepository $repo): Response
    {
        return $this->render('client/index.html.twig', ['clients' => $repo->listeAvecTotaux()]);
    }
}
