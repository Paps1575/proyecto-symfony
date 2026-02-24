<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegistroViewController extends AbstractController
{
    // Esta es la ruta que tu "home/index.html.twig" está gritando que no encuentra
    #[Route('/crud-api', name: 'app_registro_api')]
    public function index(): Response
    {
        return $this->render('registro_api/index.html.twig');
    }
}
