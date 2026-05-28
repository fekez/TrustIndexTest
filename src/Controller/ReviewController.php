<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Review;
use App\Enum\ReviewState;
use App\Form\ReviewType;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;

class ReviewController extends AbstractController
{
    public function __construct(private readonly CacheInterface $cache)
    {
    }

    #[Route('/', name: 'review_index', methods: ['GET'])]
    public function index(Request $request, ReviewRepository $repository, PaginatorInterface $paginator): Response
    {
        $query = $repository->createQueryBuilder('r')
            ->andWhere('r.objectState = :state')
            ->setParameter('state', ReviewState::Published)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('review/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/review/new', name: 'review_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        LoggerInterface $logger,
        RateLimiterFactoryInterface $reviewSubmitLimiter,
    ): Response {
        $review = new Review();
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $limiter = $reviewSubmitLimiter->create($request->getClientIp());
            if (!$limiter->consume(1)->isAccepted()) {
                $this->addFlash('danger', 'Túl sok beküldés. Kérjük várj 10 percet.');

                return $this->render('review/new.html.twig', [
                    'form' => $form,
                ]);
            }

            if ($form->isValid()) {
                $em->persist($review);
                $em->flush();

                $logger->info('review.created', [
                    'id' => $review->getId(),
                    'company_name' => $review->getCompanyName(),
                    'rating' => $review->getRating(),
                    'author_email' => $review->getAuthorEmail(),
                ]);

                try {
                    $this->cache->delete('company_stats_'.md5(''));
                } catch (\Psr\Cache\InvalidArgumentException) {
                }

                $this->addFlash('success', 'A véleményed sikeresen elküldve!');

                return $this->redirectToRoute('review_index');
            }
        }

        return $this->render('review/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/review/{id}', name: 'review_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, ReviewRepository $repository): Response
    {
        $review = $repository->find($id);

        if (null === $review || $review->isTrash()) {
            throw $this->createNotFoundException('A keresett vélemény nem található.');
        }

        return $this->render('review/show.html.twig', [
            'review' => $review,
        ]);
    }

    #[Route('/companies', name: 'companies_index', methods: ['GET'])]
    public function companies(Request $request, ReviewRepository $repository, PaginatorInterface $paginator): Response
    {
        $query = trim((string) $request->query->get('q', ''));

        $stats = $repository->getCompanyStats('' !== $query ? $query : null);

        $pagination = $paginator->paginate(
            $stats,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('companies/index.html.twig', [
            'pagination' => $pagination,
            'query' => $query,
        ]);
    }

    #[Route('/review/{id}/delete', name: 'review_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(
        int $id,
        Request $request,
        ReviewRepository $repository,
        EntityManagerInterface $em,
        RateLimiterFactoryInterface $reviewDeleteLimiter,
    ): Response {
        $limiter = $reviewDeleteLimiter->create($request->getClientIp());
        if (!$limiter->consume(1)->isAccepted()) {
            $this->addFlash('danger', 'Túl sok törlési kísérlet. Kérjük várj 10 percet.');

            return $this->redirectToRoute('review_index');
        }

        $review = $repository->find($id);

        if (null === $review || $review->isTrash()) {
            throw $this->createNotFoundException('A keresett vélemény nem található.');
        }

        $review->setObjectState(ReviewState::Trash);
        $em->flush();

        try {
            $this->cache->delete('company_stats_'.md5(''));
        } catch (\Psr\Cache\InvalidArgumentException) {
        }

        $this->addFlash('success', 'A vélemény sikeresen törölve.');

        return $this->redirectToRoute('review_index');
    }

    #[Route('/health', name: 'health_check', methods: ['GET'])]
    public function health(EntityManagerInterface $em): JsonResponse
    {
        try {
            $em->getConnection()->executeQuery('SELECT 1');
            $dbStatus = 'ok';
        } catch (\Throwable) {
            $dbStatus = 'error';
        }

        $status = 'ok' === $dbStatus ? 'ok' : 'error';
        $httpCode = 'ok' === $status ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE;

        return new JsonResponse([
            'status' => $status,
            'db' => $dbStatus,
            'timestamp' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ], $httpCode);
    }
}
