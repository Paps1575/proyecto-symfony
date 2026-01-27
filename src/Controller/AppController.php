<?php

namespace App\Controller;

use App\Entity\Registro;
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
    #[Route('/registro', name: 'app_registro')]
    public function registro(Request $request): Response
    {
        $datos = new RegistroDatos();
        $form = $this->createForm(RegistroType::class, $datos);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // LLAVE SECRETA DE TU CAPTURA
            $secret = '6LdJQlcsAAAAAAjD68LES59fdLyxsThXkDPuRz62';
            $recaptcha = new ReCaptcha($secret);

            // Google manda el token en este campo específico
            $gRecaptchaResponse = $request->request->get('g-recaptcha-response');

            // Verificamos directamente con Google
            $resp = $recaptcha->verify($gRecaptchaResponse);

            if ($resp->isSuccess()) {
                $request->getSession()->set('usuario_nombre', $datos->nombre);
                return $this->redirectToRoute('app_confirmar');
            }

            // Si falla, mostramos el código de error real de Google
            $errorCodes = implode(', ', $resp->getErrorCodes());
            $this->addFlash('error', 'Google rechazó el captcha. Error: ' . $errorCodes);
        }

        return $this->render('app/registro.html.twig', [
            'formulario' => $form->createView(),
            'breadcrumbs' => [
                ['name' => 'Inicio', 'url' => '/'],
                ['name' => 'Registro', 'url' => '#'],
            ],
        ]);
    }

    #[Route('/confirmar', name: 'app_confirmar')]
    public function confirmar(Request $request): Response
    {
        $nombre = $request->getSession()->get('usuario_nombre', 'Desconocido');
        return $this->render('app/confirmar.html.twig', [
            'nombre' => $nombre,
            'breadcrumbs' => [
                ['name' => 'Inicio', 'url' => '/'],
                ['name' => 'Registro', 'url' => $this->generateUrl('app_registro')],
                ['name' => 'Confirmar', 'url' => '#'],
            ],
        ]);
    }

    #[Route('/exito', name: 'app_exito', methods: ['GET', 'POST'])]
    public function exito(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $nombre = $request->getSession()->get('usuario_nombre', 'Anónimo');
            $registro = new Registro();
            $registro->setNombre($nombre);
            try {
                $em->persist($registro);
                $em->flush();
                $this->addFlash('success', '¡Registro exitoso en Railway!');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error de BD: ' . $e->getMessage());
                return $this->redirectToRoute('app_registro');
            }
        }
        return $this->render('app/exito.html.twig', [
            'breadcrumbs' => [
                ['name' => 'Inicio', 'url' => '/'],
                ['name' => 'Registro', 'url' => $this->generateUrl('app_registro')],
                ['name' => 'Confirmar', 'url' => $this->generateUrl('app_confirmar')],
                ['name' => 'Éxito', 'url' => '#'],
            ],
        ]);
    }
}
