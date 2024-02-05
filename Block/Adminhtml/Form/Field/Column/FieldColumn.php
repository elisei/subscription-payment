<?php
/**
 * O2TI Payment Subscription.
 *
 * Copyright © 2024 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\SubscriptionPayment\Block\Adminhtml\Form\Field\Column;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Magento\Ui\Component\Form\AttributeMapper;

/**
 * Class Field Column - Create Field to Column.
 */
class FieldColumn extends Select
{
    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element.
     *
     * @param string $value
     *
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }

        return parent::_toHtml();
    }

    /**
     * Render Options.
     *
     * @return array
     */
    private function getSourceOptions(): array
    {
        $options = [
            [
                'label' => __('1 hour'),
                'value' => 'PT1H',
            ],
            [
                'label' => __('1 day'),
                'value' => 'P1D',
            ],
            [
                'label' => __('14 days'),
                'value' => 'P14D',
            ],
            [
                'label' => __('1 month'),
                'value' => 'P1M',
            ],
            [
                'label' => __('45 days'),
                'value' => 'P45D',
            ],
            [
                'label' => __('3 months'),
                'value' => 'P3M',
            ]
        ];

        return $options;
    }
}
