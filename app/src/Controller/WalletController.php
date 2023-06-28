<?php
/**
 * Tag controller.
 */

namespace App\Controller;

use App\Entity\Operation;
use App\Entity\User;
use App\Entity\Wallet;
use App\Form\Type\WalletType;
use App\Service\OperationServiceInterface;
use App\Service\ReportServiceInterface;
use App\Service\WalletServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class WalletController.
 */
#[Route('/wallet')]
class WalletController extends AbstractController
{
    /**
     * Wallet service.
     */
    private WalletServiceInterface $walletService;

    /**
     * Operation service.
     */
    private OperationServiceInterface $operationService;

    /**
     * Translator.
     */
    private TranslatorInterface $translator;

    /**
     * Report service.
     */
    private ReportServiceInterface $reportService;

    /**
     * WalletController constructor.
     *
     * @param WalletServiceInterface    $walletService    Wallet service interface
     * @param OperationServiceInterface $operationService Operation service interface
     * @param TranslatorInterface       $translator       Translator interface
     * @param ReportServiceInterface    $reportService    Report service interface
     */
    public function __construct(WalletServiceInterface $walletService, OperationServiceInterface $operationService, TranslatorInterface $translator, ReportServiceInterface $reportService)
    {
        $this->walletService = $walletService;
        $this->operationService = $operationService;
        $this->translator = $translator;
        $this->reportService = $reportService;
    }

    /**
     * Index action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route(
        name: 'wallet_index',
        methods: 'GET',
    )]
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $pagination = $this->walletService->createPaginatedList(
            $request->query->getInt('page', 1),
            $user,
        );

        return $this->render(
            'wallet/index.html.twig',
            ['pagination' => $pagination],
        );
    }

    /**
     * Show action.
     *
     * @param Wallet $wallet Wallet entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'wallet_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET',
    )]
    public function show(Wallet $wallet): Response
    {
        return $this->render(
            'wallet/show.html.twig',
            ['wallet' => $wallet],
        );
    }

    /**
     * Create action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route(
        '/create',
        name: 'wallet_create',
        methods: 'GET|POST',
    )]
    public function create(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $wallet = new Wallet();
        $wallet->setUser($user);
        $form = $this->createForm(WalletType::class, $wallet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->getData()->getBalance() < 0) {
                $this->addFlash(
                    'warning',
                    $this->translator->trans('message.balance_cannot_be_negative')
                );

                return $this->redirectToRoute('wallet_index');
            }

            $this->walletService->save($wallet);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('wallet_index');
        }

        return $this->render(
            'wallet/create.html.twig',
            ['form' => $form->createView()],
        );
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param Wallet  $wallet  Wallet entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/edit',
        name: 'wallet_edit',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET|PUT']
    )]
    #[isGranted('EDIT', subject: 'wallet')]
    public function edit(Request $request, Wallet $wallet): Response
    {
        $form = $this->createForm(
            WalletType::class,
            $wallet,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl(
                    'wallet_edit',
                    ['id' => $wallet->getId()]
                ),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->walletService->save($wallet);

            $this->addFlash(
                'success',
                $this->translator->trans('message.updated_successfully')
            );

            return $this->redirectToRoute('wallet_index');
        }

        return $this->render(
            'wallet/edit.html.twig',
            [
                'wallet' => $wallet,
                'form' => $form->createView(),
            ],
        );
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param Wallet  $wallet  Wallet entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/delete',
        name: 'wallet_delete',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|DELETE'
    )]
    public function delete(Request $request, Wallet $wallet): Response
    {
        $form = $this->createForm(
            FormType::class,
            $wallet,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl(
                    'wallet_delete',
                    ['id' => $wallet->getId()]
                ),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $operations = $this->operationService->findByWallet($wallet);
            foreach ($operations as $operation) {
                $this->operationService->delete($operation);
            }

            $reports = $this->reportService->findByWallet($wallet);
            foreach ($reports as $report) {
                $this->reportService->delete($report);
            }

            $this->walletService->delete($wallet);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('wallet_index');
        }

        return $this->render(
            'wallet/delete.html.twig',
            [
                'form' => $form->createView(),
                'wallet' => $wallet,
            ],
        );
    }
}
