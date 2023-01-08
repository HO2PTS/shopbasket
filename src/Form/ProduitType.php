<?php

namespace App\Form;

use App\Entity\Produit;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('taille', ChoiceType::class, [
                'choices' => [
                    'S' => 'S',
                    'M' => 'M',
                    'L' => 'L',
                    'XL' => 'XL'
                ]
            ])
            ->add('photo', FileType::class, [
                'label' => 'Image du produit',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Enregistrez une image valide',
                    ])
                ],
            ] )
            ->add('description')
            ->add('prix')
            ->add('team',  ChoiceType::class, [
                'choices' => [
                    'Atlanta Hawks' => 'Atlanta Hawks',
                    'Boston Celtics' => 'Boston Celtics',
                    'Brooklyn Nets' => 'Brooklyn Nets',
                    'Charlotte Hornets' => 'Charlotte Hornets',
                    'Chicago Bulls' => 'Chicago Bulls',
                    'Cleveland Cavaliers' => 'Cleveland Cavaliers',
                    'Dallas Mavericks' => 'Dallas Mavericks',
                    'Denver Nuggets' => 'Denver Nuggets',
                    'Detroit Pistons' => 'Detroit Pistons',
                    'Golden State Warriors' => 'Golden State Warriors',
                    'Houston Rockets' => 'Houston Rockets',
                    'Indiana Pacers' => 'Indiana Pacers',
                    'Los Angeles Clippers' => 'Los Angeles Clippers',
                    'Los Angeles Lakerst' => 'Los Angeles Lakers',
                    'Memphis Grizzlies' => 'Memphis Grizzlies',
                    'Miami Heat' => 'Miami Heat',
                    'Milwaukee Bucks' => 'Milwaukee Bucks',
                    'Minnesota Timberwolves' => 'Minnesota Timberwolves',
                    'New Orleans Pelicans' => 'New Orleans Pelicans',
                    'New York Knicks' => 'New York Knicks',
                    'Oklahoma City Thunder' => 'Oklahoma City Thunder',
                    'Orlando Magic' => 'Orlando Magic',
                    'Philadelphia 76ers' => 'Philadelphia 76ers',
                    'Phoenix Suns' => 'Phoenix Suns',
                    'Portland Trail Blazers' => 'Portland Trail Blazers',
                    'Sacramento Kings' => 'Sacramento Kings',
                    'San Antonio Spurs' => 'San Antonio Spurs',
                    'Toronto Raptors' => 'Toronto Raptors',
                    'Utah Jazz' => 'Utah Jazz',
                    'Washington Wizards' => 'Washington Wizards',
                ]
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
