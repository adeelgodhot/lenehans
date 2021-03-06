<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Block\Adminhtml\Product\Attribute\Edit\Form;

use Amasty\Stockstatus\Api\Data\StockstatusSettingsInterface;
use Amasty\Stockstatus\Api\StockstatusSettings\GetByOptionIdAndStoreIdInterface;
use Amasty\Stockstatus\Block\Adminhtml\Product\Attribute\Edit\Form\Preview\Image;
use Amasty\Stockstatus\Block\Adminhtml\Product\Attribute\Edit\Form\Renderer\Fieldset\Element as Renderer;
use Amasty\Stockstatus\Model\Backend\StockstatusSettings\Form\ParamsProvider;
use Amasty\Stockstatus\Model\StockstatusSettings as StockstatusSettingsModel;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\Store;

class StockstatusSettings extends Generic
{
    /**
     * @var GetByOptionIdAndStoreIdInterface
     */
    private $getByOptionIdAndStoreId;

    /**
     * @var Renderer
     */
    private $renderer;

    /**
     * @var ParamsProvider
     */
    private $paramsProvider;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        GetByOptionIdAndStoreIdInterface $getByOptionIdAndStoreId,
        Renderer $renderer,
        ParamsProvider $paramsProvider,
        array $data = []
    ) {
        $this->getByOptionIdAndStoreId = $getByOptionIdAndStoreId;
        $this->renderer = $renderer;
        $this->paramsProvider = $paramsProvider;

        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $data
        );
    }

    protected function _prepareForm()
    {
        /** @var StockstatusSettingsModel $stockstatusSetting **/
        $stockstatusSetting = $this->getStockstatusSetting();
        /** @var Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'stockstatus_advanced_setting_form',
                    'class' => 'admin__scope-old',
                    'action' => $this->getUrl('amstockstatus/product_attribute_settings/save', [
                        StockstatusSettingsInterface::OPTION_ID => $this->paramsProvider->getOptionId(),
                        Store::ENTITY => $this->paramsProvider->getStoreId()
                    ]),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                ],
            ]
        );
        $form->setUseContainer(true);
        $form->setFieldsetElementRenderer($this->renderer);
        $form->setDataObject($stockstatusSetting);
        $this->addMainFields($form);
        $form->setValues($stockstatusSetting->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    private function addMainFields(Form $form): void
    {
        $mainSettingsFieldset = $form->addFieldset(
            'main_settings',
            ['class'=>'form-inline']
        );
        $mainSettingsFieldset->addField(
            StockstatusSettingsInterface::IMAGE_PATH,
            'file',
            [
                'name' => StockstatusSettingsInterface::IMAGE_PATH,
                'label' => __('Icon Image'),
                'title' => __('Icon Image'),
                'note' => __('Allowed file types: JPEG (JPG), PNG, GIF, BMP, SVG'),
                'after_element_js'=> $this->getImagePreviewHtml()
            ]
        );
        $mainSettingsFieldset->addField(
            StockstatusSettingsInterface::TOOLTIP_TEXT,
            'textarea',
            [
                'name' => StockstatusSettingsInterface::TOOLTIP_TEXT,
                'label' => __('Tooltip'),
                'title' => __('Tooltip')
            ]
        );
    }

    private function getImagePreviewHtml(): string
    {
        $imagePreviewBlock = $this->getLayout()->createBlock(
            Image::class,
            'amstockstatus_image_preview',
            ['settingsModel' => $this->getStockstatusSetting()]
        );

        return $imagePreviewBlock->toHtml();
    }

    private function getStockstatusSetting(): StockstatusSettingsInterface
    {
        return $this->getByOptionIdAndStoreId->execute(
            $this->paramsProvider->getOptionId(),
            $this->paramsProvider->getStoreId()
        );
    }
}
