<?php

namespace App\Service;

use App\Entity\Report;
use App\Entity\User;
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
     * ReportService constructor.
     *
     * @param ReportRepository   $reportRepository Report repository
     * @param PaginatorInterface $paginator        Paginator
     */
    public function __construct(ReportRepository $reportRepository, PaginatorInterface $paginator)
    {
        $this->reportRepository = $reportRepository;
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
