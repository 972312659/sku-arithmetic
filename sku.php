<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 2019/5/14
 * Time: 9:41 AM
 */
/**
 * @param $product
 * @param null $filter = [
 *                          {
 *                              Id: 'DP001',  //属性id(如规格的id)
 *                              PropertyValue: 'DPP001' //属性值id(如规格20mg*10片的id)
 *                          },
 *                          {
 *                              Id: 'DP002',
 *                              PropertyValue: 'DPP003'
 *                          }
 *                      ]
 */
function property($product, $filter = null)
{
    $choosed = [];
    $line = [];
    if (!$filter) {
        $choosed = array_column($filter, 'PropertyValue');
        $line = array_column($filter, 'Id');
    }
    $productSkuGroup = [];
    foreach ($product['Sku'] as $item) {
        $tmpIds = array_column($item['PropertyIds'], 'PropertyValueId');
        foreach ($tmpIds as $k => $id) {
            $tmp_new = $tmpIds;
            unset($tmp_new[$k]);
            $productSkuGroup[$id] = array_merge($productSkuGroup[$id], $tmp_new);
        }
    }
    foreach ($productSkuGroup as &$group) {
        $group = array_unique($group);
    }
    foreach ($product['Properties'] as $property) {
        $choosed_tmp = [];
        if (in_array($property['Id'], $line)) {
            $choosed_tmp = array_search(array_intersect(array_column($property['PropertyValues'], 'Id'), $choosed)[0], $choosed);
        }
        foreach ($property['PropertyValues'] as &$propertyValue) {
            $value_arr = $productSkuGroup[$propertyValue['Id']];
            if ($choosed) {
                if (in_array($propertyValue['Id'], $choosed)) {
                    $propertyValue['Choosed'] = true;
                }
                if (count($choosed) > 1) {
                    if (isset($value_arr) && is_array($value_arr) && count($value_arr) > 0) {
                        $propertyValue['Enable'] = false;
                        if (in_array($property['Id'], $line)) {
                            if ($value_arr == array_intersect($value_arr, $choosed_tmp)) {
                                $propertyValue['Enable'] = true;
                            }
                        } else {
                            if ($value_arr == array_intersect($value_arr, $choosed)) {
                                $propertyValue['Enable'] = true;
                            }
                        }
                    }
                } else {
                    if (in_array($property['Id'], $line)) {
                        $propertyValue['Enable'] = (isset($value_arr) && is_array($value_arr) && count($value_arr) > 0) ? true : false;
                    } else {
                        $propertyValue['Enable'] = (isset($value_arr) && is_array($value_arr) && count($value_arr) > 0 && in_array($choosed[0], $value_arr)) ? true : false;
                    }
                }
            } else {
                $propertyValue['Enable'] = (isset($value_arr) && is_array($value_arr) && count($value_arr) > 0) ? true : false;
            }
        }
    }
}