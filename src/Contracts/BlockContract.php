<?php

namespace Monstrex\AveSite\Contracts;

interface BlockContract
{
    public function renderRegion($region_name, $path = null);
    public function render($key);
    public function renderForm($key, $subject = null, $suffix = null);
    public function renderLayout($layout, $page);
    public function getByID($id);
    public function getByKey($key);
    public function getByTitle($title);
    public function getFormByKey($key);
    public function getBlockField($block, $field);
}
