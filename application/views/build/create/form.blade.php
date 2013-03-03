<h3>Build Information Form</h3>

<p>{{ Form::label('name', 'Name') }}</p>
<p>{{ Form::text('name', false, array('class' => 'input-block-level')) }}</p>

<p>{{ Form::label('class', 'Class') }}</p>
<p>{{ Form::select('class', D3Up_Classes::$classes, null, array('class' => 'input-block-level')) }}</p>

<p>{{ Form::label('level', 'Level') }}</p>
<p>{{ Form::text('level', false, array('class' => 'input-block-level')) }}</p>

<p>{{ Form::label('paragon', 'Paragon') }}</p>
<p>{{ Form::text('paragon', false, array('class' => 'input-block-level')) }}</p>

<p>{{ Form::label('hardcore', 'Hardcore?') }}</p>
<p>{{ Form::checkbox('hardcore') }}</p>

<!-- submit button -->
@if (Session::has('login_errors'))
	<div class="alert alert-error">Email/Username or Password incorrect.</div>
@endif
<div class='btn-group'>
	{{ Form::submit('Create Build', array('class' => 'btn')) }}
</div>