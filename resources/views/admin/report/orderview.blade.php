<center><h2>Report Order</h2></center>
<table>
  <tr><td colspan="2">Parameter</td></tr>
  <tr>
    <td>Period</td>
    <td>{{date_format(date_create($request->tglaw),'d-M-Y') }} s.d {{date_format(date_create($request->tglak),'d-M-Y')}}</td>
  </tr>
  @if($request->dist_id)
  <tr>
    <td>Distributor</td>
    <td>{{$request->distributor}}</td>
  </tr>
  @endif
  @if($request->outlet_id)
  <tr>
    <td>Outlet</td>
    <td>{{$request->outlet}}</td>
  </tr>
  @endif
  @if($request->propinsi)
  <tr>
    <td>Propinsi</td>
    <td>{{$request->propinsi}}</td>
  </tr>
  @endif
  @if($request->channel)
  <tr>
    <td>Channel</td>
    <td>{{$request->channel}}</td>
  </tr>
  @endif
  @if($request->psc_flag=="1" or $pharma_flag=="1")
  <tr>
    <td>Divisi</td>
    <td>
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
      <th>Product</th>
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
          <td rowspan="{{$header->lines->count()}}">{{$no}}</td>
          <td rowspan="{{$header->lines->count()}}">{{$header->notrx}}</td>
          <td rowspan="{{$header->lines->count()}}">{{$header->customer_name}}</td>
          <td rowspan="{{$header->lines->count()}}">{{$header->channel}}</td>
          <td rowspan="{{$header->lines->count()}}">{{$header->alamat}}</td>
          <td rowspan="{{$header->lines->count()}}">{{$header->distributor_name}}</td>
          <td rowspan="{{$header->lines->count()}}">{{$header->divisi}}</td>
          <td rowspan="{{$header->lines->count()}}">{{$header->tgl_order}}</td>
          <td>{{$header->lines->first()->title}}</td>
          <td>{{$header->lines->first()->qty_request_primary}}</td>
          <td>{{$header->lines->first()->unit_price/$header->lines->first()->conversion_qty}}</td>
          <td>{{$header->lines->first()->amount}}</td>
          <td rowspan="{{$header->lines->count()}}">{{$header->tgl_approve}}</td>
          <td rowspan="{{$header->lines->count()}}" ></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td rowspan="{{$header->lines->count()}}">{{$header->status_name}}</td>
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
          <td></td>
          <!--DO release po-->
          <td></td>
          <td></td>
          <td></td>
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
