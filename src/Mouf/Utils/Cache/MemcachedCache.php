<?php
namespace Mouf\Utils\Cache;

use Memcached;
use Psr\Log\LoggerInterface;

/**
 * This package contains a cache mechanism that relies on Memcached lib.
 */
class MemcachedCache implements CacheInterface {
	
	/**
	 * The default time to live of elements stored in the session (in seconds).
	 * Please note that if the session is flushed, all the elements of the cache will disapear anyway.
	 * If empty, the time to live will be the time of the session. 
	 *
	 * @var int
	 */
	protected $defaultTimeToLive;
	
	/**
	 * The logger used to trace the cache activity.
	 *
	 * @var LoggerInterface
	 */
	protected $log;
	
	/**
	 * Memcached Server parameters. Register the host and port for each server, like "127.0.0.1:11211"
	 * This plugin establishes a connection only if it is used by a request.
	 *
	 * @var array<string>
	 */
	protected $servers;
	
	/**
	 * If the connection cannot be establish, throw an exception if the parameter is check (by default), else nothing
	 *
	 * @var bool
	 */
	protected $crash = true;
	
	/**
	 * 
	 * @param array<string> $servers Memcached Server parameters. Register the host and port for each server, like "127.0.0.1:11211"
	 * @param int $defaultTimeToLive The default time to live of elements stored in the session (in seconds). Defaults to 3600.
	 * @param LoggerInterface $log The logger used to trace the cache activity.
	 * @param bool $exceptionOnConnectionError If the connection cannot be establish, throw an exception if the parameter is check (by default), else nothing
	 */
	public function __construct($servers, $defaultTimeToLive = 3600, LoggerInterface $log = null, $exceptionOnConnectionError = true) {
		$this->servers = $servers;
		$this->defaultTimeToLive = $defaultTimeToLive;
		$this->log = $log;
		$this->crash = $exceptionOnConnectionError;
	}
	
	/**
	 * Memcached object to save the connection
	 * 
	 * @var Memcache
	 */
	private $memcachedObject = null;
	
	/**
	 * Save value if it is impossible to establish memcache connection
	 * 
	 * @var bool
	 */
	private $noConnection = false;
	
	/**
	 * Establish the connection to the memcached server
	 * 
	 * @throws Exception If no connection set in the Mouf configuration
	 */
	private function connection() {
		if($this->servers) {
			$this->memcachedObject = new Memcached();
			$noServer = true;
			foreach ($this->servers as $server) {
				$parameters = explode(':', $server);
				if(!isset($parameters[1]))
					$parameters[1] = '11211';
				$this->memcachedObject->addServer($parameters[0], $parameters[1]);
				$status = $this->memcachedObject->getStats();
				if($status) {
					$noServer = false;
				}
			}
			if($noServer) {
				$this->noConnection = true;
				if ($this->log) {
					$this->log->error('Memcache Exception, unable to establish connection to memcached server');
				}
				if($this->crash) {
					throw new MemcachedCachedException('Memcache Exception, unable to establish connection to memcached server');
				}
				return false;
			}
			return true;
		}
		throw new MemcachedCachedException("Error no connection set in Memcached cache. Add it in the Mouf interface");
	}
	
	/**
	 * Returns the cached value for the key passed in parameter.
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get($key) {
		if($this->memcachedObject === null) {
			if(!$this->connection()) {
				return null;
			}
		} elseif($this->noConnection) {
			return null;
		}
		
		return $this->memcachedObject->get($key);
	}
	
	/**
	 * Sets the value in the cache.
	 *
	 * @param string $key The key of the value to store
	 * @param mixed $value The value to store
	 * @param float $timeToLive The time to live of the cache, in seconds.
	 */
	public function set($key, $value, $timeToLive = null) {
		if(is_null($this->memcachedObject)) {
			if(!$this->connection())
				return false;
		}
		elseif($this->noConnection)
			return false;
		
		$this->memcachedObject->set($key, $value, $timeToLive);
	}
	
	/**
	 * Removes the object whose key is $key from the cache.
	 *
	 * @param string $key The key of the object
	 */
	public function purge($key) {
		if(is_null($this->memcachedObject)) {
			if(!$this->connection())
				return false;
		}
		elseif($this->noConnection)
			return false;
		
		$this->memcachedObject->delete($key);
	}
	
	/**
	 * Removes all the objects from the cache.
	 *
	 */
	public function purgeAll() {
		if(is_null($this->memcachedObject)) {
			if(!$this->connection())
				return false;
		}
		elseif($this->noConnection)
			return false;
		
		$this->memcachedObject->flush();
	}
	
}