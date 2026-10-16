{{-- A plain grid of property cards. $properties, $user --}}
<div class="row">
    @foreach ($properties as $property)
        <div class="col-sm-6 col-lg-4 col-xxl-3 mb-4">@include('theme._property_card', ['property' => $property])</div>
    @endforeach
</div>
