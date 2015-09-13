<?php

namespace App\Components\RegistrationForm;


interface IRegistrationFormFactory
{
    /**
     * @return \App\Components\RegistrationForm
     */
    function create();
}