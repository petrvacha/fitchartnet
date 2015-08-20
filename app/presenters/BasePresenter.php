<?php

namespace App\Presenters;

use Nette,
	App\Model;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    /** @const MESSAGE_TYPE_INFO string */
    const MESSAGE_TYPE_INFO = 'info';

    /** @const MESSAGE_TYPE_SUCCESS string */
    const MESSAGE_TYPE_SUCCESS = 'success';

    /** @const MESSAGE_TYPE_ERROR string */
    const MESSAGE_TYPE_ERROR = 'error';
}
