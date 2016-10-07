<?php

namespace FL\GmailDoctrineBundle\Form;

use FL\GmailDoctrineBundle\Form\Type\FromType;
use FL\GmailDoctrineBundle\Model\SendEmail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SendEmailType
 * @package FL\GmailDoctrineBundle\Form\Type
 */
class SendEmailType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('from', FromType::class, ['required'=>false])
            ->add('to', TextType::class, ['required'=>false])
            ->add('subject', TextType::class, ['required'=>false])
            ->add('body_html', TextareaType::class, ['required'=>false])
            ->add('body_plain_text', TextareaType::class, ['required'=>false])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => SendEmail::class, ]);
    }
}
