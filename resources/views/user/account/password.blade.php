{{ Form::open(['route' => ['user.password.change'], 'class' => 'form-horizontal', 'method' => 'PATCH']) }}

{!! form_row('password', [
    'name' => 'old_password',
    'required' => true,
    'title' => trans('validation.attributes.old_password'),
    'label_class' => 'col-md-4',
    'field_wrapper_class' => 'col-md-6',
]) !!}

{!! form_row('password', [
    'name' => 'password',
    'required' => true,
    'title' => trans('validation.attributes.new_password'),
    'label_class' => 'col-md-4',
    'field_wrapper_class' => 'col-md-6',
]) !!}

{!! form_row('password', [
    'name' => 'password_confirmation',
    'required' => true,
    'title' => trans('validation.attributes.new_password_confirmation'),
    'label_class' => 'col-md-4',
    'field_wrapper_class' => 'col-md-6',
]) !!}

<div class="form-group">
    <div class="col-md-6 col-md-offset-4">
        {{ Form::submit(trans('buttons.update'), ['class' => 'btn btn-primary', 'id' => 'change-password']) }}
    </div>
</div>

{{ Form::close() }}