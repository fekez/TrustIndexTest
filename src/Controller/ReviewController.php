<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}
