<?php
/**
 * Report service.
 */

namespace App\Service;

use App\Entity\Report;
use App\Entity\User;
use App\Entity\Wallet;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface ReportServiceInterface.
 */
interface ReportServiceInterface
{
    /**
     * Create paginated list.
     *
     * @return PaginationInterface Paginated list
     */
    public function createPaginatedList(int $getInt, User $user): PaginationInterface;

    /**
     * Save entity.
     *
     * @param Report $report Report entity
     **/
    public function save(Report $report);

    /**
     * Delete entity.
     *
     * @param Report $report Report entity
     */
    public function delete(Report $report);

    public function getReportData(array $list);

    /**
     * Prepare filters.
     *
     * @param Report $report Report entity
     */
    public function prepareFilters(Report $report);

    /**
     * Find by wallet.
     *
     * @param Wallet $wallet Wallet entity
     *
     * @return Report|null Report entity
     */
    public function findByWallet(Wallet $wallet);


}// end interface
