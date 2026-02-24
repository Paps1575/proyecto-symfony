<?php

namespace App\Controller;

use App\Entity\Registro;
use App\Entity\ImagenCarrusel;
use App\Form\RegistroType;
use App\Model\RegistroDatos;
use Doctrine\ORM\EntityManagerInterface;
use ReCaptcha\ReCaptcha;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AppController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }

    #[Route('/crud-api', name: 'app_registro_api')]
    public function crud(): Response
    {
        return $this->render('registro_api/index.html.twig');
    }

    #[Route('/registro', name: 'app_registro')]
    public function registro(Request $request): Response
    {
        $datos = new RegistroDatos();
        $form = $this->createForm(RegistroType::class, $datos);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $secret = '6LdJQlcsAAAAAAjD68LES59fdLyxsThXkDPuRz62';
            $recaptcha = new ReCaptcha($secret);
            $resp = $recaptcha->verify($request->request->get('g-recaptcha-response'));

            if ($resp->isSuccess()) {
                $request->getSession()->set('usuario_nombre', $datos->nombre);
                return $this->redirectToRoute('app_confirmar');
            }
            $this->addFlash('error', 'Captcha no válido.');
        }

        return $this->render('app/registro.html.twig', [
            'formulario' => $form->createView(), // CORRECCIÓN: Siempre enviar el createView
        ]);
    }

    #[Route('/carrusel', name: 'app_carrusel')]
    public function carrusel(EntityManagerInterface $em): Response
    {
        // CORRECCIÓN: Buscamos directo del EM para evitar error de repositorio
        $imagenes = $em->getRepository(ImagenCarrusel::class)->findAll();
        return $this->render('carrusel/index.html.twig', [
            'imagenes' => $imagenes,
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

    // Rutas de apoyo (Confirmar y Éxito)
    #[Route('/confirmar', name: 'app_confirmar')]
    public function confirmar(Request $request): Response {
        return $this->render('app/confirmar.html.twig', ['nombre' => $request->getSession()->get('usuario_nombre', 'Usuario')]);
    }
}
