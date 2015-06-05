<?php

/**
 * @author wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
class shopWholesalePluginBackendSaveCategoryController extends waJsonController {

    public function execute() {
        try {
            $category_id = waRequest::post('category_id');
            $name = waRequest::post('name');
            $value = waRequest::post('value');
            $category_model = new shopCategoryModel();
            $category_model->updateById($category_id, array($name => $value));
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
