<?php
/**
 * Operation controller.
 */

namespace App\Controller;

use App\Entity\Operation;
use App\Repository\OperationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
     * @param OperationRepository $repository Operation repository
     *
     * @return Response HTTP response
     */
    #[Route(
        name: 'operation_index',
        methods: 'GET'
    )]
    public function index(OperationRepository $repository): Response
    {
        $operations = $repository->findAll();

        return $this->render(
            'operation/index.html.twig',
            ['operations' => $operations]
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
