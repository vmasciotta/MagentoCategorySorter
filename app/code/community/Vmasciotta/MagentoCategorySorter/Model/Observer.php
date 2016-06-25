<?php

class Vmasciotta_MagentoCategorySorter_Model_Observer
{

    protected function orderCategories($parentId, $conn){
        //The position field is in the main category table
        //add prefix if you have one
        $table = 'catalog_category_entity';
        //get the children for a specific category id
        $collection = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('name')
            ->addFieldToFilter('parent_id', $parentId);
        $categories = array();
        foreach ($collection as $category){
            //in case there are 2 categories with the same name we shouldn't skip them
            //but there shouldn't be
            $categories[$category->getName().'_'.$category->getId()] = $category->getId();
        }
        //sort categories by name
        ksort($categories);
        //set their position
        $position = 1;

        foreach ($categories as $name=>$id) {
            $q = "UPDATE {$table} SET 'position' = {$position} where 'entity_id' = {$id}";
            $conn->query($q);
            $position++;
            //sort the current category children by calling the same function recursively
            $this->orderCategories($id, $conn);
        }
    }

    public function sortCategories(Varien_Event_Observer $observer)
    {
        //set the initial parameters for the function
        $root = 1;
        $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
        $this->orderCategories($root, $conn);
    }
}