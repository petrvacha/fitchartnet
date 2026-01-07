<?php

namespace Fitchart\Application;

/**
 * I/O problem
 */
class IOException extends \Exception
{
};


/**
 * Connection problem
 */
class ConnectionException extends IOException
{
};


/**
 * Data problem
 */
class DataException extends IOException
{
};


/**
 * Unexpected state
 */
class LogicException extends \LogicException
{
};


/**
 * Unaccetable state
 */
class SecurityException extends LogicException
{
};


/**
 * Methods which are partially / not implemented
 */
class NotImplementedException extends LogicException
{
};


/**
 * Invalid argument passed to a method
 */
class InvalidArgumentException extends LogicException
{
};


/**
 * Application jumped to invalid state - fatal
 */
class InvalidStateException extends LogicException
{
};
