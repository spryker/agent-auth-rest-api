<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerTest\Glue\AgentAuthRestApi\Helper;

use Codeception\Module;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\UserTransfer;
use PHPUnit\Framework\Assert;
use SprykerTest\ApiPlatform\Helper\ApiRequestHelperTrait;
use SprykerTest\Shared\Customer\Helper\CustomerDataHelper;
use SprykerTest\Shared\User\Helper\UserDataHelper;
use Symfony\Component\HttpFoundation\Response;

/**
 * The agent identity an agent-scoped suite acts as, and the token requests that mint it.
 *
 * Nothing about the identity is stubbed: the agent is a Zed user with `isAgent` set, and the token
 * every secured request carries is minted by the real `agent_credentials` grant through
 * `POST /agent-access-tokens`. That is why a suite using it enables no `ApiLoginHelper` - the OAuth
 * client it would replace is the very thing under test, and the `agent` scope the grant issues is
 * what the `ROLE_AGENT` gate on the agent resources reads.
 */
class AgentDataHelper extends Module
{
    use ApiRequestHelperTrait;

    protected const string URI_AGENT_ACCESS_TOKENS = '/agent-access-tokens';

    protected const string RESOURCE_TYPE_AGENT_ACCESS_TOKENS = 'agent-access-tokens';

    protected const string URI_ACCESS_TOKENS = '/access-tokens';

    protected const string RESOURCE_TYPE_ACCESS_TOKENS = 'access-tokens';

    protected const string ATTRIBUTE_USERNAME = 'username';

    protected const string ATTRIBUTE_PASSWORD = 'password';

    protected const string ATTRIBUTE_ACCESS_TOKEN = 'accessToken';

    protected const string CUSTOMER_PASSWORD = '$SecureCustomerP@ssw0rd123#';

    protected const string HEADER_AUTHORIZATION = 'Authorization';

    protected const string BEARER_PREFIX = 'Bearer ';

    /**
     * A token the OAuth server would reject: syntactically a bearer, so the firewall's authenticator
     * engages and then fails, which is the `401` case as opposed to the anonymous `403`.
     */
    protected const string INVALID_ACCESS_TOKEN = 'not-a-signed-jwt';

    /**
     * `haveUser()` persists it hashed the way the agent-credentials grant verifies it, so the token
     * request can send the same value.
     */
    public const string AGENT_PASSWORD = '$SecureAgentP@ssw0rd123#';

    public function arrangeAgent(): UserTransfer
    {
        return $this->getUserDataHelper()->haveUser([
            UserTransfer::PASSWORD => static::AGENT_PASSWORD,
            UserTransfer::IS_AGENT => true,
        ]);
    }

    protected function getUserDataHelper(): UserDataHelper
    {
        /** @var \SprykerTest\Shared\User\Helper\UserDataHelper $userDataHelper */
        $userDataHelper = $this->getModule('\\' . UserDataHelper::class);

        return $userDataHelper;
    }

    /**
     * Authenticates every following request in the method as an agent, with a token obtained the way
     * a storefront agent obtains one - by posting agent credentials to `/agent-access-tokens`.
     */
    public function arrangeAuthenticatedAgent(): UserTransfer
    {
        $userTransfer = $this->arrangeAgent();

        $response = $this->postAgentAccessToken((string)$userTransfer->getUsername(), static::AGENT_PASSWORD);

        Assert::assertSame(Response::HTTP_CREATED, $response->getStatusCode(), (string)$response->getContent());

        $this->authenticateWithToken((string)$this->readAttributes($response)[static::ATTRIBUTE_ACCESS_TOKEN]);

        return $userTransfer;
    }

    /**
     * Authenticates as an ordinary customer: a token the stack accepts, carrying the `customer`
     * scope and therefore not `ROLE_AGENT`. This is what separates "authenticated but forbidden"
     * from the unauthenticated case on an agent-only resource.
     */
    public function arrangeAuthenticatedCustomer(): CustomerTransfer
    {
        $customerTransfer = $this->getCustomerDataHelper()->haveConfirmedCustomer([
            CustomerTransfer::PASSWORD => static::CUSTOMER_PASSWORD,
            CustomerTransfer::NEW_PASSWORD => static::CUSTOMER_PASSWORD,
        ]);

        $response = $this->handleApiRequest(
            'POST',
            static::URI_ACCESS_TOKENS,
            $this->encodeJsonApiBody(static::RESOURCE_TYPE_ACCESS_TOKENS, [
                static::ATTRIBUTE_USERNAME => $customerTransfer->getEmail(),
                static::ATTRIBUTE_PASSWORD => static::CUSTOMER_PASSWORD,
            ]),
        );

        Assert::assertSame(Response::HTTP_CREATED, $response->getStatusCode(), (string)$response->getContent());

        $this->authenticateWithToken((string)$this->readAttributes($response)[static::ATTRIBUTE_ACCESS_TOKEN]);

        return $customerTransfer;
    }

    public function arrangeInvalidAccessToken(): void
    {
        $this->authenticateWithToken(static::INVALID_ACCESS_TOKEN);
    }

    public function postAgentAccessToken(string $username, string $password): Response
    {
        return $this->handleApiRequest(
            'POST',
            static::URI_AGENT_ACCESS_TOKENS,
            $this->encodeJsonApiBody(static::RESOURCE_TYPE_AGENT_ACCESS_TOKENS, [
                static::ATTRIBUTE_USERNAME => $username,
                static::ATTRIBUTE_PASSWORD => $password,
            ]),
        );
    }

    protected function authenticateWithToken(string $accessToken): void
    {
        $this->addDefaultRequestHeader(static::HEADER_AUTHORIZATION, static::BEARER_PREFIX . $accessToken);
    }

    /**
     * @return array<string, mixed>
     */
    protected function readAttributes(Response $response): array
    {
        /** @var array{data: array{attributes: array<string, mixed>}} $document */
        $document = json_decode((string)$response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        return $document['data']['attributes'];
    }

    protected function getCustomerDataHelper(): CustomerDataHelper
    {
        /** @var \SprykerTest\Shared\Customer\Helper\CustomerDataHelper $customerDataHelper */
        $customerDataHelper = $this->getModule('\\' . CustomerDataHelper::class);

        return $customerDataHelper;
    }
}
