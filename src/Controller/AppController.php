<?php
namespace App\Controller;

use App\Repository\ImagenCarruselRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AppController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response {
        return $this->render('home/index.html.twig');
    }

    // ESTA ES LA RUTA DEL REGISTRO (La que tiene reCAPTCHA)
    #[Route('/registro', name: 'app_registro')]
    public function registro(): Response {
        // ... (aquí va tu lógica de reCAPTCHA que ya tenías)
        return $this->render('app/registro.html.twig');
    }

    // ESTA ES LA RUTA DEL CRUD API
    #[Route('/crud-api', name: 'app_registro_api')]
    public function crud(): Response {
        return $this->render('registro_api/index.html.twig');
    }

    // ESTA ES LA RUTA DEL CARRUSEL
    #[Route('/carrusel', name: 'app_carrusel_view')]
    public function carrusel(ImagenCarruselRepository $repo): Response {
        return $this->render('carrusel/index.html.twig', [
            'imagenes' => $repo->findAll()
        ]);
    }
}
