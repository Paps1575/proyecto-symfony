<?php

namespace App\Controller;

use App\Entity\Registro;
use App\Form\RegistroType;
use App\Model\RegistroDatos;
use App\Repository\ImagenCarruselRepository; // <--- Para el carrusel
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

    // ESTA ES LA RUTA QUE BUSCA EL BOTÓN AZUL "VER CRUD"
    #[Route('/crud-api', name: 'app_registro_api')]
    public function crud(): Response
    {
        return $this->render('registro_api/index.html.twig');
    }

    // ESTA ES LA RUTA QUE BUSCA EL BOTÓN MORADO "CARRUSEL"
    #[Route('/carrusel', name: 'app_carrusel_view')]
    public function carruselView(ImagenCarruselRepository $repo): Response
    {
        return $this->render('carrusel/index.html.twig', [
            'imagenes' => $repo->findAll(),
        ]);
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
            $gRecaptchaResponse = $request->request->get('g-recaptcha-response');
            $resp = $recaptcha->verify($gRecaptchaResponse);

            if ($resp->isSuccess()) {
                $request->getSession()->set('usuario_nombre', $datos->nombre);
                return $this->redirectToRoute('app_confirmar');
            }
            $this->addFlash('error', 'Google rechazó el captcha.');
        }

        return $this->render('app/registro.html.twig', [
            'formulario' => $form->createView(),
        ]);
    }

    // ... (Mantén confirmar y exito igual)
}
