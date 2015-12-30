<?php
/*
 * Copyright (c) 2014 Eltrino LLC (http://eltrino.com)
 *
 * Licensed under the Open Software License (OSL 3.0).
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://opensource.org/licenses/osl-3.0.php
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@eltrino.com so we can send you a copy immediately.
 */
namespace Diamante\DeskBundle\Datagrid;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\DataGridBundle\Event\OrmResultAfter;
use Oro\Bundle\DataGridBundle\Event\OrmResultBefore;
use Oro\Bundle\EntityBundle\ORM\QueryHintResolver;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\QueryConverter\YamlConverter;
use Diamante\DeskBundle\Model\Audit\AuditRepository;

/**
 * Class CombinedAuditDatasource
 *
 * @package Diamante\DeskBundle\Datagrid
 */
class CombinedAuditDatasource extends AbstractDatasource
{
    const TYPE = 'diamante_combined_audit_datasource';

    /** @var  array */
    protected $config;

    /** @var QueryBuilder */
    protected $qbOroAudit;

    /** @var QueryBuilder */
    protected $qbDiamanteAudit;

    /**
     * @var AuditRepository
     */
    protected $auditRepository;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var QueryHintResolver
     */
    protected $queryHintResolver;

    /**
     * @var array
     */
    protected $queryHints;

    /**
     * @var DataGridInterface
     */
    protected $grid;

    /**
     * @param Registry $doctrineRegistry
     * @param AuditRepository $auditRepository
     * @param EventDispatcherInterface $dispatcher
     * @param QueryHintResolver $queryHintResolver
     */
    public function __construct(
        Registry $doctrineRegistry,
        AuditRepository $auditRepository,
        EventDispatcherInterface $dispatcher,
        QueryHintResolver $queryHintResolver
    ) {
        $this->doctrineRegistry     = $doctrineRegistry;
        $this->auditRepository      = $auditRepository;

        $this->qbDiamanteAudit      = $auditRepository->createQueryBuilder('a');
        $this->dispatcher           = $dispatcher;
        $this->queryHintResolver    = $queryHintResolver;
    }

    /**
     * @param DatagridInterface $grid
     * @param array             $config
     */
    public function process(DatagridInterface $grid, array $config)
    {
        $this->config = $config;
        $this->grid   = $grid;

        $queryConfig = array_intersect_key($this->config, array_flip(['query']));
        $converter = new YamlConverter();
        $this->qbOroAudit = $converter->parse($queryConfig, $this->doctrineRegistry->getManager()->createQueryBuilder('a'));

        if (isset($config['hints'])) {
            $this->queryHints = $config['hints'];
        }

        parent::process($grid, $config);
    }

    /**
     * @return ResultRecordInterface[]
     */
    public function getResults()
    {
        $audit = [];
        $rows  = [];

        /** @var $qb QueryBuilder $query */
        foreach ($this->getQueryBuilders() as $qb) {
            $query = $qb->getQuery();

            $this->queryHintResolver->resolveHints(
                $query,
                null !== $this->queryHints ? $this->queryHints : []
            );

            $beforeEvent = new OrmResultBefore($this->grid, $query);
            $this->dispatcher->dispatch(OrmResultBefore::NAME, $beforeEvent);

            $result = $beforeEvent->getQuery()->execute();

            $audit = array_merge($audit, $result);
            unset($result, $query, $beforeEvent);
        }

        $this->applySorting($audit);

        foreach ($audit as $item) {
            $rows[] = new ResultRecord($item);
        }

        $this->applyPagination($rows);

        $event = new OrmResultAfter($this->grid, $rows);
        $this->dispatcher->dispatch(OrmResultAfter::NAME, $event);

        $records = $event->getRecords();

        return $records;

    }

    /**
     * @return QueryBuilder
     */
    protected function getQbOroAudit()
    {
        return $this->qbOroAudit;
    }

    /**
     * @return QueryBuilder
     */
    protected function getQbDiamanteAudit()
    {
        return $this->qbDiamanteAudit;
    }

    /**
     * @return array
     */
    public function getQueryBuilders()
    {
        return [
            $this->getQbDiamanteAudit(),
            $this->getQbOroAudit()
        ];
    }
}
