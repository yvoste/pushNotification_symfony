<?php

// src/DUESI/PlatformBundle/Form/ImageType.php

namespace DEUSI\PlatformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImageType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      ->add('file', FileType::class)    
      
    ;    
  }
  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults(['data_class' => 'DEUSI\PlatformBundle\Entity\Image']);
  }
}

