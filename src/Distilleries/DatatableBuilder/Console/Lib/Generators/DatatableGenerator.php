<?php  namespace Distilleries\DatatableBuilder\Console\Lib\Generators;

class DatatableGenerator extends \Kris\LaravelFormBuilder\Console\FormGenerator
{

    /**
     * Prepare template for single add field
     *
     * @param      $field
     * @param bool $isLast
     * @return string
     */
    protected function prepareAdd($field, $isLast = false)
    {
        $field = trim($field);
        $textArr = [
            "            ->add('",
            $field,
            "',null",
            ",trans('datatable.".strtolower($field)."')",
            ")",
            ($isLast) ? "" : "\n"
        ];

        return join('', $textArr);
    }
}
