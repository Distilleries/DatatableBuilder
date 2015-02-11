<script type="text/javascript">
    jQuery(document).ready(function(){
        // dynamic table
        oTable = jQuery('#{{ $id }}').dataTable({

        "language": {
            "aria": {
                "sortAscending": "{{_(": activate to sort column ascending")}}",
                "sortDescending":  "{{_(": activate to sort column descending")}}"
            },
            "emptyTable":  "{{_("No data available in table")}}",
            "info":  "{{_("Showing _START_ to _END_ of _TOTAL_ entries")}}",
            "infoEmpty":  "{{_("No entries found")}}",
            "infoFiltered":  "{{_("(filtered1 from _MAX_ total entries)")}}",
            "lengthMenu":  "{{_("Show _MENU_ entries")}}",
            "search":  "{{_("Search:")}}",
            "zeroRecords":  "{{_("No matching records found")}}"
        },
         'fnServerData': function (sSource, aoData, fnCallback) {
            $.ajax
            ({
                'dataType': 'text json',
                'type': 'GET',
                'url': sSource,
                'data': dist.Form.Fields.DatatableUtils.addSessionFilters(aoData,$('#{{ $id }}')),
                'success': fnCallback
            });
        },
        'fnDrawCallback': function (oSettings) {

        },
        @foreach ($options as $k => $o)
            {{ json_encode($k) }}: {{ json_encode($o) }},
        @endforeach

        @foreach ($callbacks as $k => $o)
            {{ json_encode($k) }}: {{ $o }},
        @endforeach

        }).on( 'draw.dt', function () {
              Metronic.initAjax();
        });
        jQuery('select',jQuery('#{{ $id }}_wrapper')).select2();
        dist.Form.Fields.DatatableUtils.initFilters('.filter-cancel','.filter-submit');
    // custom values are available via $values array
    });
</script>
