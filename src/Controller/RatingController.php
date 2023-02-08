<?php

namespace App\Controller;

use App\Entity\Rating;
use App\Form\Rating1Type;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/rating')]
class RatingController extends AbstractController
{

    #[Route('/{id},{id_serie}', name: 'rating_delete', methods: ['GET'])]
    public function delete( Rating $rating, EntityManagerInterface $entityManager, int $id_serie): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if($user != null && $user->getAdmin()){
            //DELETE FROM table_name WHERE condition;
            $query = $entityManager
            ->createQuery(
                "DELETE
                FROM App:Rating r
                WHERE r.id = :id
                "
            );
            $query->setParameter("id",$rating->getId());
            $query->execute();
        }
       

        return $this->redirectToRoute('series_show', ['id'=> $id_serie], Response::HTTP_SEE_OTHER);
    }

/*
    #[Route('/', name: 'rating_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $ratings = $entityManager
            ->getRepository(Rating::class)
            ->findAll();

        return $this->render('rating/index.html.twig', [
            'ratings' => $ratings,
        ]);
    }

    #[Route('/new', name: 'rating_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $rating = new Rating();
        $form = $this->createForm(Rating1Type::class, $rating);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($rating);
            $entityManager->flush();

            return $this->redirectToRoute('rating_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('rating/new.html.twig', [
            'rating' => $rating,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'rating_show', methods: ['GET'])]
    public function show(Rating $rating): Response
    {
        return $this->render('rating/show.html.twig', [
            'rating' => $rating,
        ]);
    }

    #[Route('/{id}/edit', name: 'rating_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Rating $rating, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Rating1Type::class, $rating);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('rating_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('rating/edit.html.twig', [
            'rating' => $rating,
            'form' => $form,
        ]);
    }

    */
}
