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
     * @param int  $getInt Page number
     * @param User $user   User entity
     *
     * @return PaginationInterface Paginated list
     */
    public function createPaginatedList(int $getInt, User $user): PaginationInterface;

    /**
     * Save entity.
     *
     * @param Report $report Report entity
     *
     **/
    public function save(Report $report): void;

    /**
     * Delete entity.
     *
     * @param Report $report Report entity
     */
    public function delete(Report $report): void;

    /**
     * Get report data.
     *
     * @param array $list List
     *
     * @return array Result
     */
    public function getReportData(array $list): array;

    /**
     * Prepare filters.
     *
     * @param Report $report Report entity
     *
     * @return array Result
     */
    public function prepareFilters(Report $report): array;

    /**
     * Find by wallet.
     *
     * @param Wallet $wallet Wallet entity
     *
     * @return array Result
     */
    public function findByWallet(Wallet $wallet): array;

    /**
     * Find by user.
     *
     * @param User $user User entity
     *
     * @return array Result
     */
    public function findByUser(User $user): array;
}// end interface
