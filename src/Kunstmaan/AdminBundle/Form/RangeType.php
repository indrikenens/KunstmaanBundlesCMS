<?php
/**
 * Created by Kunstmaan.
 * Date: 20/06/14
 * Time: 15:25
 */

namespace Kunstmaan\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * HTML5 range type field
 */
class RangeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array('attr' => array('min' => 0, 'max' => 100, 'step' => 1)));
    }

    // BC for SF < 2.7
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }

    /**
     * Get parent
     *
     * @return string
     */
    public function getParent()
    {
        return 'integer';
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return 'range';
    }
}
