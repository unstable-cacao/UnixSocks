<?php
namespace UnixSocks;


class ClientBuilder implements IClientBuilder
{
	/** @var ClientPluginManager */
	private $manager = null;
	
	/** @var IClientPlugin[]|string[] */
	private $plugins = [];
	
	
	private function createManager(): void
	{
		foreach ($this->plugins as &$plugin)
		{
			if (is_string($plugin))
				$plugin = new $plugin;
		}
		
		$this->manager = new ClientPluginManager($this->plugins);
	}
	
	
	/**
	 * @param IClientPlugin|string $plugin
	 */
	public function addPlugin($plugin): void
	{
		$this->manager = null;
		$this->plugins[] = $plugin;
	}
	
	public function getClient(?string $socket = null): IClient
	{
		if (!$this->manager)
			$this->createManager();
		
		return new Client($socket, $this->manager);
	}
}