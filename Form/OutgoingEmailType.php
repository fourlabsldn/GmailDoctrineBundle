<?php

namespace FL\GmailDoctrineBundle\Form;

use FL\GmailDoctrineBundle\Form\Type\FromType;
use FL\GmailDoctrineBundle\Model\OutgoingEmail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OutgoingEmailType
 * @package FL\GmailDoctrineBundle\Form
 */
class OutgoingEmailType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('from', FromType::class, ['required'=>true])
            ->add('to', TextType::class, ['required'=>true])
            ->add('subject', TextType::class, ['required'=>true])
            ->add('threadId', TextType::class, ['required'=>false])
            ->add('bodyHtml', TextareaType::class, ['required'=>false])
            ->add('bodyPlainText', TextareaType::class, ['required'=>false])
        ;
        $builder->get('to')
            ->addModelTransformer(new CallbackTransformer(
                function ($toArray) {
                    return implode(', ', $toArray);
                },
                function ($toString) {
                    return array_map('trim', explode(',', $toString));
                }
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', OutgoingEmail::class);
    }
}
