<ul>
    @foreach ($entry as $k=>$v)
        @if ($k=='name' || $k=='id' || $k=='item_order')
            <li><strong>{{$k}}:</strong> {{$v}}</li>
        @endif
    @endforeach
</ul>