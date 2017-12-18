<center><h2>Report Order</h2></center>
<table>
  <tr><td colspan="2">Parameter</td></tr>
  <tr>
    <td>Period</td>
    <td>{{date_format(date_create($request->tglaw),'d-M-Y') }} s.d {{date_format(date_create($request->tglak),'d-M-Y')}}</td>
  </tr>
  @if(isset($request->distributor))
  <tr>
    <td>Distributor</td>
    <td>{{$request->distributor}}</td>
  </tr>
  @endif
  @if(isset($request->outlet))
  <tr>
    <td>Outlet</td>
    <td>{{$request->outlet}}</td>
  </tr>
  @endif
  @if(isset($request->propinsi))
  <tr>
    <td>Propinsi</td>
    <td>{{$namapropinsi}}</td>
  </tr>
  @endif
  @if(isset($request->channel))
  <tr>
    <td>Channel</td>
    <td>{{$nmchannel}}</td>
  </tr>
  @endif
</table>
<table border="1">
  <thead>
    <tr>
      <th rowspan="2">#</th>
      <th rowspan="2">No.trx</th>
      <th rowspan="2">Nama Outlet</th>
      <th rowspan="2">Channel</th>
      <th rowspan="2">Alamat</th>
      <th rowspan="2">Distributor</th>
      <th rowspan="2">Divisi</th>
      <th rowspan="2">Tgl Order</th>
      <th colspan="4">Order</th>
      <th rowspan="2">Tgl Confirm Distributor</th>
      <th rowspan="2">Tgl Confirm Outlet Terima</th>
      <th colspan="4">DO (Release PO)</th>
      <th rowspan="2">Service Level</th>
      <th rowspan="2">Lead Time</th>
      <th rowspan="2">Status</th>
    </tr>
    <tr>
      <th></th>
      <th></th>
      <th></th>
      <th></th>
      <th></th>
      <th></th>
      <th></th>
      <th></th>
      <th>Product</th>
      <th>Qty</th>
      <th>Harga</th>
      <th>Value</th>
      <th></th>
      <th></th>
      <th>No SJ</th>
      <th>Qty</th>
      <th>Value</th>
      <th>Tgl Kirim</th>
      <th></th>
      <th></th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    @php($no=0)
    @php($tmpheader=null)
    @foreach($datalist->groupBy('id') as $key => $data)
    @php($no+=1)
    @php($groupheader=$data->count())
      <tr>
        @if($key<>$tmpheader)
        @php($tmpheader=$key)
        <td rowspan="{{$groupheader}}" style="vertical-align:middle">{{$no}}</td>
        <td rowspan="{{$groupheader}}" style="vertical-align:middle">{{$data->first()->notrx}}</td>
        <td rowspan="{{$groupheader}}" style="vertical-align:middle">{{$data->first()->customer_name}}</td>
        <td rowspan="{{$groupheader}}" style="vertical-align:middle">{{$data->first()->channel}}</td>
        <td rowspan="{{$groupheader}}" style="vertical-align:middle">{{$data->first()->alamat}}</td>
        <td rowspan="{{$groupheader}}" style="vertical-align:middle">{{$data->first()->distributor_name}}</td>
        <td rowspan="{{$groupheader}}" style="vertical-align:middle">{{$data->first()->divisi}}</td>
        <td rowspan="{{$groupheader}}" style="vertical-align:middle">{{$data->first()->tgl_order}}</td>
        @endif
        @php($lineno=0)
        @foreach($data as $detail)
        @if($lineno>0)
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          @endif
        <td>{{$detail->title}}</td>
        <td align="right">{{$detail->qty_request_primary}}</td>
        <td align="right">{{number_format($detail->unit_price_primary,2)}}</td>
        <td align="right">{{number_format($detail->amount,2)}}</td>
        <td>{{$detail->tgl_approve}}</td>
        @if($lineno==0)
        <td rowspan="{{$groupheader}}">{{$detail->tgl_terima}}</td>
        @else
        <td></td>
        @endif
        <td>{{$detail->deliveryno}}</td>
        <td align="right">{{$detail->qty_shipping}}</td>
        <td align="right">{{number_format($detail->qty_shipping*$detail->unit_price_primary,2)}}</td>
        <td>{{$detail->tgl_kirim}}</td>
        <td>@if(isset($detail->tgl_kirim))
          {{$detail->tgl_kirim-$detail->tgl_order}}
          @endif
        </td>
        <td>@if(isset($detail->tgl_terima))
            {{$detail->tgl_terima-$detail->tgl_order}}
            @endif
        </td>

        @if($lineno>0) </tr>@endif

        @if($lineno==0)
        <td rowspan="{{$groupheader}}">{{$detail->status_name}}</td>
        @endif
        @php($lineno+=1)
        @endforeach
      </tr>



    @endforeach


  </tbody>
</table>
