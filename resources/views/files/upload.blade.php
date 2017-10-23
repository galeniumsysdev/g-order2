@extends('layouts.navbar_product')

@section('content')
<div class="container">
    <div class="row">
			<div class="col-md-10 col-sm-offset-1">
					<div class="panel panel-default">
						<div class="panel-heading"><strong>Upload Files CMO</strong></div>
						<div class="panel-body" style="overflow-x:auto;">
								<div id="frmsearch" class="panel panel-default">
								    @if(count($errors))
								        <ul>
								            @foreach($errors->all() as $error)
								                <li>{{ $error }}</li>
								            @endforeach
								        </ul>
								    @endif
								    <ul>
								        @foreach($files as $file)
								            <li>{{ $file }}</li>
								        @endforeach
								    </ul>
								    {!! Form::open(array('url' => '/handleUpload', 'files' => true)) !!}
										{!! Form::token() !!}
										<div class="form-group">
											<br>
											<label for="filepdf" class="col-md-2 control-label">Period</label>
											<div class="col-md-10" >
								        <input type="text" name="period" value="{{$period}}" class="form-class" readonly>
											</div>
										</div>
										<div class="form-group">
											<br>
											<label for="filepdf" class="col-md-2 control-label">File PDF</label>
											<div class="col-md-10" >
								        <input type="file" accept="application/pdf" name="file1">
											</div>
										</div>
										<div class="form-group">
											<br>
											<label for="fileexcel" class="col-md-2 control-label">File Excel</label>
											<div class="col-md-10" >
								        <input type="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" name="file2" >
											</div>
										</div>

										<div class="form-group">
												<div class="col-md-10 col-md-offset-2">
													<button type="submit" name="upload" class="btn btn-primary">
															@lang('label.upload')
													</button>
											</div>
										</div>
								    {!! Form::close() !!}

									<br></br><br>
									<!--<a href="http://localhost:8000/viewAlldownloadfile"><strong>DETAIL FILES</strong></a>-->
							</div>
						</div>
					</div>
				</div>
		</div>
</div>
@endsection
