<?php

namespace App\Controller;

use App\Form\RegistroType;
use App\Model\RegistroDatos;
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
        return $this->render('home/index.html.twig', [
            'breadcrumbs' => [['name' => 'Inicio', 'url' => '#']]
        ]);
    }

    #[Route('/registro', name: 'app_registro')]
    public function registro(Request $request): Response
    {
        $datos = new RegistroDatos();
        $form = $this->createForm(RegistroType::class, $datos);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TU SECRET KEY OFICIAL (uRz62)
            $secret = '6LdJQlcsAAAAAAjD68LES59fdLyxsThXkDPuRz62';
            $recaptcha = new ReCaptcha($secret);
            $gRecaptchaResponse = $request->request->get('g-recaptcha-response');

            $resp = $recaptcha->verify($gRecaptchaResponse);

            if ($resp->isSuccess()) {
                $request->getSession()->set('usuario_nombre', $datos->nombre);
                return $this->redirectToRoute('app_confirmar');
            }
            $this->addFlash('error', 'Google rechazó el captcha. Intenta de nuevo.');
        }

        return $this->render('app/registro.html.twig', [
            'formulario' => $form->createView(),
            'breadcrumbs' => [
                ['name' => 'Inicio', 'url' => $this->generateUrl('app_home')],
                ['name' => 'Registro', 'url' => '#']
            ]
        ]);
    }

    #[Route('/confirmar', name: 'app_confirmar')]
    public function confirmar(Request $request): Response
    {
        $nombre = $request->getSession()->get('usuario_nombre', 'Usuario');
        return $this->render('app/confirmar.html.twig', [
            'nombre' => $nombre,
            'breadcrumbs' => [
                ['name' => 'Inicio', 'url' => $this->generateUrl('app_home')],
                ['name' => 'Registro', 'url' => $this->generateUrl('app_registro')],
                ['name' => 'Confirmar', 'url' => '#']
            ]
        ]);
    }
}
