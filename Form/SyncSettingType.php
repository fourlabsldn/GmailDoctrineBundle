<?php

namespace FL\GmailDoctrineBundle\Form;

use FL\GmailDoctrineBundle\Entity\SyncSetting;
use FL\GmailBundle\Form\Type\InboxType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SettingsType
 * @package FL\GmailDoctrineBundle\Form\Type
 */
class SyncSettingType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('userIds', InboxType::class, [
            'multiple'=>true,
            'expanded'=>true,
        ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => SyncSetting::class, ]);
    }
}
