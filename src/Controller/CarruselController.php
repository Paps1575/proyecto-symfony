<?php

namespace App\Controller;

use App\Entity\ImagenCarrusel;
use App\Repository\ImagenCarruselRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CarruselController extends AbstractController
{
    #[Route('/carrusel', name: 'app_carrusel')]
    public function index(ImagenCarruselRepository $repo): Response
    {
        return $this->render('carrusel/index.html.twig', [
            'imagenes' => $repo->findAll(),
        ]);
    }

    #[Route('/carrusel/subir', name: 'app_carrusel_subir', methods: ['POST'])]
    public function subir(Request $request, EntityManagerInterface $em): Response
    {
        $archivo = $request->files->get('imagen');
        if ($archivo) {
            $nuevoNombre = uniqid().'.'.$archivo->guessExtension();
            $archivo->move($this->getParameter('kernel.project_dir').'/public/uploads', $nuevoNombre);

            $img = new ImagenCarrusel();
            $img->setRuta($nuevoNombre);
            $em->persist($img);
            $em->flush();
        }

        return $this->redirectToRoute('app_carrusel');
    }
}
