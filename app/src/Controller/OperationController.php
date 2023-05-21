<?php
/**
 * Operation controller.
 */

namespace App\Controller;

use App\Entity\Operation;
use App\Repository\OperationRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class OperationController.
 */
#[Route('/operation')]
class OperationController extends AbstractController
{
    /**
     * Index action.
     *
     * @param Request             $request              HTTP request
     * @param OperationRepository $operationRepository  Operation repository
     * @param PaginatorInterface  $paginator            Paginator
     *
     * @return Response HTTP response
     */
    #[Route(
        name: 'operation_index',
        methods: 'GET'
    )]
    public function index(Request $request, OperationRepository $operationRepository, PaginatorInterface $paginator): Response
    {
        $pagination = $paginator->paginate(
            $operationRepository->queryAll(),
            $request->query->getInt('page', 1),
            OperationRepository::PAGINATOR_ITEMS_PER_PAGE
        );

        return $this->render(
            'operation/index.html.twig',
            ['pagination' => $pagination]
        );
    }

    /**
     * Show action.
     *
     * @param Operation $operation Operation entity
     *
     * @return Response HTTP response
     *
     */
    #[Route(
        '/{id}',
        name: 'operation_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET'
    )]
    public function show(Operation $operation): Response
    {
        return $this->render(
            'operation/show.html.twig',
            ['operation' => $operation]
        );
    }
}
