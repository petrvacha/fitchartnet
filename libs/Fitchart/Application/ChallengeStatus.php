<?php

namespace Fitchart\Application;

use App\Model\Challenge;

class ChallengeStatus
{
    use \Nette\SmartObject;

    /** @var  string */
    protected $textState;


    /**
     * @param string $textState
     */
    public function __construct($textState)
    {
        $this->textState = $textState;
    }

    /**
     * @param $textState
     * @return ChallengeStatus
     */
    public function setTextState($textState)
    {
        $this->textState = $textState;

        return $this;
    }

    /**
     * @return string
     */
    public function getTextState()
    {
        return $this->textState;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->textState === Challenge::TEXT_STATUS_ACTIVE;
    }

    /**
     * @return bool
     */
    public function isGone()
    {
        return $this->textState === Challenge::TEXT_STATUS_GONE;
    }

    public function __toString()
    {
        return $this->textState;
    }
}
