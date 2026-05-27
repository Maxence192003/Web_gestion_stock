<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_root')]
    public function redirectToWelcome(): RedirectResponse
    {
        return $this->redirectToRoute('app_home');
    }

    #[Route('/home', name: 'app_home_legacy')]
    #[Route('/accueil', name: 'app_home')]
    #[Route('/aceuil', name: 'app_home_fallback')]
    public function index(): Response
    {
        $downloadDirectory = $this->getParameter('kernel.project_dir').'/public/downloads';
        $apkFiles = glob($downloadDirectory.'/*.apk') ?: [];
        $apkPathname = $apkFiles[0] ?? null;
        $apkVersion = $apkPathname ? (string) filemtime($apkPathname) : null;

        return $this->render('home/index.html.twig', [
            'apkFilename' => $apkPathname ? basename($apkPathname) : null,
            'apkAvailable' => null !== $apkPathname,
            'apkVersion' => $apkVersion,
        ]);
    }
}
