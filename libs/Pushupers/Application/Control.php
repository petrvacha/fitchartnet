<?php

namespace Pushupers\Application;

/**
 * Control
 */
class Control extends \Nette\Application\UI\Control
{
    /** @const SUCCESS_MESSAGE_CZ */
    const SUCCESS_MESSAGE_CZ = 'Změny úspěšně uloženy.';

    /** @const ERROR_MESSAGE_CZ */
    const ERROR_MESSAGE_CZ = 'Při ukládání nastala chyba.';

    /** @const SUCCESS_MESSAGE_TYPE */
    const SUCCESS_MESSAGE_TYPE = 'success';

    /** @const ERROR_MESSAGE_TYPE */
    const ERROR_MESSAGE_TYPE = 'danger';

    /** @const INFO_MESSAGE_TYPE */
    const INFO_MESSAGE_TYPE = 'info';


    /** @var mixed */
    protected $data = [];

    
    public $onSuccess = [];

    /**
     * @return string
     */
    protected function getTemplatePath()
    {
        $class = $this->getReflection();
        return dirname($class->getFileName()) . '/templates/' . $class->getShortName() . '.latte';
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        if (is_array($data)) {
            $this->data = $data;

        } elseif ($data instanceof \Traversable) {
            $this->data = iterator_to_array($data);

        } elseif ($data === FALSE) {
            $this->data = [];

        } else {
            throw new InvalidArgumentException(__CLASS__ . ': Argument 1 must be an array, instance of Traversable or FALSE expression, ' . gettype($data) . ' given.');
        }
    }
}
