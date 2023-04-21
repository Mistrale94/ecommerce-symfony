<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('fullName'),
            ChoiceField::new('roles')->allowMultipleChoices()->setChoices([
                'User' => 'ROLE_USER',
                'Admin' => 'ROLE_ADMIN',
                'Super Admin' => 'ROLE_SUPER_ADMIN',
            ]),
        ];
    }
}
