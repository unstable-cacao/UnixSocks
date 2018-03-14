<?php
namespace UnixSocks;


interface IClient
{
	public function setFile(string $path): void;
	public function getFile(): string;
	
	public function tryConnect(): bool;
	public function connect(): void;
	public function accept(?float $timeout = null): bool;
	public function close(): void;
	
	public function isOpen(): bool;
	public function isClosed(): bool;
	
	/**
	 * @return resource|null
	 */
	public function getSocket();
	
	
	public function hasInput(): bool;
	
	public function read(int $maxLength = 1024, ?float $timeout = null): ?string;
	public function readBuffer(int $length = 1024, ?float $timeout = null): ?string;
	public function readLine(?float $timeout = null, ?int $maxLength = null): ?string;
	
	/**
	 * @param string|string[] $stop
	 * @param float|null $timeout
	 * @param int|null $maxLength
	 * @return string|null
	 */
	public function readUntil($stop, ?float $timeout = null, ?int $maxLength = null): ?string;
	
	public function write(string $input): void;
	public function writeLine(string $input): void;
}