<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\AgentAuthRestApi\Api\Storefront\Provider;

use Generated\Api\Storefront\AgentCustomerSearchStorefrontResource;
use Generated\Shared\Transfer\CustomerQueryTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractStorefrontProvider;
use Spryker\Client\Agent\AgentClientInterface;
use Spryker\Glue\AgentAuthRestApi\AgentAuthRestApiConfig;

class AgentCustomerSearchStorefrontProvider extends AbstractStorefrontProvider
{
    public function __construct(
        protected AgentClientInterface $agentClient,
        protected AgentAuthRestApiConfig $agentAuthRestApiConfig,
    ) {
    }

    /**
     * @return array<\Generated\Api\Storefront\AgentCustomerSearchStorefrontResource>
     */
    protected function provideCollection(): array
    {
        $query = (string)$this->getRequest()->query->get($this->agentAuthRestApiConfig->getRequestParameterQuery(), '');

        $limit = $this->getPaginationLimit($this->agentAuthRestApiConfig->getDefaultCustomerSearchPaginationLimit());
        $offset = $this->getPaginationOffset($this->agentAuthRestApiConfig->getDefaultCustomerSearchPaginationOffset());

        $customerQueryTransfer = (new CustomerQueryTransfer())
            ->setQuery($query)
            ->setOffset($offset)
            ->setLimit($limit);

        $customerAutocompleteResponseTransfer = $this->agentClient->findCustomersByQuery($customerQueryTransfer);

        $customers = [];
        foreach ($customerAutocompleteResponseTransfer->getCustomers() as $customerTransfer) {
            $customers[] = $this->mapCustomerTransferToArray($customerTransfer);
        }

        $nbResults = $customerAutocompleteResponseTransfer->getPagination()?->getNbResults() ?? 0;

        $resource = new AgentCustomerSearchStorefrontResource();
        $resource->customers = $customers;
        // currentPage and maxPage will be read by Spryker\ApiPlatform\EventSubscriber\PaginationLinksResponseSubscriber
        // to emit JSON:API top-level pagination links (first, last, prev, next).
        $resource->pagination = $this->calculatePagination($offset, $limit, $nbResults);

        return [$resource];
    }

    /**
     * @return array<string, string|null>
     */
    protected function mapCustomerTransferToArray(CustomerTransfer $customerTransfer): array
    {
        return [
            'customerReference' => $customerTransfer->getCustomerReference(),
            'email' => $customerTransfer->getEmail(),
            'firstName' => $customerTransfer->getFirstName(),
            'lastName' => $customerTransfer->getLastName(),
        ];
    }
}
