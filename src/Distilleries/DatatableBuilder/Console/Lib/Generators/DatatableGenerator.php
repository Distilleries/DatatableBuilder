<?php

namespace Distilleries\DatatableBuilder\Console\Lib\Generators;

use Kris\LaravelFormBuilder\Console\FormGenerator;

class DatatableGenerator extends FormGenerator
{

    /**
     * Prepare template for single add field.
     *
     * @param string $field
     * @param bool $isLast
     * @return string
     */
    protected function prepareAdd($field, $isLast = false)
    {
        $field = trim($field);
        $textArr = [
            "            ->add('",
            $field,
            "', null",
            ", trans('datatable." . strtolower($field) . "')",
            ")",
            ($isLast) ? "" : "\n"
        ];

        return join('', $textArr);
    }
}
