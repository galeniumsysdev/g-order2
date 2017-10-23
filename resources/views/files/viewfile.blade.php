@extends('layouts.navbar_product')

@section('content')
<div class="container">
    <div class="wrapper">
        <section class="panel panel-default">
            <div class="panel-heading"><strong>
                DETAIL FILES CMO
            </strong></div>
            <div class="panel-heading">
                <table class="table table-bordered">
                    <thead>
                        <th>Period</th>
						            <th>Version</th>
                        <th>Upload Date</th>
                        <th>Download</th>
                    </thead>

                    <tbody>
                    @foreach($downloads as $down)
                        <tr>
                            <td><strong>{{ $down->period }}</strong></td>
							<td><strong>{{ $down->version }}</strong></td>
                            <td><strong>{{ $down->created_at }}</strong></td>
                            <td>
                            <a href="{{ url('/downloadCMO/'.$down->file_pdf) }}" download="{{ $down->file_pdf }}">
                                <button type="button" class"btn btn-primary"><strong>PDF</strong></button>
							</a>
							<a href="{{ url('/downloadCMO/'.$down->file_excel) }}" download="{{ $down->file_excel }}">
								<button type="button" class"btn btn-primary"><strong>EXCEL</strong></button>
                            </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
			<br></br>
			
        </section>
    </div>
  </div>

@endsection
