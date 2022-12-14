<?php

namespace App\Controller;

use App\Entity\Computer;
use App\Entity\Favorite;
use App\Form\ComputerType;
use App\Repository\ComputerRepository;
use App\Repository\FavoriteRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/computer')]
class ComputerController extends AbstractController
{
    #[Route('/', name: 'app_computer_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(ComputerRepository $computerRepository): Response
    {
        return $this->render('computer/index.html.twig', [
            'computers' => $computerRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_computer_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, ComputerRepository $computerRepository): Response
    {
        $author = $this->getUser();
        $computer = new Computer();
        $form = $this->createForm(ComputerType::class, $computer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $lettres = range('A', 'Z');
            shuffle($lettres);
            $lettre = array_shift($lettres);
            shuffle($lettres);
            $lettre .= array_shift($lettres);
            shuffle($lettres);
            $lettre .= array_shift($lettres);
            // un nombre sur 4 digit au hasard
            $nombre = mt_rand(10, 99);
            $reference = $lettre.$nombre;

            $computer->setReference($reference);
            $computer->setAuthor($author);
            $computer->setIsVisible(true);
            $computerRepository->save($computer, true);
            return $this->redirectToRoute('app_computer_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('computer/new.html.twig', [
            'computer' => $computer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_computer_show', methods: ['GET'])]
    public function show(Computer $computer): Response
    {
        return $this->render('computer/show.html.twig', [
            'computer' => $computer,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_computer_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit($id, Request $request, Computer $computer, ComputerRepository $computerRepository): Response
    {
        $thisAnnonce = $computerRepository->find($id);
        if($thisAnnonce->isIsVisible() == false) {
            $this->addFlash('Erreur', 'Cette annonce n\'existe plus');
            return $this->redirectToRoute('app_home');
        }
        $form = $this->createForm(ComputerType::class, $computer);
        $form->handleRequest($request);
        $author = $this->getUser();
        if($author == false){
            $this->addFlash('Erreur', 'Vous devez avoir un compte pour ajouter/modifier une anonnce');
            return $this->redirectToRoute('app_home');
        }

        if($this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN') or $computer->getAuthor()==$author) {
            if ($form->isSubmitted() && $form->isValid()) {
                $computerRepository->save($computer, true);

                $this->addFlash('Succès', 'Votre annonce a bien été modifiée');
                return $this->redirectToRoute('app_computer_index', [], Response::HTTP_SEE_OTHER);
            }
        }else {
            $this->addFlash('Erreur', 'Vous n\'êtes pas l\'auteur de cette anonnce');
            return $this->redirectToRoute('app_home');
        }

        return $this->renderForm('computer/edit.html.twig', [
            'computer' => $computer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_computer_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Computer $computer, ComputerRepository $computerRepository): Response
    {
        $author = $this->getUser();
        if($author == false){
            $this->addFlash('Erreur', 'Votre devez avoir un compte pour ajouter une annonce');
            return $this->redirectToRoute('home');
        }
        if($this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN') or $computer->getAuthor() == $author) {
            $computer->setisVisible(false);
            $computerRepository->save($computer);
        }else {
            $this->addFlash('Erreur', "Vous n'êtes pas l'auteur de cette annonce !" );
            return $this->redirectToRoute('app_annonce_show', ['id' => $computer->getId()]);
        }
        $this->addFlash('Succès', "Votre annonce a bien été supprimée !" );

        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/fav', name: 'app_annonce_fav', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function favUserAnnonce(Computer $computer, FavoriteRepository $favRepo): Response
    {
        $user = $this->getUser();
        if(!$user) return $this->redirectToRoute('app_login');

        if($computer->isUserfav($user)){
            $signedUp = $favRepo->findOneBy([
                'computers' => $computer,
                'users' => $user
            ]);
            $favRepo ->remove($signedUp);
            $this->addFlash('Erreur', "Cette annonce n'est plus dans vos favoris" );

            return $this->redirectToRoute('app_home');
        }

        $newFav = new Favorite();
        $newFav->setComputers($computer)
            ->setUsers($user);

        $favRepo->save($newFav);
        $this->addFlash('Succès', "Cette annonce est désormais dans vos favoris" );

        return $this->redirectToRoute('app_profile');
    }
}
