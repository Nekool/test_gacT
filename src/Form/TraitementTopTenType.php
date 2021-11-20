<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;


class TraitementTopTenType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
//        AJOUTER POSSIBILITE DE CHOISIR LA TRANCHE D'HEURE?
        $builder
            ->add('send', SubmitType::class);
    }
}