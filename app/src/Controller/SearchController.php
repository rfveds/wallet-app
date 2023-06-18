<?php
/**
 * Search controller.
 */

namespace App\Controller;

use App\Service\OperationServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SearchController.
 */
class SearchController extends AbstractController
{
    /**
     * Operation service.
     */
    private OperationServiceInterface $operationService;

    /**
     * SearchController constructor.
     *
     * @param OperationServiceInterface $operationService Operation service
     */
    public function __construct(OperationServiceInterface $operationService)
    {
        $this->operationService = $operationService;
    }

    /**
     * Search action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route(
        path: '/search',
        name: 'search',
        methods: ['GET'],
    )]
    public function search(Request $request): Response
    {
        $user = $this->getUser();
        $filters = $this->getFilters($request);

        $pagination = $this->operationService->createPaginatedList(
            $request->query->getInt('page', 1),
            $user,
            $filters
        );

        return $this->render(
            'operation/index.html.twig',
            [
                'title' => '$title',
                'pagination' => $pagination,
            ]
        );
    }

    /**
     * Get filters from request.
     *
     * @param Request $request HTTP request
     *
     * @return array<string, int> Array of filters
     *
     * @psalm-return array{operation_name: string, status_id: int}
     */
    private function getFilters(Request $request): array
    {
        $filters = [];
        $filters['operation_title'] = $request->query->getAlnum('search');

        return $filters;
    }
}
