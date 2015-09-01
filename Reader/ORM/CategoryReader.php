<?php

namespace DnD\Bundle\MagentoConnectorBundle\Reader\ORM;

use Doctrine\ORM\EntityRepository;
use Pim\Bundle\BaseConnectorBundle\Reader\Doctrine\Reader;

/**
 *
 * @author    DnD Mimosa <mimosa@dnd.fr>
 * @copyright Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryReader extends Reader
{
    /**
     * @var EntityRepository
     */
    protected $categoryRepository;

    /**
     * @var string
     */
    protected $excludedCategories;

    /**
     * @var array
     */
    protected $expression;

    /**
     * @var int
     */
    protected $categoriesNumber;

    /**
     * @param EntityRepository $categoryRepository
     */
    public function __construct(EntityRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
        $this->expression         = array();
        $this->categoriesNumber   = 0;
    }

    /**
     * get excludedCategories
     *
     * @return string excludedCategories
     */
    public function getExcludedCategories()
    {
        return $this->excludedCategories;
    }

    /**
     * Set excludedCategories
     *
     * @param string $excludedCategories excludedCategories
     *
     * @return AbstractProcessor
     */
    public function setExcludedCategories($excludedCategories)
    {
        $this->excludedCategories = $excludedCategories;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        if (!$this->query) {
            $qb = $this->categoryRepository->createQueryBuilder('c');
            if($this->getExcludedCategories() != ''){
                $categories = explode(',', $this->getExcludedCategories());
                foreach($categories as $cat){
                    if($this->categoriesNumber == 0){
                        $qb->where(
                            $qb->expr()->orX(
                                $qb->expr()->neq('c.code', ':code'. $this->categoriesNumber)
                            )
                        );
                        $qb->setParameter('code'.$this->categoriesNumber, $cat);
                    }else{
                        $qb->andWhere(
                            $qb->expr()->orX(
                                $qb->expr()->neq('c.code', ':code'.$this->categoriesNumber)
                            )
                        );
                        $qb->setParameter('code'.$this->categoriesNumber, $cat);
                    }
                    $this->categoriesNumber++;
                    $this->recursiveCategories($cat);
                }
                if(!empty($this->expression)){
                    foreach ($this->expression as $key => $value) {
                        $qb->andWhere(
                            $qb->expr()->orX(
                                $qb->expr()->neq('c.code', ':code'.$key)
                            )
                        );
                        $qb->setParameter('code'.$key, $value);
                    }
                }
            }
            $qb
                ->orderBy('c.root')
                ->addOrderBy('c.left');
            $this->query = $qb->getQuery();


        }

        return $this->query;
    }

    /**
     * Remove categories children recursively
     */
    protected function recursiveCategories($cat)
    {
        $children = $this->getCategoryChildren($cat);
        if($children == NULL){
            return;
        }else{
            foreach($children as $child){
                $this->categoriesNumber++;
                $this->expression[$this->categoriesNumber] = $child["code"];
                $this->recursiveCategories($child["code"]);
            }
        }
    }

    /**
     * Get all children of a category by its code
     */
    protected function getCategoryChildren($categoryCode)
    {
        $categoryId = $this->getCategoryId($categoryCode);
        if($categoryId == NULL)
            return null;
        $qb = $this->categoryRepository->createQueryBuilder('c');
        $qb ->select('c.code')
            ->where(
                $qb->expr()->orX(
                    $qb->expr()->eq('c.parent', ':parent')
                )
            )
            ->setParameter('parent', $categoryId['id']);
        return $qb->getQuery()->getResult();
    }

    /**
     * Get category ID by its code
     */
    protected function getCategoryId($categoryCode)
    {
        $qb = $this->categoryRepository->createQueryBuilder('c');
        $qb ->select('c.id')
            ->where(
                $qb->expr()->orX(
                    $qb->expr()->eq('c.code', ':code')
                )
            )
            ->setParameter('code', $categoryCode);
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array(
            'excludedCategories' => array(
                'options' => array(
                    'required' => false,
                    'label'    => 'dnd_magento_connector.export.excludedCategories.label',
                    'help'     => 'dnd_magento_connector.export.excludedCategories.help'
                )
            )
        );
    }
}
