<?php

namespace FL\GmailDoctrineBundle\Form;

use FL\GmailBundle\Form\Type\InboxType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SettingsType.
 */
class SyncSettingType extends AbstractType
{
    /**
     * @var string
     */
    private $syncSettingClass;

    /**
     * @param string $syncSettingClass
     */
    public function __construct(string $syncSettingClass)
    {
        $this->syncSettingClass = $syncSettingClass;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('userIds', InboxType::class, [
            'multiple' => true,
            'expanded' => true,
            'label' => 'Users To Sync',
        ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => $this->syncSettingClass]);
    }
}
