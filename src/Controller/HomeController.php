<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Service\SearchUseCase;
use App\Domain\Model\SearchCriteria;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Exception\UnexpectedValueException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    /**
     * @param Request $request
     * @param SearchUseCase $searchUseCase
     * @return Response
     * @throws UnexpectedValueException
     */
    #[Route('/', name: 'app_home')]
    public function index(Request $request, SearchUseCase $searchUseCase): Response
    {
        $query = $request->query->getString('q');
        $page = $request->query->getInt('page', 1);
        /** @var array<string, string> $filters */
        $filters = $request->query->all('filters');

        $results = null;

        if ('' !== $query) {
            try {
                $criteria = new SearchCriteria($query, $page, 10, $filters);
                $results = $searchUseCase->execute($criteria);
            } catch (InvalidArgumentException $e) {
                throw new BadRequestException($e->getMessage(), previous: $e);
            }
        }

        return $this->render('home/index.html.twig', [
            'query' => $query,
            'results' => $results,
            'currentPage' => $page,
            'activeFilters' => $filters,
        ]);
    }
}
