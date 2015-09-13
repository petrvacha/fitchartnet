<?php

namespace App\Components\SignForm;


interface ISignFormFactory
{
    /**
     * @return \App\Components\SignForm
     */
    function create();
}