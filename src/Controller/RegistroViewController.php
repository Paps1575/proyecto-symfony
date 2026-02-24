<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegistroViewController extends AbstractController
{
    #[Route('/crud-api', name: 'app_registro_api')]
    public function index(): Response
    {
        return $this->render('registro_api/index.html.twig');
    }

    #[Route('/carrusel', name: 'app_carrusel')]
    public function carrusel(): Response
    {
        return $this->render('carrusel/index.html.twig');
    }
}
