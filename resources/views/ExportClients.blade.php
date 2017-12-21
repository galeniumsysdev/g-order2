<!DOCTYPE html>
<html>
<head>
  <title>Export File Excel</title>
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
          <td>{{ $c->longitude.','.$c->langitude }}</td>
          <td>{{ $c->subgroup_dc_id }}</td>
          <td>{{ $c->psc_flag.','.$c->pharma_flag }}</td>
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
