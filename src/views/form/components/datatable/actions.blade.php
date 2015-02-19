<a href="{{ action($route.'getView',$data['id']) }}" class="btn btn-sm blue filter-submit margin-bottom"><i class="glyphicon glyphicon-edit"></i> {{_('View')}}</a>
<a href="{{ action($route.'getEdit',$data['id']) }}" class="btn btn-sm yellow filter-submit margin-bottom"><i class="glyphicon glyphicon-edit"></i> {{_('Edit')}}</a>
{!! Form::open([
'url' => action($route.'putDestroy'),
'method' => 'put',
'class'=>'form-inline']) !!}
{!! Form::hidden('id',$data['id']) !!}
{!! Form::button('<i class="glyphicon glyphicon-trash"></i> '._('Remove'),[
    "type"=>"submit",
    "data-toggle"=>"confirmation",
    "data-placement"=>"right",
    "data-singleton"=>"true",
    "data-btn-cancel-label"=>_('No'),
    "data-btn-ok-label"=>_('Yes'),
    "data-title"=>_('Are you sure?'),
    "class"=>"btn btn-sm red filter-submit margin-bottom",
]) !!}
{!! Form::close() !!}
