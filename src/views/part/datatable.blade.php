@section('datatable')
    @if (! empty($filters))
        {!! $filters !!}
    @endif

    <div class="portlet box green-haze">
        <div class="portlet-title">
            <div class="caption">
                <i class="glyphicon glyphicon-th-list"></i>{{ trans('datatable-builder::datatable.listing') }}
            </div>
            <div class="tools">
                <a href="javascript:;" class="collapse"></a>
            </div>
        </div>
        <div class="portlet-body">
            <div class="dataTables_wrapper no-footer">
                <div class="">
                    {!! Datatable::table()
                            ->setId($id)
                            ->addColumn($colomns_display)
                            ->setOptions($datatable_options)
                            ->setUrl(action($route))
                            ->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop
