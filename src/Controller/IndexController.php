<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController'
        ]);
    }

    #[Route('/apropos', name: 'apropos')]
    public function apropos(): Response
    {
        return $this->render('index/apropos.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }

    #[Route('/user', name: 'compte')]
    public function compte(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if($user == null){
            return $this->render('index/index.html.twig', [
                'controller_name' => 'IndexController',
            ]);
        }

        $series = $user->getSeries();

        return $this->render('index/compte.html.twig', [
            'series' => $series,'user' => $user
        ]);
    }
}
