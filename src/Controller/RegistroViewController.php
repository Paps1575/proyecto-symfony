<?php
namespace App\Controller;

use App\Repository\ImagenCarruselRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegistroViewController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home(): Response {
        return $this->render('home/index.html.twig');
    }

    #[Route('/crud-api', name: 'app_registro_api')]
    public function crud(): Response {
        return $this->render('registro_api/index.html.twig');
    }

    #[Route('/carrusel', name: 'app_carrusel_view')]
    public function carrusel(ImagenCarruselRepository $repo): Response {
        return $this->render('carrusel/index.html.twig', [
            'imagenes' => $repo->findAll()
        ]);
    }
}
