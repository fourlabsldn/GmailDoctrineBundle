<?php

namespace FL\GmailDoctrineBundle\Form\Type;

use FL\GmailBundle\Form\Type\InboxType;
use FL\GmailBundle\Services\Directory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SyncSettingType extends AbstractType
{
    /**
     * @var string
     */
    protected $syncSettingClass;

    /**
     * @var Directory
     */
    private $directory;

    /**
     * @param string    $syncSettingClass
     * @param Directory $directory
     */
    public function __construct(
        string $syncSettingClass,
        Directory $directory
    ) {
        $this->syncSettingClass = $syncSettingClass;
        $this->directory = $directory;
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
            'label' => 'Sync',
        ]);
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $choices = [];
            if ($event->getForm()->get('userIds')->getData()) {
                foreach ($event->getForm()->get('userIds')->getData() as $userId) {
                    $emails = $this->directory->resolveEmailsFromUserId($userId, Directory::MODE_RESOLVE_PRIMARY_ONLY);
                    if (array_key_exists(0, $emails)) {
                        $choices[$emails[0]] = $userId;
                    }
                }
            }
            $event->getForm()->add('userIdsCurrentlyFlagged', ChoiceType::class, [
                'multiple' => true,
                'expanded' => true,
                'label' => 'In inbox',
                'choices' => $choices,
            ]);
            $event->getForm()->add('userIdsAvailableAsFromAddress', ChoiceType::class, [
                'multiple' => true,
                'expanded' => true,
                'label' => 'Send from',
                'choices' => $choices,
            ]);
        });
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => $this->syncSettingClass]);
    }
}
