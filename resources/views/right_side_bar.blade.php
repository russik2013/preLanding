
@foreach($SideBarGroop as $groop)
    @if(($groop -> items ? $groop -> items -> count() : 0) > 0)
        {{--<ul>--}}
        @foreach($groop -> items as $item)

                <h5 class="m-b-0 pink-border-bottom">READER RESULTS</h5>

                <h5 class="m-b-0 pink-border-bottom">PROFIT: {{$item->profit}} {{config('globalsiteamount')[$setting->amount]}}</h5>
                <a href="{{$item->url}}" class="out_link " target="_blank">
                    <img src="{{asset('images/'.$item->photo)}}" class="img-responsive">
                </a>
                <p class="m-b-5">{!! $item->text !!}</p>

                <p><strong><i>{!! $item->people !!}</i></strong></p>
            @endforeach

        <hr>
        @endif
@endforeach

