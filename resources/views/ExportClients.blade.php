<center><h2>Report NOO</h2></center>
<table>
  <tr><td colspan="3">Parameter</td></tr>
  @if($request->distributor)
  <tr>
    <td colspan="2">Distributor</td>
    <td>{{$request->distributor}}</td>
  </tr>
  @endif
  @if($request->city)
  <tr>
    <td colspan="2">City</td>
    <td>{{$request->city}}</td>
  </tr>
  @endif
  @if($request->channel)
  <tr>
    <td colspan="2">Channel</td>
    <td>{{$request->channel}}</td>
  </tr>
  @endif
  @if($request->divisi)
  <tr>
    <td colspan="2">Divisi</td>
    <td>{{$request->divisi}}
      @if($request->psc_flag=="1" and $request->pharma_flag=="1")
        PSC/PHARMA
      @elseif($request->psc_flag=="1")
        PSC
      @elseif($request->pharma_flag=="1")
        PHARMA
      @endif
    </td>
  </tr>
  @endif
</table>
<!DOCTYPE html>
<html>
<head>
  <title>Report NOO</title>
</head>
<body>
  <table>
    <thead>
      <tr>
        <td><strong>NO.</strong></td>
        <td><strong>NAMA OUTLET</strong></td>
        <td><strong>PROVINCE</strong></td>
        <td><strong>AREA</strong></td>
        <td><strong>KOORDINAT</strong></td>
        <td><strong>CHANNEL</strong></td>
        <td><strong>DIVISI</strong></td>
        <td><strong>TGL. REGISTER</strong></td>
        <td><strong>ID CUSTOMER</strong></td>
        <td><strong>DISTRIBUTOR</strong></td>
        <td><strong>STATUS</strong></td>
      </tr>
    </thead>
    <tbody>
      @php($i=0)
        @foreach($customers as $c)
        @php($i+=1)
        @if($c->distributor->count())
        @php($rowgab=$c->distributor->count())
        @else
        @php($rowgab=1)
        @endif
        <tr>
          <td rowspan="{{$rowgab}}">{{$i}}</td>
          <td rowspan="{{$rowgab}}">{{ $c->customer_name }}</td>
          <td rowspan="{{$rowgab}}">{{ $c->province }}</td>
          <td rowspan="{{$rowgab}}">{{ $c->city }}</td>
          <td rowspan="{{$rowgab}}">@if(isset ($c->longitude))
            {{ $c->longitude.','.$c->langitude }}
            @endif
          </td>
          <td rowspan="{{$rowgab}}">{{ $c->subgroup_name }}</td>
          <td rowspan="{{$rowgab}}">
            @if($c->psc_flag=="1" and $c->psc_flag=="1")
              PSC, Pharma
            @elseif($c->psc_flag=="1")
                PSC
            @elseif($c->pharma_flag=="1")
                Pharma
            @endif
          </td>
          <td rowspan="{{$rowgab}}">{{ $c->created_at }}</td>
          <td rowspan="{{$rowgab}}">{{$c->id}}</td>
          <td>@if($c->distributor->count())
            {{$c->distributor->first()->customer_name}}
            @endif
          </td>
          <td rowspan="{{$rowgab}}">{{ $c->Status }}</td>
        </tr>
        @if($rowgab>1)
          @php($lineno=0)
          @foreach ($c->distributor as $dist)
            @if($lineno!=0)
            <tr>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td>{{$dist->customer_name}}</td>
              <td></td>
            </tr>
            @endif
            @php($lineno+=1)
          @endforeach
        @endif
        @endforeach

    </tbody>
  </table>
</body>
</html>
