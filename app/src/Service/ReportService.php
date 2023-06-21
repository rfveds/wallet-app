<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Report;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Wallet;
use App\Repository\OperationRepository;
use App\Repository\ReportRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class ReportService implements ReportServiceInterface
{
    private $reportRepository;

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
}// end class
