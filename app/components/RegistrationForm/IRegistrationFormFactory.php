<?php

namespace App\Components\RegistrationForm;

interface IRegistrationFormFactory
{
    /**
     * @return \App\Components\RegistrationForm
     */
    public function create();
}
