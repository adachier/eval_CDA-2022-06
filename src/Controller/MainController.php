<?php

namespace App\Controller;

use App\Entity\Brand;
use App\Repository\ComputerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ComputerRepository $computerRepository): Response
    {
        return $this->render('main/index.html.twig', [
            'computers' => $computerRepository->findBy([
                'is_visible' => true
            ])
        ]);
    }

    #[Route('/computer/by/{id}', name: 'app_tab')]
    public function tab(Brand $brand, ComputerRepository $computerRepository): Response
    {
        return $this->render('main/tab.html.twig', [
            'brand' => $brand,
            'computers' => $computerRepository->findBy([
                'is_visible' => true
            ])
        ]);
    }
}
