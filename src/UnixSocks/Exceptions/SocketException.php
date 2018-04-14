<?php
namespace UnixSocks\Exceptions;


class SocketException extends UnixSocksException
{
	public function __construct()
	{
		parent::__construct(socket_strerror(socket_last_error()));
	}
}