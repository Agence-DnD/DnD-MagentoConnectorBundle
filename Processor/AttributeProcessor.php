<?php

namespace DnD\Bundle\MagentoConnectorBundle\Processor;

use Symfony\Component\Validator\Constraints as Assert;
use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Gedmo\Sluggable\Util\Urlizer;
/**
 *
 * @author    DnD Mimosa <mimosa@dnd.fr>
 * @copyright Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface
{

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
	    $result = [];
        $result['type']                   = $item->getAttributeType();
        $result['code']                   = $item->getCode();
        $result['label-en_US']            = $item->setLocale('en_US')->getLabel();
        $result['label-fr_FR']            = $item->setLocale('fr_FR')->getLabel();
        $result['group']                  = $item->getGroup()->getCode();
        $result['unique']                 = ($item->isUnique()) ? 1 : 0;
        $result['useable_as_grid_filter'] = ($item->isUseableAsGridFilter()) ? 1 : 0;
        $result['allowed_extensions']     = '';
        $result['metric_family']          = '';
	    $result['default_metric_unit']    = '';
        $result['localizable']            = ($item->isLocalizable()) ? 1 : 0;
        $result['scopable']               = ($item->isScopable()) ? 1 : 0;
        
        if($item->getAttributeType() == 'pim_catalog_metric'){
		    $result['metric_family']       = $item->getMetricFamily();
		    $result['default_metric_unit'] = $item->getDefaultMetricUnit();
	    }
	    
	    if($item->getAttributeType() == 'pim_catalog_image'){
		    $result['allowed_extensions'] = implode(",", $item->getAllowedExtensions());
	    }
	    
	    $families = array();
        if($item->getFamilies()){
            foreach($item->getFamilies() as $family){
                array_push($families, $family->getCode());
            }
        }
	    
	    $result['families'] = implode(",", $families);
	    
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array();
    }

}
