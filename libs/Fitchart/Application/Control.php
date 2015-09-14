<?php

namespace Fitchart\Application;

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
     * @param \Nette\Application\UI\Form $form
     */
    protected function addBootstrapStyling(\Nette\Application\UI\Form $form)
    {
        $form->getElementPrototype()->class('form-horizontalss');
        $usedPrimary = FALSE;
        
        foreach ($form->getControls() as $control) {
            if ($control instanceof \Nette\Forms\Controls\Button) {
                    $control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-default');
                    $usedPrimary = TRUE;

            } elseif ($control instanceof \Nette\Forms\Controls\TextBase || $control instanceof \Nette\Forms\Controls\SelectBox || $control instanceof \Nette\Forms\Controls\MultiSelectBox) {
                    $control->getControlPrototype()->addClass('form-control');

            } elseif ($control instanceof \Nette\Forms\Controls\Checkbox || $control instanceof \Nette\Forms\Controls\CheckboxList || $control instanceof \Nette\Forms\Controls\RadioList) {
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

        } elseif ($data === FALSE) {
            $this->data = [];

        } else {
            throw new InvalidArgumentException(__CLASS__ . ': Argument 1 must be an array, instance of Traversable or FALSE expression, ' . gettype($data) . ' given.');
        }
    }
}
