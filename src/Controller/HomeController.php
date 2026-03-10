<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Service\SearchUseCase;
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
     * @throws BadRequestException
     * @throws UnexpectedValueException
     */
    #[Route('/', name: 'app_home')]
    public function index(Request $request, SearchUseCase $searchUseCase): Response
    {
        try {
            $query = $request->query->get('q', '*:*');
            $page = $request->query->getInt('page', 1);
            /** @var array<string, string> $filters */
            $filters = $request->query->all('filters');
        } catch (UnexpectedValueException|BadRequestException $e) {
            throw new BadRequestException($e->getMessage(), previous: $e);
        }

        $limit = 10;
        $offset = ($page - 1) * $limit;

        if ($page < 1) {
            $page = 1;
            $offset = 0;
        }

        $results = null;

        if ($query !== '*:*') {
            $results = $searchUseCase->execute($query, $offset, $limit, $filters);
        }

        return $this->render('home/index.html.twig', [
            'query' => $query === '*:*' ? '' : $query,
            'results' => $results,
            'currentPage' => $page,
            'activeFilters' => $filters,
        ]);
    }
}
