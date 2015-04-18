<?php

namespace DnD\Bundle\MagentoConnectorBundle\Reader\Doctrine;

use Pim\Bundle\BaseConnectorBundle\Reader\Doctrine\ORMProductReader as BaseORMProductReader;

/**
 * Override of the product reader to add new options (updated time condition,
 * complete or not products, etc...)
 *
 * @author    DnD Mimosa <mimosa@dnd.fr>
 * @copyright Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ORMProductReader extends BaseORMProductReader
{
    /**
     * @var string
     */
    protected $exportFrom = '1970-01-01 01:00:00';

    /**
     * @var boolean
     */
    protected $isEnabled = true;

    /**
     * @var boolean
     */
    protected $isComplete = true;

    /**
     * Get exportFrom
     *
     * @return string exportFrom
     */
    public function getExportFrom()
    {
        return $this->exportFrom;
    }

    /**
     * Set exportFrom
     *
     * @param string $exportFrom exportFrom
     *
     * @return AbstractProcessor
     */
    public function setExportFrom($exportFrom)
    {
        $this->exportFrom = $exportFrom;

        return $this;
    }

    /**
     * get isEnabled
     *
     * @return boolean isEnabled
     */
    public function getIsEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * Set isEnabled
     *
     * @param string isEnabled $isEnabled
     *
     * @return AbstractProcessor
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    /**
     * get isComplete
     *
     * @return boolean isComplete
     */
    public function getIsComplete()
    {
        return $this->isComplete;
    }

    /**
     * Set isComplete
     *
     * @param string isComplete $isComplete
     *
     * @return AbstractProcessor
     */
    public function setIsComplete($isComplete)
    {
        $this->isComplete = $isComplete;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array_merge(
            parent::getConfigurationFields(),
            [
                'exportFrom' => [
                    'required' => false,
                    'options' => [
                        'help'    => 'dnd_magento_connector.export.exportFrom.help',
                        'label'   => 'dnd_magento_connector.export.exportFrom.label',
                    ]
                ],
                'isEnabled' => [
                    'type'    => 'switch',
                    'required' => false,
                    'options' => [
                        'help'    => 'dnd_magento_connector.export.isEnabled.help',
                        'label'   => 'dnd_magento_connector.export.isEnabled.label',
                    ]
                ],
                'isComplete' => [
                    'type'    => 'switch',
                    'required' => false,
                    'options' => [
                        'help'    => 'dnd_magento_connector.export.isComplete.help',
                        'label'   => 'dnd_magento_connector.export.isComplete.label',
                    ]
                ]
            ]
        );
    }

    /**
     * Get ids of products which are completes and in channel
     *
     * @return array
     */
    protected function getIds()
    {
        if (!is_object($this->channel)) {
            $this->channel = $this->channelManager->getChannelByCode($this->channel);
        }

        if ($this->missingCompleteness) {
            $this->completenessManager->generateMissingForChannel($this->channel);
        }

        $this->query = $this->DnDBuildByChannelAndCompleteness($this->channel, $this->getIsComplete());

        $rootAlias = current($this->query->getRootAliases());
        $rootIdExpr = sprintf('%s.id', $rootAlias);

        $from = current($this->query->getDQLPart('from'));

        $this->query
            ->select($rootIdExpr)
            ->resetDQLPart('from')
            ->from($from->getFrom(), $from->getAlias(), $rootIdExpr)
            ->andWhere(
                $this->query->expr()->orX(
                    $this->query->expr()->gte($from->getAlias() . '.updated', ':updated')
                )
            )
            ->setParameter('updated', $this->getDateFilter())
            ->setParameter('enabled', $this->getIsEnabled())
            ->groupBy($rootIdExpr);
        $results = $this->query->getQuery()->getArrayResult();

        return array_keys($results);
    }

    /**
     * Get product collection by channel and completness
     */
    protected function DnDBuildByChannelAndCompleteness($channel, $isComplete){
        $scope = $channel->getCode();

        $qb = $this->repository->buildByScope($scope);

        $rootAlias = $qb->getRootAlias();

        $complete = ($isComplete) ? $qb->expr()->eq('pCompleteness.ratio', '100') : $qb->expr()->lt('pCompleteness.ratio', '100');
        $expression =
            'pCompleteness.product = '.$rootAlias.' AND '.
            $complete.' AND '.
            $qb->expr()->eq('pCompleteness.channel', $channel->getId());

        $rootEntity          = current($qb->getRootEntities());
        $completenessMapping = $this->entityManager->getClassMetadata($rootEntity)
            ->getAssociationMapping('completenesses');
        $completenessClass   = $completenessMapping['targetEntity'];
        $qb->innerJoin(
            $completenessClass,
            'pCompleteness',
            'WITH',
            $expression
        );

        $treeId = $channel->getCategory()->getId();
        $expression = $qb->expr()->eq('pCategory.root', $treeId);
        $qb->innerJoin(
            $rootAlias.'.categories',
            'pCategory',
            'WITH',
            $expression
        );


        return $qb;
    }

    /**
     * Get the date use to filter the product collection
     *
     * @return string
     */
    protected function getDateFilter()
    {
        if (!empty($this->exportFrom)) {
            return $this->exportFrom;
        }

        $query = $this->entityManager->createQuery(
            "SELECT MAX(je.endTime) FROM Akeneo\Bundle\BatchBundle\Entity\JobExecution je WHERE je.jobInstance = :jobInstance"
        );

        $query->setParameter('jobInstance', $this->stepExecution->getJobExecution()->getJobInstance());

        $lastExecutionDate = $query->getOneOrNullResult();

        $date = (isset($lastExecutionDate[1]) && $lastExecutionDate[1] != null) ? $lastExecutionDate[1] : '1970-01-01 01:00:00';

        return $date;
    }
}
