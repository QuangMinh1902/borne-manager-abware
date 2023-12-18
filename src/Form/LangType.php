<?php

namespace App\Form;

use App\Entity\Lang;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class LangType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('code', TextType::class, [
            "attr" => [
                "class" => "form-control",
                "minlength" => '2',
                "maxlength" => "2"
            ],
            "label" => "Code",
            "label_attr" => [
                "class" => "form-label mt-4",
            ]
        ])
        ->add('lang', TextType::class, [
            "attr" => [
                "class" => "form-control",
                "minlength" => '2',
                "maxlength" => "45"
            ],
            "label" => "Lang",
            "label_attr" => [
                "class" => "form-label mt-4",
            ]
        ])
        ->add('submit', SubmitType::class, [
            "attr" => [
                "class" => "btn btn-primary mt-4",
            ],
            "label" => "Créer",
        ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lang::class,
        ]);
    }
}
