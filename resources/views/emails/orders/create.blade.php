@component('mail::message')
@if($customer=="")
# Pembuatan PO: {{$so_headers->customer_po}}

Terimakasih Anda telah melakukan pembelian melalui aplikasi {{ config('app.name') }}. Dibawah ini adalah rincian pembelian dengan nomor transaksi system <strong>{{$so_headers->notrx}}</strong>:
@else
# Pesanan baru dari: {{$customer}}

Anda mendapatkan pesanan baru dari {{$customer}} melalui aplikasi {{ config('app.name') }}. Dibawah ini adalah rincian pesanan dengan nomor transaksi system <strong>{{$so_headers->notrx}}</strong>:
@endif
@component('mail::table')
| Product           | Qty              | Harga Satuan        |Total              |
| ------------------|:----------------:| -------------------:|------------------:|
@php($tax=0)
@php($subtotal=0)
@foreach($lines as $l)
@php($tax+=$l->tax_amount)
@php($subtotal+=$l->amount)
| {{$l->title}}     | {{$l->qty_request." ".$l->uom}}| {{number_format($l->unit_price,2)}}|   {{number_format($l->amount,2)}}|
@endforeach
|                   |                  |                  Tax|{{number_format($subtotal,2)}}|
|                   |                  |                  Tax|{{number_format($tax,2)}}|
|                   |                  |                Total|{{number_format($total,2)}}|
@endcomponent

Terimakasih,<br>
Admin {{ config('app.name') }}
@endcomponent
