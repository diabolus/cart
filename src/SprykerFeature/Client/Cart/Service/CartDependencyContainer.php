<?php
/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace SprykerFeature\Client\Cart\Service;

use Generated\Client\Ide\FactoryAutoCompletion\CartService;
use SprykerEngine\Client\Kernel\Service\AbstractDependencyContainer;
use SprykerFeature\Client\Cart\CartDependencyProvider;
use SprykerFeature\Client\Cart\Service\Zed\CartStubInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use SprykerFeature\Client\Cart\Service\KvStorage\CartKvStorageInterface;

/**
 * @method CartService getFactory()
 */
class CartDependencyContainer extends AbstractDependencyContainer
{

    /**
     * @return SessionInterface
     */
    public function createSession()
    {
        $session = $this->getFactory()->createSessionCartSession(
            $this->getProvidedDependency(CartDependencyProvider::SESSION)
        );

        return $session;
    }

    /**
     * @return CartStubInterface
     */
    public function createZedStub()
    {
        $zedStub = $this->getProvidedDependency(CartDependencyProvider::SERVICE_ZED);
        $cartStub = $this->getFactory()->createZedCartStub(
            $zedStub
        );

        return $cartStub;
    }

    /**
     * @return CartKvStorageInterface
     */
    public function createKvStorage()
    {
        $kvStorage = $this->getProvidedDependency(CartDependencyProvider::KV_STORAGE);

        return $this->getFactory()->createKvStorageCartKvStorage($kvStorage);
    }

}
