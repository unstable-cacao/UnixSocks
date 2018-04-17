<?php
namespace UnixSocks\Exceptions\Comms;


use UnixSocks\Exceptions\UnixSocksException;


class CommsException extends UnixSocksException
{
	public function __construct($socket)
	{
		$code = socket_last_error($socket);
		
		parent::__construct(
			socket_strerror($code),
			$code
		);
	}
}