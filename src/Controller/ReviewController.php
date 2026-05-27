<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReviewController extends AbstractController
{
    #[Route('/', name: 'review_index', methods: ['GET'])]
    public function index(ReviewRepository $repository): Response
    {
        return $this->render('review/index.html.twig', [
            'reviews' => $repository->findAllOrderedByDate(),
        ]);
    }

    #[Route('/review/new', name: 'review_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        LoggerInterface $logger,
    ): Response {
        $review = new Review();
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($review);
            $em->flush();

            $logger->info('review.created', [
                'id' => $review->getId(),
                'company_name' => $review->getCompanyName(),
                'rating' => $review->getRating(),
                'author_email' => $review->getAuthorEmail(),
            ]);

            $this->addFlash('success', 'A véleményed sikeresen elküldve!');

            return $this->redirectToRoute('review_index');
        }

        return $this->render('review/new.html.twig', [
            'form' => $form,
        ]);
    }
}
