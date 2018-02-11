<center><h2>REKAP DPL</h2></center>
<table>
  <tr><td colspan="2"><strong>Parameter</strong></td></tr>
  <tr>
    <td>Period</td>
    <td>{{ $request->trx_in_date }}</td>
  </tr>
</table>
<table border="1">
  <thead>
    <tr>
      <th rowspan="2">No.</th>
      <th rowspan="2">No. DPL</th>
      <th rowspan="2">Tanggal DPL</th>
      <th rowspan="2">Nama Outlet / DR</th>
      <th rowspan="2">Nama MR / SPV</th>
      <th rowspan="2">Nama Produk</th>
      <th rowspan="2">Sales In Unit</th>
      <th rowspan="2">HNA</th>
      <th rowspan="2">Total</th>
      <th colspan="3" align="center">Kondisi Discount</th>
      <th rowspan="2">Distributor</th>
      <th rowspan="2">No. Transaksi</th>
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
      <th></th>
      <th>GPL Bonus</th>
      <th>GPL (%)</th>
      <th>Distributor</th>
    </tr>
  </thead>
  <tbody>
    @php($no=0)
    @php($tmpheader=null)
    @foreach($datalist as $key => $data)
    @php($no+=1)
      <tr>
        <td style="vertical-align:middle">{{ $no }}</td>
        <td style="vertical-align:middle">{{ $data->dpl_no }}</td>
        <td style="vertical-align:middle">{{ $data->created_at }}</td>
        <td style="vertical-align:middle">{{ $data->customer_name }}</td>
        <td style="vertical-align:middle">{{ $data->spv_name }}</td>
        <td style="vertical-align:middle">{{ $data->nm_product }}</td>
        <td style="vertical-align:middle">{{ $data->qty_request_primary }}</td>
        <td style="vertical-align:middle">{{ $data->hna }}</td>
        <td style="vertical-align:middle">{{ $data->total }}</td>
        <td style="vertical-align:middle">{{ $data->gpl_bonus }}</td>
        <td style="vertical-align:middle">{{ $data->gpl_discount }}</td>
        <td style="vertical-align:middle">{{ $data->disc_distributor }}</td>
        <td style="vertical-align:middle">{{ $data->distributor }}</td>
        <td style="vertical-align:middle">{{ $data->deliveryno }}</td>
      </tr>
      @endforeach
  </tbody>
</table>
