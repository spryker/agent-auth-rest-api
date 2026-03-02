<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\AgentAuthRestApi;

use Spryker\Glue\AgentAuthRestApi\Dependency\Client\AgentAuthRestApiToAgentClientInterface;
use Spryker\Glue\AgentAuthRestApi\Dependency\Client\AgentAuthRestApiToOauthClientInterface;
use Spryker\Glue\AgentAuthRestApi\Dependency\Service\AgentAuthRestApiToOauthServiceInterface;
use Spryker\Glue\AgentAuthRestApi\Dependency\Service\AgentAuthRestApiToUtilEncodingServiceInterface;
use Spryker\Glue\AgentAuthRestApi\Processor\Creator\AgentAccessTokenCreator;
use Spryker\Glue\AgentAuthRestApi\Processor\Creator\AgentAccessTokenCreatorInterface;
use Spryker\Glue\AgentAuthRestApi\Processor\Creator\AgentCustomerImpersonationAccessTokenCreator;
use Spryker\Glue\AgentAuthRestApi\Processor\Creator\AgentCustomerImpersonationAccessTokenCreatorInterface;
use Spryker\Glue\AgentAuthRestApi\Processor\Finder\RestUserFinder;
use Spryker\Glue\AgentAuthRestApi\Processor\Finder\RestUserFinderInterface;
use Spryker\Glue\AgentAuthRestApi\Processor\Logger\AuditLogger;
use Spryker\Glue\AgentAuthRestApi\Processor\Logger\AuditLoggerInterface;
use Spryker\Glue\AgentAuthRestApi\Processor\Mapper\RestUserMapper;
use Spryker\Glue\AgentAuthRestApi\Processor\Mapper\RestUserMapperInterface;
use Spryker\Glue\AgentAuthRestApi\Processor\Reader\AgentAuthorizationHeaderReader;
use Spryker\Glue\AgentAuthRestApi\Processor\Reader\AgentAuthorizationHeaderReaderInterface;
use Spryker\Glue\AgentAuthRestApi\Processor\Reader\CustomerReader;
use Spryker\Glue\AgentAuthRestApi\Processor\Reader\CustomerReaderInterface;
use Spryker\Glue\AgentAuthRestApi\Processor\RestResponseBuilder\AgentAccessTokenRestResponseBuilder;
use Spryker\Glue\AgentAuthRestApi\Processor\RestResponseBuilder\AgentAccessTokenRestResponseBuilderInterface;
use Spryker\Glue\AgentAuthRestApi\Processor\Validator\AgentAccessTokenRestRequestValidator;
use Spryker\Glue\AgentAuthRestApi\Processor\Validator\AgentAccessTokenRestRequestValidatorInterface;
use Spryker\Glue\AgentAuthRestApi\Processor\Validator\AgentValidator;
use Spryker\Glue\AgentAuthRestApi\Processor\Validator\AgentValidatorInterface;
use Spryker\Glue\Kernel\AbstractFactory;

/**
 * @method \Spryker\Glue\AgentAuthRestApi\AgentAuthRestApiConfig getConfig()
 */
class AgentAuthRestApiFactory extends AbstractFactory
{
    public function createCustomerReader(): CustomerReaderInterface
    {
        return new CustomerReader(
            $this->getAgentClient(),
            $this->createAgentAccessTokenRestResponseBuilder(),
            $this->getConfig(),
        );
    }

    public function createAgentAccessTokenCreator(): AgentAccessTokenCreatorInterface
    {
        return new AgentAccessTokenCreator(
            $this->getOauthClient(),
            $this->createAgentAccessTokenRestResponseBuilder(),
            $this->createAuditLogger(),
        );
    }

    public function createAgentCustomerImpersonationAccessTokenCreator(): AgentCustomerImpersonationAccessTokenCreatorInterface
    {
        return new AgentCustomerImpersonationAccessTokenCreator(
            $this->getOauthClient(),
            $this->createAgentAccessTokenRestResponseBuilder(),
        );
    }

    public function createAgentValidator(): AgentValidatorInterface
    {
        return new AgentValidator($this->getConfig());
    }

    public function createAgentAccessTokenRestRequestValidator(): AgentAccessTokenRestRequestValidatorInterface
    {
        return new AgentAccessTokenRestRequestValidator(
            $this->createAgentAuthorizationHeaderReader(),
            $this->getOauthClient(),
        );
    }

    public function createAgentAccessTokenRestResponseBuilder(): AgentAccessTokenRestResponseBuilderInterface
    {
        return new AgentAccessTokenRestResponseBuilder($this->getResourceBuilder());
    }

    public function createRestUserMapper(): RestUserMapperInterface
    {
        return new RestUserMapper($this->createAgentAuthorizationHeaderReader());
    }

    public function createRestUserFinder(): RestUserFinderInterface
    {
        return new RestUserFinder($this->createAgentAuthorizationHeaderReader());
    }

    public function createAgentAuthorizationHeaderReader(): AgentAuthorizationHeaderReaderInterface
    {
        return new AgentAuthorizationHeaderReader(
            $this->getOauthService(),
            $this->getUtilEncodingService(),
        );
    }

    public function createAuditLogger(): AuditLoggerInterface
    {
        return new AuditLogger();
    }

    public function getAgentClient(): AgentAuthRestApiToAgentClientInterface
    {
        return $this->getProvidedDependency(AgentAuthRestApiDependencyProvider::CLIENT_AGENT);
    }

    public function getOauthClient(): AgentAuthRestApiToOauthClientInterface
    {
        return $this->getProvidedDependency(AgentAuthRestApiDependencyProvider::CLIENT_OAUTH);
    }

    public function getOauthService(): AgentAuthRestApiToOauthServiceInterface
    {
        return $this->getProvidedDependency(AgentAuthRestApiDependencyProvider::SERVICE_OAUTH);
    }

    public function getUtilEncodingService(): AgentAuthRestApiToUtilEncodingServiceInterface
    {
        return $this->getProvidedDependency(AgentAuthRestApiDependencyProvider::SERVICE_UTIL_ENCODING);
    }
}
