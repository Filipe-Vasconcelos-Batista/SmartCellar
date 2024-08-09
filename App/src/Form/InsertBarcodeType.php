<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InsertBarcodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('photo', FileType::class, [
            'label'=>false,
            'mapped' => false,
            'multiple'=>true,
            'attr' => [
                'class'=>'form-control-file btn btn-success',
                'accept' => 'image/*',
                'style' => 'display:none;',
                ],
            ])
            ->add('barcode' ,TextType::class,[
                'label'=>false,
                'mapped'=>false,
                'attr'=>[
                    'class'=>'form-control',
                ],
            ] );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
