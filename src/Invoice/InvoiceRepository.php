<?php

namespace Smart\Sage50\Invoice;

use Exception;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Smart\Sage50\RepositoryInterface;

class InvoiceRepository extends EntityRepository implements RepositoryInterface
{
    /**
     * @param DateTime $dateTime
     * @throws Exception
     */
    public function fetchAllSince(DateTime $dateTime)
    {
	    throw new Exception('Invoices do not support fetch all since');
    }

	/**
	 * @param int $id
	 * @return InvoiceEntity[]
	 */
    public function fetchNewAfterId($id)
	{
		return $this->createQueryBuilder('i')
		            ->where('i.id > :id')
		            ->setParameter('id', $id)
		            ->getQuery()
		            ->getResult();
	}
}