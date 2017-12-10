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
        @foreach($customers as $c)
        <tr>
          <td></td>
          <td>{{ $c->customer_name }}</td>
          <td>{{ $c->province }}</td>
          <td>{{ $c->city }}</td>
          <td>@if(isset ($c->longitude))
            {{ $c->longitude.','.$c->langitude }}
            @endif
          </td>
          <td>{{ $c->subgroup_name }}</td>
          <td>
                            @if($c->psc_flag=="1" and $c->psc_flag=="1")
                              PSC, Pharma
                            @elseif($c->psc_flag=="1")
                                PSC
                            @elseif($c->pharma_flag=="1")
                                Pharma
                            @endif
                          </td>
          <td>{{ $c->created_at }}</td>
          <td></td>
          <td></td>
          <td>{{ $c->Status }}</td>
        </tr>
        @endforeach
    </tbody>
  </table>
</body>
</html>
