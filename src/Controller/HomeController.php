<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'breadcrumbs' => [
                ['name' => 'Inicio', 'url' => '#'],
            ],
        ]);
    }

    #[Route('/crud-api', name: 'app_crud_api')]
    public function crudApi(): Response
    {
        return $this->render('registro_api/index.html.twig', [
            'breadcrumbs' => [
                ['name' => 'Inicio', 'url' => $this->generateUrl('app_home')],
                ['name' => 'CRUD API', 'url' => '#'],
            ],
        ]);
    }
}
