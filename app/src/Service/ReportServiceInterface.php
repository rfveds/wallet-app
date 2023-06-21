<?php
/**
 * Report service.
 */

namespace App\Service;

use App\Entity\Report;
use App\Entity\User;
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



}// end interface
