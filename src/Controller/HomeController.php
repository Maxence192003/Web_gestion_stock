<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    #[Route('/', name: 'app_root')]
    public function index(): Response
    {
        $downloadDirectory = $this->getParameter('kernel.project_dir').'/public/downloads';
        $apkFiles = glob($downloadDirectory.'/*.apk') ?: [];
        $apkPathname = $apkFiles[0] ?? null;

        return $this->render('home/index.html.twig', [
            'apkFilename' => $apkPathname ? basename($apkPathname) : null,
            'apkAvailable' => null !== $apkPathname,
        ]);
    }
}
