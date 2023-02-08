<?php

namespace App\Controller;

use App\Entity\Rating;
use App\Entity\User;
use App\Entity\Series;
use App\Entity\Season;
use App\Form\SeriesType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;


#[Route('/series')]
class SeriesController extends AbstractController
{
    #[Route('/', name: 'series_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
    {

        $debut = $request->query->get('debut',"");
        $page = $request->query->getInt('page',0);
        

        $series = $this->getSeries($entityManager, $debut,$page);

        $pagePrec = $page > 0;
        $pageSuiv = count($this->getSeries($entityManager,$debut, $page+1)) > 0;


        return $this->render('series/index.html.twig', [
            'series' => $series, "debut" => $debut,"page" => $page, "pagePrec" => $pagePrec, "pageSuiv" => $pageSuiv 
        ]);
    }

    #[Route('/{id}', name: 'series_show', methods: ['GET'])]
    public function show(EntityManagerInterface $entityManager, Series $series, int $id): Response
    {

        $saisons = $this->getSaisons( $id);

        /** @var User $user */
        $user = $this->getUser();
        $suivi = false;
        if($user != null){
            $suivi = $this->estSuivi($user,$series);
        }
        
        $coms = $this->getRates($entityManager, $id);
        return $this->render('series/show.html.twig', [
            'series' => $series, 'saisons' => $saisons, 'commentaires' => $coms, 'suivi' => $suivi
        ]);
    }



    #[Route("/poster/{id}", name: 'poster_series_show', methods: ['GET'])]
    public function getPoster(Series $serie): Response
    {
        return new Response(stream_get_contents($serie->getPoster()), 200, array(
            'Content-type'=>'image/jpeg',));
    }

    #[Route("/{id}/comment", name: 'comment_series', methods: ['POST'])]
    public function insertComment (EntityManagerInterface $entityManager, Series $series, Request $request){
        $note = $request->get('note',null);
        $com = $request->get('commentaire',null);
        $date = new \DateTime('@'.strtotime('now'));
        $user = $this->getUser();
        


        if($note >= 1 && $note <= 5){
            $rate = new Rating();
            $rate->setValue($note);
            $rate->setComment($com);
            $rate->setDate($date);
            $rate->setSeries($series);
            $rate->setUser($user);
    
            $entityManager->persist($rate);
            $entityManager->flush();
        }
       

        return $this->redirectToRoute('series_show',["id" => $series->getId()]);
    }

    

    #[Route("/{id}/suivre", name: 'suivre_serie', methods: ['GET'])]
    public function suivre (EntityManagerInterface $entityManager, Series $series){
       
        /** @var User $user */
        $user = $this->getUser();
        if(isset($user) && $user != null)
            $user->addSeries($series);
            $entityManager->persist($user);
            $entityManager->flush();
        
            
            return $this->redirectToRoute('series_show',["id" => $series->getId()]);
    }

    #[Route("/{id}/arreter_suivre", name: 'arreter_suivre_serie', methods: ['GET'])]
    public function arreter_suivre (EntityManagerInterface $entityManager, Series $series){
       
        /** @var User $user */
        $user = $this->getUser();
        if(isset($user) && $user != null)
            $user->removeSeries($series);

        $entityManager->persist($user);
        $entityManager->flush();
            
       return $this->redirectToRoute('compte');
    }

    public function estSuivi(User $user, Series $series): Bool
    {
        return $user->getSeries()->contains($series);
    }

    public function getRates(EntityManagerInterface $entityManager, int $serie_id){
        $query = $entityManager
            ->createQuery(
                "SELECT r
                FROM App:Rating r
                WHERE r.series = :id
                ORDER BY r.date DESC"
            );
        $query->setParameter("id",$serie_id);

        $coms = $query->getResult();
        return $coms;
    }

    public function getSeries(EntityManagerInterface $entityManager, string $debut, int $page){
        $nbSeries = 12;
        $query = $entityManager
            ->createQuery(
                "SELECT s
                FROM App:Series s
                WHERE s.title
                LIKE :debut"
            );
        $query->setParameter("debut",$debut."%");

        $series = $query->setFirstResult($page*$nbSeries)
                ->setMaxResults($nbSeries)
                ->getResult();
  
        return $series;
    }

    public function getOrderSeries(EntityManagerInterface $entityManager, string $debut, int $page){
        $nbSeries = 12;
        /*$query = $entityManager
            ->createQuery(
                "SELECT s
                FROM App:Series s
                JOIN App:Rating avg(r.value) as rate on r.series = s.id
                WHERE s.title
                LIKE :debut
                ORDER BY rate ASC"
            );*/

            
        $query->setParameter("debut",$debut."%");

        $series = $query->setFirstResult($page*$nbSeries)
                ->setMaxResults($nbSeries)
                ->getResult();


                    /*
            SELECT
            m.* 
            FROM 
            "memberships" AS m
            JOIN "people" AS p on p.personid = m.personID
            WHERE
            m.groupId = 32
            ORDER BY 
            p.name
            */
  
        return $series;
    }
    
    public function getSaisons(int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em ->getRepository(Season::class);
        $saisons = $repo->createQueryBuilder('s')
            ->select("s")
            ->where("s.series = :id")
            ->setParameter("id",$id)
            ->getQuery()
            ->getResult()
            ;
        return $saisons;
    }


    /*
    #[Route('/new', name: 'series_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $series = new Series();
        $form = $this->createForm(SeriesType::class, $series);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($series);
            $entityManager->flush();

            return $this->redirectToRoute('series_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('series/new.html.twig', [
            'series' => $series,
            'form' => $form,
        ]);
    }
    */

    /*
    #[Route('/{id}/edit', name: 'series_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Series $series, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SeriesType::class, $series);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('series_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('series/edit.html.twig', [
            'series' => $series,
            'form' => $form,
        ]);
    }
    */

    /*
    #[Route('/{id}', name: 'series_delete', methods: ['POST'])]
    public function delete(Request $request, Series $series, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$series->getId(), $request->request->get('_token'))) {
            $entityManager->remove($series);
            $entityManager->flush();
        }

        return $this->redirectToRoute('series_index', [], Response::HTTP_SEE_OTHER);
    }
    */
}
