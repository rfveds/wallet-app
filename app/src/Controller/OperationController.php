<?php
/**
 * Operation controller.
 */

namespace App\Controller;

use App\Entity\Operation;
use App\Entity\User;
use App\Form\Type\OperationType;
use App\Service\OperationServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

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
     * Translator.
     */
    private TranslatorInterface $translator;

    /**
     * OperationController constructor.
     *
     * @param OperationServiceInterface $operationService Operation service
     * @param TranslatorInterface       $translator       Translator
     */
    public function __construct(OperationServiceInterface $operationService, TranslatorInterface $translator)
    {
        $this->operationService = $operationService;
        $this->translator = $translator;
    }// end __construct()

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
        $filters = $this->getFilters($request);
        //var_dump($filters);
        /** @var User $user */
        $user = $this->getUser();
        $pagination = $this->operationService->createPaginatedList(
            $request->query->getInt('page', 1),
            $user,
            $filters
        );

        return $this->render(
            'operation/index.html.twig',
            ['pagination' => $pagination]
        );
    }// end index()

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
    #[IsGranted(
        'VIEW',
        subject: 'operation'
    )]
    public function show(Operation $operation): Response
    {
        return $this->render(
            'operation/show.html.twig',
            ['operation' => $operation]
        );
    }// end show()

    /**
     * Create action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     *
     * @throws \Exception
     */
    #[Route(
        '/create',
        name: 'operation_create',
        methods: 'GET|POST'
    )]
    public function create(Request $request): Response
    {
        /*
            @var User $user
        */
        $user = $this->getUser();
        $operation = new Operation();
        $operation->setAuthor($user);
        $form = $this->createForm(
            OperationType::class,
            $operation,
            [
                'action' => $this->generateUrl('operation_create'),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $wallet = $form->get('wallet')->getData();
            $amount = $form->get('amount')->getData();
            $balance = $wallet->getBalance();
            if (($balance + $amount) < $balance) {
                $this->addFlash(
                    'warning',
                    'message.insufficient_funds'
                );
            } else {
                $wallet->setBalance($balance + $amount);
                $this->operationService->save($operation);

                $this->addFlash(
                    'success',
                    $this->translator->trans('message.created_successfully')
                );
            }

            return $this->redirectToRoute('operation_index');
        }// end if

        return $this->render(
            'operation/create.html.twig',
            ['form' => $form->createView()]
        );
    }// end create()

    /**
     * Edit action.
     *
     * @param Request   $request   HTTP request
     * @param Operation $operation Operation entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/edit',
        name: 'operation_edit',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|PUT'
    )]
    #[IsGranted(
        'EDIT',
        subject: 'operation'
    )]
    public function edit(Request $request, Operation $operation): Response
    {
        $form = $this->createForm(
            OperationType::class,
            $operation,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl(
                    'operation_edit',
                    ['id' => $operation->getId()]
                ),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->operationService->save($operation);

            $this->addFlash(
                'success',
                $this->translator->trans('message.updated_successfully')
            );

            return $this->redirectToRoute('operation_index');
        }

        return $this->render(
            'operation/edit.html.twig',
            [
                'form' => $form->createView(),
                'operation' => $operation,
            ]
        );
    }// end edit()

    /**
     * Delete action.
     *
     * @param Request   $request   HTTP request
     * @param Operation $operation Operation entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/delete',
        name: 'operation_delete',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|DELETE'
    )]
    #[IsGranted(
        'DELETE',
        subject: 'operation'
    )]
    public function delete(Request $request, Operation $operation): Response
    {
        $form = $this->createForm(
            FormType::class,
            $operation,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl(
                    'operation_delete',
                    ['id' => $operation->getId()]
                ),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->operationService->delete($operation);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('operation_index');
        }

        return $this->render(
            'operation/delete.html.twig',
            [
                'form' => $form->createView(),
                'operation' => $operation,
            ]
        );
    }// end delete()

    /**
     * Get filters from request.
     *
     * @param Request $request HTTP request
     *
     * @return array<string, int> Array of filters
     *
     * @psalm-return array{category_id: int, tag_id: int, status_id: int}
     */
    private function getFilters(Request $request): array
    {
        $filters = [];
        $filters['category_id'] = $request->query->getInt('filters_category_id');
        $filters['tag_id'] = $request->query->getInt('filters_tag_id');
        $filters['operation_title'] = $request->query->getAlnum('filters_operation_title');
        $filters['operation_date_from'] = $request->query->getAlnum('filters_operation_date_from');
        $filters['operation_date_to'] = $request->query->getAlnum('filters_operation_date_to');

        return $filters;
    }// end getFilters()
}// end class
