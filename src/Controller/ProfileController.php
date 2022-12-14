<?php

namespace App\Controller;

use App\Repository\ComputerRepository;
use App\Repository\FavoriteRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    #[IsGranted('ROLE_USER')]
    public function index(FavoriteRepository $favRepo, ComputerRepository $computerRepo): Response
    {
        $user = $this->getUser();
        return $this->render('profile/index.html.twig', [
            'computersFav' => $favRepo->findBy([
                'users' => $user,
            ]),
            'computersOwned' => $computerRepo->findBy([
                'author' => $user,
                'is_visible' => true
            ])
        ]);
    }
}
