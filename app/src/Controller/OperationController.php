<?php
/**
 * wallet-app.
 *
 * (c) Karol Kijowski , 2023
 */

namespace App\Controller;

use App\Entity\Operation;
use App\Service\OperationServiceInterface;
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
     * Operation service.
     */
    private OperationServiceInterface $operationService;

    /**
     * OperationController constructor.
     *
     * @param OperationServiceInterface $operationService Operation service interface
     */
    public function __construct(OperationServiceInterface $operationService)
    {
        $this->operationService = $operationService;
    }

    /**
     * Index action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route(
        name: 'operation_index',
        methods: 'GET'
    )]
    public function index(Request $request): Response
    {
        $pagination = $this->operationService->createPaginatedList(
            $request->query->getInt('page', 1)
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
