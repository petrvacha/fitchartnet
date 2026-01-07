<?php

namespace Fitchart\Application;

use Nette\Forms\Controls\Button;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\CheckboxList;
use Nette\Forms\Controls\MultiSelectBox;
use Nette\Forms\Controls\RadioList;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\TextBase;
use Nette\Forms\Controls\UploadControl;
use Nette\Utils\Strings;

/**
 * Control
 */
class Control extends \Nette\Application\UI\Control
{
    /** @const SUCCESS_MESSAGE_CZ */
    public const SUCCESS_MESSAGE_CZ = 'Změny úspěšně uloženy.';

    /** @const ERROR_MESSAGE_CZ */
    public const ERROR_MESSAGE_CZ = 'Při ukládání nastala chyba.';

    /** @const SUCCESS_MESSAGE_TYPE */
    public const SUCCESS_MESSAGE_TYPE = 'success';

    /** @const ERROR_MESSAGE_TYPE */
    public const ERROR_MESSAGE_TYPE = 'danger';

    /** @const INFO_MESSAGE_TYPE */
    public const INFO_MESSAGE_TYPE = 'info';


    /** @var mixed */
    protected $data = [];


    public $onSuccess = [];


    /**
     * @param \Nette\Application\UI\Form $form
     */
    protected function addBootstrapStyling(\Nette\Application\UI\Form $form)
    {
        $form->getElementPrototype()->class('form-horizontal');
        
        foreach ($form->getControls() as $control) {
            if ($control instanceof Button) {
                $control->getControlPrototype()->addClass(Strings::startsWith($control->getName(), 'submit') ? 'btn btn-primary btn-block' : 'btn btn-primary');
            } elseif ($control instanceof UploadControl || $control instanceof TextBase || $control instanceof SelectBox || $control instanceof MultiSelectBox) {
                $control->getControlPrototype()->addClass('form-control');
            } elseif ($control instanceof Checkbox || $control instanceof CheckboxList || $control instanceof RadioList) {
                $control->getSeparatorPrototype()->setName('div')->addClass($control->getControlPrototype()->type);
            }
        }
    }

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
        } elseif ($data === false) {
            $this->data = [];
        } else {
            throw new InvalidArgumentException(__CLASS__ . ': Argument 1 must be an array, instance of Traversable or FALSE expression, ' . gettype($data) . ' given.');
        }
    }
}
