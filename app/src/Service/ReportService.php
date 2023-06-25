<?php
/**
 * Report service.
 */

namespace App\Service;

use App\Entity\Report;
use App\Entity\User;
use App\Entity\Wallet;
use App\Repository\OperationRepository;
use App\Repository\ReportRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class ReportService.
 */
class ReportService implements ReportServiceInterface
{
    /**
     * Report repository.
     */
    private ReportRepository $reportRepository;

    /**
     * Paginator.
     */
    private PaginatorInterface $paginator;

    /**
     * Operation repository.
     */
    private OperationRepository $operationRepository;

    /**
     * ReportService constructor.
     *
     * @param ReportRepository    $reportRepository    Report repository
     * @param OperationRepository $operationRepository Operation repository
     * @param PaginatorInterface  $paginator           Paginator
     */
    public function __construct(ReportRepository $reportRepository, PaginatorInterface $paginator, OperationRepository $operationRepository)
    {
        $this->reportRepository = $reportRepository;
        $this->operationRepository = $operationRepository;
        $this->paginator = $paginator;
    }// end __construct()

    /**
     * Create paginated list.
     */
    public function createPaginatedList(int $getInt, User $user): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->reportRepository->queryByAuthor($user),
            $getInt,
            ReportRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }// end createPaginatedList()

    /**
     * Save report.
     *
     * @param Report $report Report entity
     */
    public function save(Report $report): void
    {
        $this->reportRepository->save($report, true);
    }// end save()

    /**
     * Delete report.
     *
     * @param Report $report Report entity
     */
    public function delete(Report $report): void
    {
        $this->reportRepository->remove($report, true);
    }// end delete()

    /**
     * Prepare data in JSON format for report.
     */
    public function getReportData(array $list): array
    {
        $amountData = [];
        foreach ($list as $operation) {
            $amountData[] = (float) $operation->getAmount();
        }

        $balanceData = [];
        foreach ($list as $operation) {
            $balanceData[] = (float) $operation->getWallet()->getBalance();
        }

        $balanceHistoryData = [];
        foreach ($list as $operation) {
            $balanceHistoryData[] = (float) $operation->getCurrentBalance();
        }

        $labelData = [];
        foreach ($list as $operation) {
            $labelData[] = $operation->getCreatedAt()->format('Y-m-d');
        }

        $amountDataJSON = json_encode($amountData);
        $labelDataJSON = json_encode($labelData);
        $balanceDataJSON = json_encode($balanceData);
        $balanceHistoryDataJSON = json_encode($balanceHistoryData);

        return [
            'amountDataJSON' => $amountDataJSON,
            'labelDataJSON' => $labelDataJSON,
            'balanceDataJSON' => $balanceDataJSON,
            'balanceHistoryDataJSON' => $balanceHistoryDataJSON,
        ];
    }// end getReportData()

    /**
     * Prepare filters for report.
     *
     * @param Report $report Report entity
     */
    public function prepareFilters(Report $report): array
    {
        $filters = [];

        if (null != $report->getCategory()) {
            $filters['category_id'] = $report->getCategory()->getId();
        }

        if (null != $report->getTag()) {
            $filters['tag_id'] = $report->getTag()->getId();
        }

        if (null != $report->getWallet()) {
            $filters['wallet_id'] = $report->getWallet()->getId();
        }

        if (null != $report->getAuthor()) {
            $filters['author_id'] = $report->getAuthor()->getId();
        }

        if (null != $report->getDateFrom()) {
            // format with datetime
            $filters['operation_date_from'] = $report->getDateFrom()->format('Y-m-d');
        }

        if (null != $report->getDateTo()) {
            $filters['operation_date_to'] = $report->getDateTo()->format('Y-m-d');
        }

        return $filters;
    }// end prepareFilters()

    /**
     * Find by wallet.
     *
     * @param Wallet $wallet Wallet entity
     *
     * @return Report|null Report entity
     */
    public function findByWallet(Wallet $wallet): ?Report
    {
        return $this->reportRepository->findByWallet($wallet);
    }
}// end class
