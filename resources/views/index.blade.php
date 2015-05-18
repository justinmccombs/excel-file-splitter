@extends('layout.default')

@section('content')

    <div class="row">
        <div class="col-xs-12">
            <h2>Choose Excel File to Split</h2>
            {!! Form::open(['files' => true, 'url' => URL::to('/'), 'class' => 'form form-horizontal']) !!}

            <div class="form-group">
            	<label for="file" class="col-sm-2 control-label">File</label>
            	<div class="col-sm-10">
            		<input type="file" name="file" id="file" class="form-control" required="required" >
            	</div>
            </div>

            <div class="form-group">
                {!! Form::label('row_count', 'Number of Rows per file', ['class' => 'col-sm-2 control-label']) !!}
                <div class="col-sm-10">
                    {!! Form::text('row_count', Input::old('row_count'), ['class' => 'form-control', 'placeholder' => 'e.g. 500', 'required' => 'required']) !!}
                </div>
            </div>

            <div class="form-actions">
                {!! Form::submit('Split File', ['class' => 'btn btn-primary']) !!}
            </div>

            {!! Form::close() !!}
        </div>
    </div>

@stop