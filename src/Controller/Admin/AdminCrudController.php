<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

abstract class AdminCrudController extends AbstractCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDateFormat('dd/MM/yyyy')
            ->setTimeFormat('HH:mm:ss')
            ->setDateIntervalFormat('%%y Year(s) %%m Month(s) %%d Day(s)')
            ->setNumberFormat('%.2d')
            ->setAutofocusSearch()
            ->setPaginatorPageSize(10)
            ->setPaginatorRangeSize(5)
            ->setPaginatorUseOutputWalkers(true)
            ->setPaginatorFetchJoinCollection(true);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Action::INDEX, Action::DETAIL)
            ->add(Action::EDIT, Action::SAVE_AND_ADD_ANOTHER);
    }
}
