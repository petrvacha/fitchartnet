<?php


interface IRegistrationFormFactory
{
    /**
     * @return \App\Components\RegistrationForm
     */
    function create();
}