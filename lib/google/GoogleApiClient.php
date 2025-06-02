<?php

namespace Revolution\Google\Client;

use BadMethodCallException;
use Google\Client as GoogleClient;
use Illuminate\Support\Arr;
use Revolution\Google\Client\Exceptions\UnknownServiceException;

class GoogleApiClient
{
    protected array $config;

    protected GoogleClient $client;

    public function __construct(#[\SensitiveParameter] array $config, string $userEmail = '')
    {
        $this->config = $config;

        // create an instance of the google client for OAuth2
        $this->client = new GoogleClient(Arr::get($config, 'config', []));

        // set application name
        $this->client->setApplicationName(Arr::get($config, 'application_name', ''));

        // set oauth2 configs
        $this->client->setClientId(Arr::get($config, 'client_id', ''));
        $this->client->setClientSecret(Arr::get($config, 'client_secret', ''));
        $this->client->setRedirectUri(Arr::get($config, 'redirect_uri', ''));
        $this->client->setScopes(Arr::get($config, 'scopes', []));
        $this->client->setAccessType(Arr::get($config, 'access_type', 'online'));
        $this->client->setApprovalPrompt(Arr::get($config, 'approval_prompt', 'auto'));

        // set developer key
        $this->client->setDeveloperKey(Arr::get($config, 'developer_key', ''));

        // auth for service account
        if (Arr::get($config, 'service.enable', false)) {
            $this->auth($userEmail);
        }
    }

    /**
     * Getter for the google client.
     */
    public function getClient(): GoogleClient
    {
        return $this->client;
    }

    /**
     * Setter for the google client.
     */
    public function setClient(GoogleClient $client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Getter for the google service.
     *
     * @throws UnknownServiceException|\ReflectionException
     */
    public function make(string $service): mixed
    {
        $service = 'Google\\Service\\'.ucfirst($service);

        if (class_exists($service)) {
            $class = new \ReflectionClass($service);

            return $class->newInstance($this->client);
        }

        throw new UnknownServiceException($service);
    }

    /**
     * Setup correct auth method based on type.
     */
    protected function auth(string $userEmail = ''): void
    {
        // see (and use) if user has set Credentials
        if ($this->useAssertCredentials($userEmail)) {
            return;
        }

        // fallback to compute engine or app engine
        $this->client->useApplicationDefaultCredentials();
    }

    /**
     * Determine and use credentials if user has set them.
     *
     * @return bool used or not
     */
    protected function useAssertCredentials(string $userEmail = ''): bool
    {
        $serviceJsonUrl = Arr::get($this->config, 'service.file', '');

        if (empty($serviceJsonUrl)) {
            return false;
        }

        $this->client->setAuthConfig($serviceJsonUrl);

        if (! empty($userEmail)) {
            $this->client->setSubject($userEmail);
        }

        return true;
    }

    /**
     * Magic call method.
     *
     * @return mixed
     *
     * @throws BadMethodCallException
     */
    public function __call(string $method, array $parameters)
    {
        if (method_exists($this->client, $method)) {
            return $this->client->{$method}(...array_values($parameters));
        }

        throw new BadMethodCallException(sprintf('Method [%s] does not exist.', $method));
    }
}
