<?php
declare(strict_types=1);

namespace Aci\Payment\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\AbstractBlock;

/**
 * HTML Textarea element block
 *
 * @method setName(string $value)
 * @method getName()
 * @method getValue()
 */
class TextAreaRenderer extends AbstractBlock
{
    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return TextAreaRenderer
     */
    public function setInputName(string $value): TextAreaRenderer
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param string $value
     * @return TextAreaRenderer
     */
    public function setInputId(string $value): TextAreaRenderer
    {
        return $this->setId($value);
    }

    /**
     * Set element's HTML ID
     *
     * @param string $elementId ID
     * @return TextAreaRenderer
     */
    public function setId(string $elementId): TextAreaRenderer
    {
        $this->setData('id', $elementId);
        return $this;
    }

    /**
     * Set element's CSS class
     *
     * @param string $class Class
     * @return TextAreaRenderer
     */
    public function setClass(string $class): TextAreaRenderer
    {
        $this->setData('class', $class);
        return $this;
    }

    /**
     * Set element's HTML title
     *
     * @param string $title Title
     * @return TextAreaRenderer
     */
    public function setTitle(string $title): TextAreaRenderer
    {
        $this->setData('title', $title);
        return $this;
    }

    /**
     * HTML ID of the element
     *
     * @return string|null
     */
    public function getId(): string|null
    {
        return $this->getData('id');
    }

    /**
     * CSS class of the element
     *
     * @return string|null
     */
    public function getClass(): string|null
    {
        return $this->getData('class');
    }

    /**
     * Returns HTML title of the element
     *
     * @return string|null
     */
    public function getTitle(): string|null
    {
        return $this->getData('title');
    }

    /**
     * Render HTML
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _toHtml(): string
    {
        return '<textarea name="' .
            $this->getName() .
            '" id="' .
            $this->getId() .
            '" class="' .
            $this->getClass() .
            '">'.$this->getValue().'</textarea>';
    }

    /**
     * Alias for toHtml()
     *
     * @return string
     */
    public function getHtml()
    {
        return $this->toHtml();
    }
}
