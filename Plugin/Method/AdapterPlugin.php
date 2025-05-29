<?php
namespace Aci\Payment\Plugin\Method;

use Magento\Payment\Model\Method\Adapter;
use TryzensIgnite\Base\Gateway\Config\Config as BaseConfig;

class AdapterPlugin
{
    /**
     * @var BaseConfig
     */
    private BaseConfig $baseConfig;

    /**
     * AdapterPlugin constructor.
     *
     * @param BaseConfig $baseConfig
     */
    public function __construct(
        BaseConfig $baseConfig
    ) {
        $this->baseConfig = $baseConfig;
    }

    /**
     * Method to check if Ignite Generic is enabled.
     *
     * @param Adapter $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsActive(Adapter $subject, bool $result): bool
    {
        $methodCode = $subject->getCode();
        $isIgnitePaymentMethod = $this->ignitePaymentMethods($methodCode);
        if ($isIgnitePaymentMethod) {
            if ($result) {
                $result = $this->baseConfig->isActive();
            }
        }
        return $result;
    }
    /**
     * Method to get accepted payment methods for generic module enable check
     *
     * @param string $methodCode
     * @return bool
     */
    public function ignitePaymentMethods(string $methodCode): bool
    {
        if (str_contains($methodCode, 'aci_apm')) {
            return true;
        }
        return false;
    }
}
