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
</table>
<table>
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
      <th colspan="3">DO (Release PO)</th>
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
      <th></th>
    </tr>
  </thead>
  <tbody>

      @php($no=0)
      @foreach($datalist as $header)
      @php($no+=1)
        <tr>
          <td rowspan="{{$header->lines->count()}}" style="vertical-align:middle">{{$no}}</td>
          <td rowspan="{{$header->lines->count()}}" style="vertical-align:middle">{{$header->notrx}}</td>
          <td rowspan="{{$header->lines->count()}}" style="vertical-align:middle">{{$header->customer_name}}</td>
          <td rowspan="{{$header->lines->count()}}" style="vertical-align:middle">{{$header->channel}}</td>
          <td rowspan="{{$header->lines->count()}}" style="vertical-align:middle">{{$header->alamat}}</td>
          <td rowspan="{{$header->lines->count()}}" style="vertical-align:middle">{{$header->distributor_name}}</td>
          <td rowspan="{{$header->lines->count()}}" style="vertical-align:middle">{{$header->divisi}}</td>
          <td rowspan="{{$header->lines->count()}}" style="vertical-align:middle">{{$header->tgl_order}}</td>
          <td>{{$header->lines->first()->title}}</td>
          <td>{{$header->lines->first()->qty_request_primary}}</td>
          <td>{{$header->lines->first()->unit_price/$header->lines->first()->conversion_qty}}</td>
          <td>{{$header->lines->first()->amount}}</td>
          <td rowspan="{{$header->lines->count()}}" style="vertical-align:middle">{{$header->tgl_approve}}</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td rowspan="{{$header->lines->count()}}" style="vertical-align:middle">{{$header->status_name}}</td>
        </tr>
        @php($lineno=0)
        @foreach($header->lines as $line)
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
          <td>{{$line->title}}</td>
          <td>{{$line->qty_request_primary}}</td>
          <td>{{$line->unit_price/$line->conversion_qty}}</td>
          <td>{{$line->amount}}</td>
          <td></td>
          @if($line->shippings->count()>0)
          <td>{{$line->shippings->first()->tgl_terima}}</td>
          <!--DO release po-->
          <td>{{$line->shippings->first()->deliveryno}}</td>
          <td>{{$line->shippings->first()->qty_shipping}}</td>
          <td></td>
          @else
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          @endif
          <td></td>
          <td></td>
          <td></td>
          </tr>
        @endif
        @php($lineno+=1)
        @endforeach

      @endforeach

  </tbody>
</table>
