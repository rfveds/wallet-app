<?php
/**
 * Tag controller.
 */

namespace App\Controller;

use App\Entity\Report;
use App\Entity\User;
use App\Form\Type\ReportType;
use App\Service\OperationServiceInterface;
use App\Service\ReportServiceInterface;
use Doctrine\ORM\NonUniqueResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ReportController.
 */
#[Route('/report')]
class ReportController extends AbstractController
{
    /**
     * Report service.
     */
    private ReportServiceInterface $reportService;

    /**
     * Operation service.
     */
    private OperationServiceInterface $operationService;

    /**
     * Translator.
     */
    private TranslatorInterface $translator;

    /**
     * ReportController constructor.
     *
     * @param ReportServiceInterface    $reportService    Report service interface
     * @param OperationServiceInterface $operationService Operation service interface
     * @param TranslatorInterface       $translator       Translator interface
     */
    public function __construct(ReportServiceInterface $reportService, OperationServiceInterface $operationService, TranslatorInterface $translator)
    {
        $this->reportService = $reportService;
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
        name: 'report_index',
        methods: 'GET',
    )]
    public function index(Request $request): Response
    {
        /*
            @var User $user
        */
        $user = $this->getUser();

        $pagination = $this->reportService->createPaginatedList(
            $request->query->getInt('page', 1),
            $user,
        );

        return $this->render(
            'report/index.html.twig',
            ['pagination' => $pagination],
        );
    }// end index()

    /**
     * Show action.
     *
     * @param Request $request HTTP request
     * @param Report  $report  Report entity
     *
     * @return Response HTTP response
     *
     * @throws NonUniqueResultException
     */
    #[Route(
        '/{id}',
        name: 'report_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET',
    )]
    #[IsGranted(
        'VIEW',
        subject: 'report',
    )]
    public function show(Request $request, Report $report): Response
    {
        $filters = $this->reportService->prepareFilters($report);

        /*
            @var User $user
        */
        $user = $this->getUser();
        $list = $this->operationService->createList(
            $user,
            $filters
        );

        $pagination = $this->operationService->createPaginatedList(
            $request->query->getInt('page', 1),
            $user,
            $filters
        );

        $data = $this->reportService->getReportData($list);

//        var_dump($data['balanceDataJSON']);
//        echo '<br>';
//        var_dump($data['balanceHistoryDataJSON']);
//        echo '<br>';
//        var_dump($data['labelDataJSON']);

        return $this->render(
            'report/show.html.twig',
            [
                'report' => $report,
                'list' => $list,
                'pagination' => $pagination,
                'amountDataJSON' => $data['amountDataJSON'],
                'labelDataJSON' => $data['labelDataJSON'],
                'balanceDataJSON' => $data['balanceDataJSON'],
                'balanceHistoryDataJSON' => $data['balanceHistoryDataJSON'],
            ],
        );
    }// end show()

    /**
     * Create action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route(
        '/create',
        name: 'report_create',
        methods: 'GET|POST',
    )]
    public function create(Request $request): Response
    {
        /*
            @var User $user
        */
        $user = $this->getUser();
        $report = new Report();
        $report->setAuthor($user);
        $form = $this->createForm(ReportType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (null !== $report->getWallet() ||
                null !== $report->getCategory() ||
                null !== $report->getTag() ||
                null !== $report->getDateTo() ||
                null !== $report->getDateFrom()) {
                $this->reportService->save($report);
                $this->addFlash(
                    'success',
                    $this->translator->trans('message.created_successfully')
                );

                return $this->redirectToRoute('report_index');
            }

            $this->addFlash(
                'warning',
                $this->translator->trans('message.select_at_least_one_criteria')
            );
        }

        return $this->render(
            'report/create.html.twig',
            ['form' => $form->createView()],
        );
    }// end create()

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param Report  $report  Report entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/edit',
        name: 'report_edit',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET|PUT']
    )]
    #[isGranted(
        'EDIT',
        subject: 'report'
    )]
    public function edit(Request $request, Report $report): Response
    {
        $form = $this->createForm(
            ReportType::class,
            $report,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl(
                    'report_edit',
                    ['id' => $report->getId()]
                ),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->reportService->save($report);

            $this->addFlash(
                'success',
                $this->translator->trans('message.updated_successfully')
            );

            return $this->redirectToRoute('report_index');
        }

        return $this->render(
            'report/edit.html.twig',
            [
                'report' => $report,
                'form' => $form->createView(),
            ],
        );
    }// end edit()

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param Report  $report  Report entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/delete',
        name: 'report_delete',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|DELETE'
    )]
    #[isGranted(
        'DELETE',
        subject: 'report'
    )]
    public function delete(Request $request, Report $report): Response
    {
        $form = $this->createForm(
            FormType::class,
            $report,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl(
                    'report_delete',
                    ['id' => $report->getId()]
                ),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->reportService->delete($report);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('report_index');
        }

        return $this->render(
            'report/delete.html.twig',
            [
                'form' => $form->createView(),
                'report' => $report,
            ],
        );
    }// end delete()
}// end class
