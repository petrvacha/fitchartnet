<?php

namespace App\Components\NewPasswordForm;


interface INewPasswordFormFactory
{
    /**
     * @param int $token
     * @return \App\Components\NewPasswordForm
     */
    function create($token);
}