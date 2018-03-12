<?php
namespace UnixSocks;


interface IClientBuilder
{
	/**
	 * @param IClientPlugin|string $plugin
	 */
	public function addPlugin($plugin): void;
	
	public function getClient(?string $socket = null): IClient;
}