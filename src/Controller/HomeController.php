<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\SolrClientService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, SolrClientService $solrClient): Response
    {
        $query = $request->query->get('q', '*:*');
        $results = null;

        if ($query !== '*:*') {
            $results = $solrClient->search($query);
        }

        return $this->render('home/index.html.twig', [
            'query' => $query === '*:*' ? '' : $query,
            'results' => $results,
        ]);
    }
}
