<script type="text/javascript">
    jQuery(document).ready(function () {
        oTable = jQuery('#{{ $id }}').dataTable({
            stateSave: true,
            "language": {
                "aria": {
                    "sortAscending": "{{ trans('datatable-builder::datatable.activate_to_sort_asc') }}",
                    "sortDescending": "{{ trans('datatable-builder::datatable.activate_to_sort_desc') }}"
                },
                "emptyTable": "{{ trans('datatable-builder::datatable.no_data') }}",
                "info": "{{ trans('datatable-builder::datatable.info') }}",
                "infoEmpty": "{{ trans('datatable-builder::datatable.infoEmpty') }}",
                "infoFiltered": "{{ trans('datatable-builder::datatable.infoFiltered') }}",
                "lengthMenu": "{{ trans('datatable-builder::datatable.lengthMenu') }}",
                "search": "{{ trans('datatable-builder::datatable.search') }}",
                "zeroRecords": "{{ trans('datatable-builder::datatable.zeroRecords') }}"
            },
            'fnServerData': function (sSource, aoData, fnCallback) {
                $.ajax({
                    'dataType': 'text json',
                    'type': 'GET',
                    'url': sSource,
                    'data': dist.Form.Fields.DatatableUtils.addSessionFilters(aoData, $('#{{ $id }}')),
                    'success': fnCallback
                });
            },
            'fnDrawCallback': function (oSettings) {
                //
            },
            @foreach ($options as $k => $o)
                {!! json_encode($k) !!}: {!! json_encode($o) !!},
            @endforeach
            @foreach ($callbacks as $k => $o)
                {!! json_encode($k) !!}: {!! $o !!},
            @endforeach
        }).on('draw.dt', function () {
            if (typeof Metronic !== 'undefined') {
                Metronic.initAjax();
            }
        });

        if (typeof jQuery.select2 !== 'undefined') {
            jQuery('select', jQuery('#{{ $id }}_wrapper')).select2();
        }

        dist.Form.Fields.DatatableUtils.initFilters('.filter-cancel', '.filter-submit');

        // Custom values are available via $values array
    });
</script>