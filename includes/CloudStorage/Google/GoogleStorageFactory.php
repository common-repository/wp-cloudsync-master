<?php

namespace OneTeamSoftware\CloudStorage\Google;

use Google\Cloud\Core\Exception\GoogleException;
use Google\Cloud\Storage\StorageClient;
use OneTeamSoftware\CloudStorage\NullStorage;
use OneTeamSoftware\CloudStorage\StorageFactoryInterface;
use OneTeamSoftware\CloudStorage\StorageInterface;
use OneTeamSoftware\Logger\LoggerInterface;
use OneTeamSoftware\Logger\NullLogger;

class GoogleStorageFactory implements StorageFactoryInterface
{
	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * constructor.
	 *
	 * @param LoggerInterface $logger
	 */
	public function __construct(LoggerInterface $logger = null)
	{
		$this->logger = $logger ?? new NullLogger();
	}

	/**
	 * returns google cloud storage or a null storage instance
	 *
	 * @param array $config {
	 *     Configuration options.
	 *
	 *     @type string $apiEndpoint The hostname with optional port to use in
	 *           place of the default service endpoint. Example:
	 *           `foobar.com` or `foobar.com:1234`.
	 *     @type string $projectId The project ID from the Google Developer's
	 *           Console.
	 *     @type CacheItemPoolInterface $authCache A cache used storing access
	 *           tokens. **Defaults to** a simple in memory implementation.
	 *     @type array $authCacheOptions Cache configuration options.
	 *     @type callable $authHttpHandler A handler used to deliver Psr7
	 *           requests specifically for authentication.
	 *     @type FetchAuthTokenInterface $credentialsFetcher A credentials
	 *           fetcher instance.
	 *     @type callable $httpHandler A handler used to deliver Psr7 requests.
	 *           Only valid for requests sent over REST.
	 *     @type array $keyFile The contents of the service account credentials
	 *           .json file retrieved from the Google Developer's Console.
	 *           Ex: `json_decode(file_get_contents($path), true)`.
	 *     @type string $keyFilePath The full path to your service account
	 *           credentials .json file retrieved from the Google Developers
	 *           Console.
	 *     @type float $requestTimeout Seconds to wait before timing out the
	 *           request. **Defaults to** `0` with REST and `60` with gRPC.
	 *     @type int $retries Number of retries for a failed request.
	 *           **Defaults to** `3`.
	 *     @type array $scopes Scopes to be used for the request.
	 *     @type string $quotaProject Specifies a user project to bill for
	 *           access charges associated with the request.
	 * }
	 * @return StorageInterface
	 */
	public function create(array $config): StorageInterface
	{
		if (isset($config['keyFile']) && is_string($config['keyFile'])) {
			$config['keyFile'] = json_decode($config['keyFile'], true);
		}

		try {
			return new GoogleStorage(new StorageClient($config), $this->logger);
		} catch (GoogleException $e) {
			$this->logger->error(__FILE__, __LINE__, 'Failed to create StorageClient: ' . $e->getMessage());
		}

		return new NullStorage();
	}
}
