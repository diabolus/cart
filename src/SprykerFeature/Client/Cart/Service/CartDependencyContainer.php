<?php
/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace SprykerFeature\Client\Cart\Service;

use Generated\Client\Ide\FactoryAutoCompletion\Cart;
use SprykerEngine\Client\Kernel\Service\AbstractDependencyContainer;
use SprykerFeature\Client\Cart\CartDependencyProvider;
use SprykerFeature\Client\Cart\Service\Zed\CartStubInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @method Cart getFactory()
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

}
