<?php

namespace App\Components\ResetPasswordForm;


interface IResetPasswordFormFactory
{
    /**
     * @return \App\Components\ResetPasswordForm
     */
    function create();
}