{{-- resources/views/employees/partials/info-row.blade.php --}}
@if($value)
<div class="row py-2 border-bottom">
    <div class="col-5 text-muted small">{{ $label }}</div>
    <div class="col-7 fw-semibold small">{{ $value }}</div>
</div>
@else
<div class="row py-2 border-bottom">
    <div class="col-5 text-muted small">{{ $label }}</div>
    <div class="col-7 text-muted small">—</div>
</div>
@endif
