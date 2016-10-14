<?php

namespace FL\GmailDoctrineBundle\Form;

use FL\GmailDoctrineBundle\Model\ThreadCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ThreadCollectionType
 * @package FL\GmailDoctrineBundle\Form
 */
class ThreadCollectionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choicesCollection = $builder->getForm()->getConfig()->getOption('threads');
        $choicesArray = [];
        foreach ($choicesCollection as $thread) {
            $choicesArray[] = $thread;
        }
        $builder
            ->add('threads', ChoiceType::class, [
                'choices' => [],
                'required'=>true,
                'multiple'=>true,
                'expanded'=> true,
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(['data_class' => ThreadCollection::class])
            ->setRequired('threads')
            ->setAllowedTypes('threads', ThreadCollection::class)
        ;
    }
}
