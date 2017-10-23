@component('mail::message')
@if($customer=="")
# Pembuatan PO: {{$so_headers->customer_po}}

Terimakasih Anda telah melakukan pembelian melalui aplikasi {{ config('app.name') }}. Dibawah ini adalah rincian pembelian anda dengan nomor transaksi system <strong>{{$so_headers->notrx}}</strong>:
@else
# Pesanan baru dari: {{$customer}}

Anda mendapatkan pesanan baru dari {{$customer}} melalui aplikasi {{ config('app.name') }}. Dibawah ini adalah rincian pembelian anda dengan nomor transaksi system <strong>{{$so_headers->notrx}}</strong>:
@endif
@component('mail::table')
| Product           | Qty              | Harga Satuan        |Total              |
| ------------------|:----------------:| -------------------:|------------------:|
@foreach($lines as $l)
| {{$l->title}}     | {{$l->qty_request." ".$l->uom}}| {{number_format($l->unit_price,2)}}|   {{number_format($l->amount,2)}}|
@endforeach
|                   |                  |                Total|{{number_format($total,2)}}|
@endcomponent

Terimakasih,<br>
Admin {{ config('app.name') }}
@endcomponent
