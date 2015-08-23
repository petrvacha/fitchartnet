<?php

namespace App\Presenters;


/**
 * Main presenter
 */
class MainPresenter extends LoginBasePresenter
{
    /** @var \IActivityFormFactory @inject */
    public $activityFormFactory;
    

    public function renderDefault()
    {
        
    }

    /**
     * @return Form
     */
    protected function createComponentActivityForm()
    {
        $form = $this->activityFormFactory->create();

        $form->onSuccess[] = function () {
            $this->redirect('Main:default');
        };
        return $form;
    }

}
