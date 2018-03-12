<?php
namespace UnixSocks;


interface IClientPlugin
{
	public function connected(IClient $client): void;
	public function disconnected(IClient $client): void;
	public function read(IClient $client, string $input): void;
	public function write(IClient $client, string $output): void;
}