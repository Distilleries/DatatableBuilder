@if(!empty($form->getFields()))
<div class="row row-filter">
    <div class="col-md-12">
        <div class="tabbable tabbable-custom boxless tabbable-reversed ">
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet box grey-cascade">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="glyphicon glyphicon-filter"></i>{{_('Filters')}}
                            </div>
                            <div class="tools">
                                <a href="javascript:;" class="expand"></a>
                            </div>
                        </div>
                        <div class="portlet-body form" style="display: none">
                            <div class="form-horizontal form-bordered">
                            {{ form_start($form) }}
                                <div class="form-body">
                                    {{ form_rest($form) }}
                                </div>
                                <div class="form-actions ">
                                    <div class="btn-set pull-right">
                                        <button class="btn btn-sm yellow filter-submit margin-bottom"><i class="glyphicon glyphicon-search"></i> {{ _('Search') }}</button>
                                        <button class="btn btn-sm red filter-cancel"><i class="glyphicon glyphicon-remove"></i> {{ _('Reset') }}</button>
                                    </div>
                                </div>
                            {{ form_end($form) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif