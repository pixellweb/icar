<?php


namespace Citadelle\Icar\app;


class IcarException extends \Exception
{
    /**
     * IcarException constructor.
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        \Log::channel('icar')->alert($message);
    }
}
