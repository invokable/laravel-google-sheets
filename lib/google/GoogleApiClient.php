<?php

namespace Revolution\Google\Client;

use BadMethodCallException;
use Google\Client as GoogleClient;
use Google\Service as GoogleService;
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

        // only allow prompt or approval_prompt
        if (Arr::has($config, 'prompt')) {
            $this->client->setPrompt(Arr::get($config, 'prompt', 'auto'));
        } elseif (Arr::has($config, 'approval_prompt')) {
            $this->client->setApprovalPrompt(Arr::get($config, 'approval_prompt', 'auto'));
        }

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
     */
    public function make(string|GoogleService $service): mixed
    {
        if ($service instanceof GoogleService) {
            return $service;
        }

        if (str_starts_with($service, 'Google\\Service\\')) {
            return new $service($this->client);
        }

        $service = 'Google\\Service\\'.ucfirst(str_replace('_', '', $service));

        try {
            if (class_exists($service)) {
                return new $service($this->client);
            }
            // catch any errors thrown when fetching the service
            // this can be caused when the service was removed
            // but the reference still exists in the auto loader
            // @codeCoverageIgnoreStart
        } catch (\ErrorException $e) {
            if (str_contains($e->getMessage(), 'No such file or directory')) {
                UnknownServiceException::throwForService($service, 0, $e);
            }

            throw $e;
            // @codeCoverageIgnoreEnd
        }

        UnknownServiceException::throwForService($service);
    }

    /**
     * Setup correct auth method based on type.
     *
     * @return void
     */
    protected function auth($userEmail = '')
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
    protected function useAssertCredentials($userEmail = '')
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
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     *
     * @throws BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (method_exists($this->client, $method)) {
            return call_user_func_array([$this->client, $method], $parameters);
        }

        throw new BadMethodCallException(sprintf('Method [%s] does not exist.', $method));
    }
}
