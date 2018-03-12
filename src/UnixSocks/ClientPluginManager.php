<?php
namespace UnixSocks;


class ClientPluginManager implements IClientPlugin
{
	/** @var IClientPlugin[] $plugins */
	private $plugins;
	
	
	/**
	 * @param IClientPlugin[] $plugins
	 */
	public function __construct(array $plugins = [])
	{
		$this->plugins = $plugins;
	}
	
	
	public function connected(IClient $client): void
	{
		foreach ($this->plugins as $plugin)
		{
			$plugin->connected($client);
		}
	}
	
	public function disconnected(IClient $client): void
	{
		foreach ($this->plugins as $plugin)
		{
			$plugin->disconnected($client);
		}
	}
	
	public function read(IClient $client, string $input): void
	{
		foreach ($this->plugins as $plugin)
		{
			$plugin->read($client, $input);
		}
	}
	
	public function write(IClient $client, string $output): void
	{
		foreach ($this->plugins as $plugin)
		{
			$plugin->write($client, $output);
		}
	}
}